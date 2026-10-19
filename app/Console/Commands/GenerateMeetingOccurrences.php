<?php

namespace App\Console\Commands;

use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\MeetingOccurrence;
use App\Models\MeetingSeries;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class GenerateMeetingOccurrences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meetings:generate-occurrences 
                            {--days=30 : Number of days ahead to generate occurrences for}
                            {--series= : Specific series ID to generate for}
                            {--limit=50 : Maximum occurrences to generate per series}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate upcoming meeting occurrences for active recurring meeting series';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysAhead = (int) $this->option('days');
        $seriesId = $this->option('series');
        $limit = (int) $this->option('limit');

        $query = MeetingSeries::where('is_active', true)
            ->where('start_date', '<=', now()->addDays($daysAhead));

        if ($seriesId) {
            $query->where('id', $seriesId);
        }

        $series = $query->get();

        if ($series->isEmpty()) {
            $this->info('No active meeting series found to generate occurrences for.');

            return Command::SUCCESS;
        }

        $totalGenerated = 0;
        $totalSeries = $series->count();

        $this->info("Processing {$totalSeries} meeting series...");

        foreach ($series as $serie) {
            $generated = $serie->generateOccurrences($limit);
            $totalGenerated += $generated;

            if ($generated > 0) {
                $this->line("  <info>{$serie->name}</info> ({$serie->getFrequencyLabel()}): Generated {$generated} occurrence(s)");
            } else {
                $this->line("  <comment>{$serie->name}</comment>: No new occurrences to generate");
            }
        }

        $this->newLine();
        $this->info("Total occurrences generated: {$totalGenerated} across {$totalSeries} series");

        // Now create actual meetings for occurrences that don't have them yet
        $this->createMeetingsForOccurrences();

        return Command::SUCCESS;
    }

    /**
     * Create actual Meeting records for occurrences that are scheduled but not yet generated.
     */
    private function createMeetingsForOccurrences(): void
    {
        $occurrences = MeetingOccurrence::with('series')
            ->where('status', 'scheduled')
            ->where('scheduled_start', '<=', now()->addDays(30))
            ->whereDoesntHave('meeting')
            ->orderBy('scheduled_start')
            ->get();

        if ($occurrences->isEmpty()) {
            $this->info('No occurrences need meeting creation.');

            return;
        }

        $created = 0;

        foreach ($occurrences as $occurrence) {
            try {
                $series = $occurrence->series;
                $parentMeeting = $series->parentMeeting()->first();

                if (! $parentMeeting) {
                    Log::warning("No parent meeting found for series {$series->id}");

                    continue;
                }

                // Check if there's an exception for this occurrence
                $exception = $occurrence->exception();
                $startDate = $occurrence->getEffectiveStart();
                $endDate = $occurrence->getEffectiveEnd();

                $meeting = Meeting::create([
                    'meeting_code' => 'AYK-'.strtoupper(Str::random(6)),
                    'name' => $parentMeeting->name,
                    'email' => $parentMeeting->email,
                    'phone' => $parentMeeting->phone,
                    'company_name' => $parentMeeting->company_name,
                    'meeting_topic' => $parentMeeting->meeting_topic,
                    'description' => $parentMeeting->description,
                    'topic_category' => $parentMeeting->topic_category,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'from_location' => $parentMeeting->from_location,
                    'to_location' => $parentMeeting->to_location,
                    'meeting_room_id' => $parentMeeting->meeting_room_id,
                    'min_attendees' => $parentMeeting->min_attendees,
                    'physical_location' => $parentMeeting->physical_location,
                    'status' => 'scheduled',
                    'is_online' => $parentMeeting->is_online,
                    'session_provider' => $parentMeeting->session_provider,
                    'host_user_id' => $parentMeeting->host_user_id,
                    'meeting_type' => $parentMeeting->meeting_type,
                    'is_multi_company' => $parentMeeting->is_multi_company,
                    'settings' => $parentMeeting->settings,
                    'series_id' => $series->id,
                    'is_series_parent' => false,
                    'occurrence_number' => $occurrence->occurrence_number,
                ]);

                // Copy attendees from parent meeting
                foreach ($parentMeeting->attendees as $attendee) {
                    $meeting->attendees()->create([
                        'full_name' => $attendee->full_name,
                        'email' => $attendee->email,
                        'phone' => $attendee->phone,
                        'department' => $attendee->department,
                        'meeting_company_id' => $attendee->meeting_company_id,
                        'invitation_status' => 'draft',
                        'invitation_expires_at' => $endDate,
                    ]);
                }

                // Copy companies from parent meeting (for multi-company)
                foreach ($parentMeeting->companies as $company) {
                    $newCompany = MeetingCompany::create([
                        'meeting_id' => $meeting->id,
                        'name' => $company->name,
                        'slug' => $company->slug,
                        'contact_name' => $company->contact_name,
                        'contact_email' => $company->contact_email,
                        'contact_phone' => $company->contact_phone,
                        'status' => $company->status,
                        'is_host_company' => $company->is_host_company,
                    ]);

                    // Copy company attendees
                    foreach ($company->attendees as $attendee) {
                        $newCompany->attendees()->create([
                            'full_name' => $attendee->full_name,
                            'email' => $attendee->email,
                            'phone' => $attendee->phone,
                            'department' => $attendee->department,
                            'invitation_status' => 'draft',
                            'invitation_expires_at' => $endDate,
                        ]);
                    }
                }

                // Link the occurrence to the meeting
                $occurrence->linkMeeting($meeting);

                // Generate session URL for online meetings
                if ($meeting->is_online) {
                    $meeting->update([
                        'session_url' => URL::signedRoute('sessions.join', ['meeting' => $meeting]),
                    ]);
                }

                $created++;
            } catch (\Throwable $e) {
                Log::error("Failed to create meeting for occurrence {$occurrence->id}: ".$e->getMessage());
                $this->error("Failed to create meeting for occurrence {$occurrence->occurrence_number} of series {$occurrence->series_id}: ".$e->getMessage());
            }
        }

        $this->info("Created {$created} meeting(s) from scheduled occurrences.");
    }
}
