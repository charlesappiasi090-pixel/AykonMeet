@extends('layouts.app')

@section('title', 'Import Results · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management / Import Results</p>
        <h1>Import Results</h1>
        <p class="admin-sub">View the results of your company import.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('admin.companies.import.create') }}">Import More</a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.imports') }}">View All Imports</a>
        </div>
    </div>
</header>

<div class="admin-tiles" aria-label="Import summary">
    <div class="admin-tile gold"><b>{{ $import->total_rows }}</b><span>Total Rows</span><small>In source file</small></div>
    <div class="admin-tile"><b>{{ $import->valid_rows }}</b><span>Valid Rows</span><small>Passed validation</small></div>
    <div class="admin-tile gold"><b>{{ $import->created_count }}</b><span>Created</span><small>New companies</small></div>
    <div class="admin-tile"><b>{{ $import->skipped_count }}</b><span>Skipped</span><small>Already existed</small></div>
    <div class="admin-tile warn"><b>{{ $import->failed_count }}</b><span>Failed</span><small>Errors during import</small></div>
</div>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Import Summary</h2><p>{{ $import->statusLabel() }} &bull; {{ $import->created_at->format('M j, Y g:i A') }}</p></div>
        <span class="admin-chip">{{ $import->statusLabel() }}</span>
    </header>

    <div class="admin-page-bars">
        <div class="admin-page-bar"><span>Created</span><div class="admin-page-bar-track"><div class="admin-page-bar-fill {{ $import->created_count > 0 ? 'ok' : '' }}" style="width:{{ $import->total_rows > 0 ? round($import->created_count / $import->total_rows * 100) : 0 }}%"></div></div><strong>{{ $import->created_count }}</strong></div>
        <div class="admin-page-bar"><span>Skipped</span><div class="admin-page-bar-track"><div class="admin-page-bar-fill warn" style="width:{{ $import->total_rows > 0 ? round($import->skipped_count / $import->total_rows * 100) : 0 }}%"></div></div><strong>{{ $import->skipped_count }}</strong></div>
        <div class="admin-page-bar"><span>Failed</span><div class="admin-page-bar-track"><div class="admin-page-bar-fill off" style="width:{{ $import->total_rows > 0 ? round($import->failed_count / $import->total_rows * 100) : 0 }}%"></div></div><strong>{{ $import->failed_count }}</strong></div>
    </div>

    <div class="admin-page-bars" style="margin-top:16px">
        <div class="admin-page-bar"><span>Success Rate</span><div class="admin-page-bar-track"><div class="admin-page-bar-fill ok" style="width:{{ $import->successRate() }}%"></div></div><strong>{{ $import->successRate() }}%</strong></div>
    </div>
</section>

@if($import->errors && count($import->errors) > 0)
<section class="admin-page-panel">
    <header class="admin-panel-head"><div><h2>Import Errors ({{ count($import->errors) }})</h2></div>
    <div class="admin-page-list">
        @foreach($import->errors as $error)
            <article class="admin-page-item" style="border-color:#fecaca;background:#fef2f2">
                <span class="admin-item-icon" style="background:#fef2f2;color:#991b1b">⚠</span>
                <div class="admin-item-body">
                    <strong>{{ $error['name'] ?? 'Unnamed' }}</strong>
                    <small>{{ $error['error'] }}</small>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif

@if($import->errors && count($import->errors) > 0)
    <div class="admin-page-actions" style="margin-top:24px">
        <a class="admin-btn admin-btn-gold" href="{{ route('admin.companies.import.create') }}">Retry Import</a>
        <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.imports') }}">Back to History</a>
    </div>
@endif
</section>

@if($import->status === 'completed' && $import->created_count > 0)
<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Created Companies</h2><p>{{ $import->created_count }} new companies were created.</p></div>
    </header>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
        @foreach(\App\Models\Company::where('import_source', 'bulk_import')->where('imported_at', '>=', $import->imported_at)->where('imported_at', '<=', $import->imported_at->addMinute())->get() as $company)
            <div class="admin-page-item">
                <span class="admin-item-icon" style="background:#e8f6ec;color:#10b981">✅</span>
                <div class="admin-item-body">
                    <strong>{{ $company->name }}</strong>
                    <small>{{ $company->email }} &bull; {{ $company->company_type ?? '—' }}</small>
                </div>
                <a class="admin-page-list-link" href="{{ route('admin.companies.show', $company) }}">View &rarr;</a>
            </div>
        @endforeach
    </div>
</section>
@endif
@endsection