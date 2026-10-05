<?php

namespace App\Services;

use App\Models\Meeting;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AttendeeImporter
{
    public const HEADERS = ['full name', 'email', 'phone'];

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

            if ($fullName === '' || $email === '' || $phone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            $createdAttendees->push($meeting->attendees()->create([
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $phone,
            ]));
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