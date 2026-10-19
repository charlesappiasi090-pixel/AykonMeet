<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\MeetingCompany;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendeeImporter
{
    public const HEADERS = ['full name', 'email', 'phone'];

    public const OPTIONAL_HEADERS = ['company', 'department'];

    public function import(UploadedFile $file, Meeting $meeting): array
    {
        $rows = $this->readRows($file);

        if (count($rows) < 2) {
            return ['created' => 0, 'skipped' => 0, 'attendees' => collect(), 'error' => 'The file is empty or has no data rows.'];
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $rows[0]);

        if (array_diff(self::HEADERS, $headers)) {
            return ['created' => 0, 'skipped' => 0, 'attendees' => collect(), 'error' => 'The file must contain Full Name, Email and Phone columns.'];
        }

        $hasCompany = in_array('company', $headers, true);
        $hasDepartment = in_array('department', $headers, true);

        $created = 0;
        $createdAttendees = collect();
        $skipped = 0;
        $usedEmails = [];

        foreach (array_slice($rows, 1) as $row) {
            $row = array_pad($row, count($headers), '');
            $values = array_combine($headers, $row);
            $fullName = trim((string) ($values['full name'] ?? ''));
            $email = trim((string) ($values['email'] ?? ''));
            $phone = trim((string) ($values['phone'] ?? ''));
            $company = $hasCompany ? trim((string) ($values['company'] ?? '')) : '';
            $department = $hasDepartment ? trim((string) ($values['department'] ?? '')) : '';

            if ($fullName === '' || $email === '' || $phone === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped++;

                continue;
            }

            if (
                isset($usedEmails[$email])
                || $meeting->attendees()->where('email', $email)->exists()
            ) {
                $skipped++;

                continue;
            }

            $usedEmails[$email] = true;
            $attributes = [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
                'department' => $department !== '' ? $department : null,
            ];
            if ($meeting->isMultiCompany() && $company !== '') {
                $attributes['meeting_company_id'] = MeetingCompany::forName($meeting, $company)->id;
            }
            $createdAttendees->push($meeting->attendees()->create($attributes));
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped, 'attendees' => $createdAttendees];
    }

    private function readRows(UploadedFile $file): array
    {
        if ($file->getClientOriginalExtension() === 'csv') {
            return $this->readCsv($file);
        }

        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $rows = $reader->load($file->getRealPath())->getActiveSheet()->toArray(null, true, false, true);
            $rows = array_values($rows);

            foreach ($rows as &$row) {
                foreach ($row as $key => $value) {
                    if ($value instanceof \DateTimeInterface) {
                        $row[$key] = $value->format('Y-m-d H:i:s');
                    }
                }
            }

            return $rows;
        } catch (\Throwable) {
            return [];
        }
    }

    private function readCsv(UploadedFile $file): array
    {
        $rows = [];
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        }

        return $rows;
    }
}
