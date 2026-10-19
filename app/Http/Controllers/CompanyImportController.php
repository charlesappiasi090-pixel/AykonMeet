<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CompanyImportController extends Controller
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB

    private const MAX_ROWS = 500;

    private const ALLOWED_MIME_TYPES = [
        'text/csv',
        'application/csv',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function index(): View
    {
        $imports = CompanyImport::with('creator')
            ->latest()
            ->paginate(20);

        return view('admin.companies.imports.index', compact('imports'));
    }

    public function create(): View
    {
        return view('admin.companies.imports.create');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $headers = [
            'name',
            'email',
            'phone',
            'website',
            'registration_number',
            'company_type',
            'address',
            'city',
            'state',
            'postal_code',
            'country',
            'logo_url',
            'notes',
        ];

        $sampleData = [
            [
                'name' => 'Acme Corporation',
                'email' => 'contact@acme.com',
                'phone' => '+1-555-123-4567',
                'website' => 'https://acme.com',
                'registration_number' => 'REG-12345',
                'company_type' => 'corporation',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'logo_url' => 'https://acme.com/logo.png',
                'notes' => 'Primary vendor',
            ],
            [
                'name' => 'TechStart LLC',
                'email' => 'info@techstart.io',
                'phone' => '+1-555-987-6543',
                'website' => 'https://techstart.io',
                'registration_number' => 'REG-67890',
                'company_type' => 'llc',
                'address' => '456 Tech Ave',
                'city' => 'San Francisco',
                'state' => 'CA',
                'postal_code' => '94105',
                'country' => 'USA',
                'logo_url' => '',
                'notes' => 'Startup partner',
            ],
        ];

        $handle = fopen('php://memory', 'r+');
        fputcsv($handle, $headers);
        foreach ($sampleData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);

        $tempFile = tempnam(sys_get_temp_dir(), 'company_template_');
        file_put_contents($tempFile, stream_get_contents($handle));
        fclose($handle);

        return response()->download($tempFile, 'company_import_template.csv', [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:'.($this::MAX_FILE_SIZE / 1024), 'mimetypes:'.implode(',', self::ALLOWED_MIME_TYPES)],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $records = $this->parseFile($file, $extension);
        } catch (\Throwable $e) {
            Log::error('Company import parse error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to parse file: '.$e->getMessage(),
            ], 422);
        }

        if (count($records) > self::MAX_ROWS) {
            return response()->json([
                'success' => false,
                'message' => 'File exceeds maximum row limit of '.self::MAX_ROWS.'. Found '.count($records).' rows.',
            ], 422);
        }

        $results = $this->validateRecords($records);

        return response()->json([
            'success' => true,
            'total_rows' => count($records),
            'valid_rows' => $results['valid']->count(),
            'invalid_rows' => $results['invalid']->count(),
            'duplicate_rows' => $results['duplicates']->count(),
            'existing_rows' => $results['existing']->count(),
            'preview' => $results['all']->take(50)->values()->all(),
            'errors' => $results['invalid']->values()->all(),
            'duplicates' => $results['duplicates']->values()->all(),
            'existing' => $results['existing']->values()->all(),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:'.($this::MAX_FILE_SIZE / 1024), 'mimetypes:'.implode(',', self::ALLOWED_MIME_TYPES)],
            'confirm' => ['required', 'accepted'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $records = $this->parseFile($file, $extension);
        } catch (\Throwable $e) {
            Log::error('Company import parse error: '.$e->getMessage());

            return back()->with('error', 'Failed to parse file: '.$e->getMessage());
        }

        if (count($records) > self::MAX_ROWS) {
            return back()->with('error', 'File exceeds maximum row limit of '.self::MAX_ROWS.'.');
        }

        $results = $this->validateRecords($records);

        if ($results['valid']->isEmpty()) {
            return back()->with('error', 'No valid records to import. Please fix the errors and try again.');
        }

        $import = CompanyImport::create([
            'filename' => $file->getClientOriginalName(),
            'total_rows' => count($records),
            'valid_rows' => $results['valid']->count(),
            'invalid_rows' => $results['invalid']->count(),
            'duplicate_rows' => $results['duplicates']->count(),
            'existing_rows' => $results['existing']->count(),
            'created_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
            'status' => 'processing',
            'created_by' => auth()->id(),
            'imported_at' => now(),
        ]);

        $created = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use ($results, $import, &$created, &$skipped, &$failed, &$errors) {
            foreach ($results['valid'] as $record) {
                try {
                    $existing = Company::where('slug', Str::slug($record['name']))->first();
                    if ($existing) {
                        $skipped++;

                        continue;
                    }

                    $company = Company::create([
                        'name' => $record['name'],
                        'slug' => Company::generateUniqueSlug($record['name']),
                        'email' => $record['email'] ?? null,
                        'phone' => $record['phone'] ?? null,
                        'website' => $record['website'] ?? null,
                        'registration_number' => $record['registration_number'] ?? null,
                        'company_type' => $record['company_type'] ?? null,
                        'address' => $record['address'] ?? null,
                        'city' => $record['city'] ?? null,
                        'state' => $record['state'] ?? null,
                        'postal_code' => $record['postal_code'] ?? null,
                        'country' => $record['country'] ?? null,
                        'logo_path' => $record['logo_path'] ?? null,
                        'notes' => $record['notes'] ?? null,
                        'status' => 'active',
                        'import_source' => 'bulk_import',
                        'imported_at' => now(),
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id(),
                    ]);
                    $created++;
                } catch (\Throwable $e) {
                    $failed++;
                    $errors[] = [
                        'name' => $record['name'],
                        'error' => $e->getMessage(),
                    ];
                    Log::error('Company creation failed: '.$e->getMessage(), [
                        'record' => $record,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }

            $import->update([
                'created_count' => $created,
                'skipped_count' => $skipped,
                'failed_count' => $failed,
                'errors' => $errors,
                'status' => 'completed',
            ]);
        });

        $message = "Import completed. {$import->created_count} companies created";
        if ($import->skipped_count > 0) {
            $message .= ", {$import->skipped_count} skipped (already exist)";
        }
        if ($import->failed_count > 0) {
            $message .= ", {$import->failed_count} failed";
        }

        return redirect()->route('admin.companies.imports.show', $import)
            ->with('success', $message);
    }

    public function show(CompanyImport $import): View
    {
        $import->load('creator');

        return view('admin.companies.imports.show', compact('import'));
    }

    private function parseFile($file, string $extension): array
    {
        $records = [];

        if ($extension === 'csv') {
            $handle = fopen($file->getRealPath(), 'r');
            if (! $handle) {
                throw new \Exception('Could not open CSV file');
            }

            $headers = fgetcsv($handle);
            if (! $headers) {
                throw new \Exception('CSV file is empty or has no headers');
            }

            $headers = array_map('strtolower', array_map('trim', $headers));
            $expectedHeaders = [
                'name', 'email', 'phone', 'website', 'registration_number',
                'company_type', 'address', 'city', 'state', 'postal_code',
                'country', 'logo_url', 'notes',
            ];

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }
                $record = array_combine($headers, array_map('trim', $row));
                $records[] = $this->normalizeRecord($record);
            }
            fclose($handle);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (empty($rows)) {
                throw new \Exception('Spreadsheet is empty');
            }

            $headers = array_map('strtolower', array_map('trim', $rows[0]));
            unset($rows[0]);

            foreach ($rows as $row) {
                if (count($row) < count($headers)) {
                    $row = array_pad($row, count($headers), '');
                }
                $record = array_combine($headers, array_map('trim', $row));
                $records[] = $this->normalizeRecord($record);
            }
        } else {
            throw new \Exception('Unsupported file format. Please use CSV or XLSX.');
        }

        return $records;
    }

    private function normalizeRecord(array $record): array
    {
        $normalized = [
            'name' => $record['name'] ?? '',
            'email' => strtolower(trim($record['email'] ?? '')),
            'phone' => $record['phone'] ?? '',
            'website' => $record['website'] ?? '',
            'registration_number' => $record['registration_number'] ?? '',
            'company_type' => $record['company_type'] ?? '',
            'address' => $record['address'] ?? '',
            'city' => $record['city'] ?? '',
            'state' => $record['state'] ?? '',
            'postal_code' => $record['postal_code'] ?? '',
            'country' => $record['country'] ?? '',
            'logo_url' => $record['logo_url'] ?? '',
            'notes' => $record['notes'] ?? '',
        ];

        return $normalized;
    }

    private function validateRecords(array $records): array
    {
        $valid = collect();
        $invalid = collect();
        $duplicates = collect();
        $existing = collect();
        $seen = [];

        foreach ($records as $index => $record) {
            $rowNumber = $index + 2; // +2 for header row and 0-index
            $errors = [];

            // Required fields
            if (empty($record['name'])) {
                $errors[] = 'Company name is required';
            }

            if (empty($record['email'])) {
                $errors[] = 'Email is required';
            } elseif (! filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }

            // Check for duplicates within the file
            $nameKey = strtolower(trim($record['name']));
            $emailKey = strtolower($record['email']);

            if (isset($seen[$nameKey])) {
                $errors[] = "Duplicate company name in file: {$record['name']}";
            }
            if (isset($seen[$emailKey])) {
                $errors[] = "Duplicate email in file: {$record['email']}";
            }
            $seen[$nameKey] = true;
            $seen[$emailKey] = true;

            // Check for existing companies in database
            $existingBySlug = Company::where('slug', Str::slug($record['name']))->exists();
            $existingByEmail = Company::whereRaw('LOWER(email) = ?', [strtolower($record['email'])])->exists();

            if ($existingBySlug || $existingByEmail) {
                $existing->push([
                    'row' => $index + 2,
                    'name' => $record['name'],
                    'email' => $record['email'],
                    'reason' => $existingBySlug ? 'Company with this name already exists' : 'Company with this email already exists',
                ]);
            }

            if (! empty($record['logo_url']) && ! filter_var($record['logo_url'], FILTER_VALIDATE_URL)) {
                $errors[] = 'Invalid logo URL format';
            }

            if (! empty($record['website']) && ! filter_var($record['website'], FILTER_VALIDATE_URL)) {
                $errors[] = 'Invalid website URL format';
            }

            if (! in_array($record['company_type'], array_keys(Company::COMPANY_TYPES), true) && ! empty($record['company_type'])) {
                $errors[] = 'Invalid company type. Allowed: '.implode(', ', array_keys(Company::COMPANY_TYPES));
            }

            $record['row'] = $index + 2;

            if (! empty($errors)) {
                $invalid->push([
                    'row' => $rowNumber,
                    'data' => $record,
                    'errors' => $errors,
                ]);
            } elseif (! empty($duplicates) && $duplicates->contains('name', $record['name'])) {
                $duplicates->push([
                    'row' => $rowNumber,
                    'data' => $record,
                    'reason' => 'Duplicate in file',
                ]);
            } elseif (! empty($existing) && $existing->contains('name', $record['name'])) {
                $existing->push([
                    'row' => $rowNumber,
                    'name' => $record['name'],
                    'reason' => 'Already exists in database',
                ]);
            } else {
                $valid->push($record);
            }
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid,
            'duplicates' => $duplicates,
            'existing' => $existing,
            'all' => collect($records),
        ];
    }
}
