<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;

class CompanyImporter
{
    public const REQUIRED_HEADERS = [
        'company name',
        'company email',
        'company phone',
        'representative name',
        'representative email',
    ];

    public const OPTIONAL_HEADERS = [
        'representative department',
        'invitation message',
    ];

    public function import(UploadedFile $file): array
    {
        $rows = $this->readRows($file);

        if (count($rows) < 2) {
            return [
                'success' => false,
                'imported' => 0,
                'skipped' => 0,
                'companies' => [],
                'error' => 'The file is empty or has no data rows.',
            ];
        }

        $headers = array_map(fn ($header) => strtolower(trim((string) $header)), $rows[0]);

        $missingRequired = array_diff(self::REQUIRED_HEADERS, $headers);
        if (! empty($missingRequired)) {
            return [
                'success' => false,
                'imported' => 0,
                'skipped' => 0,
                'companies' => [],
                'error' => 'Missing required columns: '.implode(', ', self::REQUIRED_HEADERS),
            ];
        }

        $imported = 0;
        $skipped = 0;
        $companies = [];

        foreach (array_slice($rows, 1) as $rowIndex => $row) {
            $row = array_pad($row, count($headers), '');
            $values = array_combine($headers, $row);

            $companyName = trim((string) ($values['company name'] ?? ''));
            $companyEmail = trim((string) ($values['company email'] ?? ''));
            $companyPhone = trim((string) ($values['company phone'] ?? ''));
            $repName = trim((string) ($values['representative name'] ?? ''));
            $repEmail = trim((string) ($values['representative email'] ?? ''));
            $repDepartment = trim((string) ($values['representative department'] ?? ''));
            $invitationMessage = trim((string) ($values['invitation message'] ?? ''));

            // Validation
            if ($companyName === '' || $companyEmail === '' || $repName === '' || $repEmail === '') {
                $skipped++;

                continue;
            }

            if (! filter_var($companyEmail, FILTER_VALIDATE_EMAIL) || ! filter_var($repEmail, FILTER_VALIDATE_EMAIL)) {
                $skipped++;

                continue;
            }

            $companies[] = [
                'name' => $companyName,
                'email' => $companyEmail,
                'phone' => $companyPhone !== '' ? $companyPhone : null,
                'rep_name' => $repName,
                'rep_email' => $repEmail,
                'rep_department' => $repDepartment !== '' ? $repDepartment : null,
                'invitation_message' => $invitationMessage !== '' ? $invitationMessage : null,
            ];

            $imported++;
        }

        return [
            'success' => true,
            'imported' => $imported,
            'skipped' => $skipped,
            'companies' => $companies,
        ];
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
