@extends('layouts.app')

@section('title', 'Import Companies · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management / Import</p>
        <h1>Import Companies</h1>
        <p class="admin-sub">Upload a CSV or Excel file to register multiple companies at once.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.import.template') }}">⬇ Download CSV Template</a>
        </div>
    </div>
</header>

<div class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Upload File</h2><p>Upload a CSV or XLSX file with company data. Maximum 5MB, 500 rows.</p></div>
    </header>

    <form class="admin-form" method="POST" action="{{ route('admin.companies.import.store') }}" enctype="multipart/form-data">
        @csrf
        <label>Company Data File
            <input type="file" name="file" accept=".csv,.xlsx,.xls" required>
            <small>Supported formats: CSV, XLSX, XLS. Maximum 5MB, 500 rows.</small>
        </label>
        <div class="admin-page-actions">
            <button class="button primary" type="submit" name="action" value="preview">🔍 Preview & Validate</button>
            <a class="button secondary" href="{{ route('admin.companies.index') }}">Cancel</a>
        </div>
    </form>
</section>

@if(isset($preview))
<section class="admin-page-panel" style="margin-top:24px">
    <header class="admin-panel-head">
        <div><h2>Import Preview</h2><p>Review the parsed data before importing. Fix any errors and re-upload if needed.</p></div>
        <div class="admin-tiles" style="grid-template-columns:repeat(4,1fr);margin-top:16px">
            <div class="admin-tile"><b>{{ $preview['total_rows'] }}</b><span>Total Rows</span></div>
            <div class="admin-tile"><b>{{ $preview['valid_rows'] }}</b><span>Valid</span></div>
            <div class="admin-tile warn"><b>{{ $preview['invalid_rows'] }}</b><span>Invalid</span></div>
            <div class="admin-tile"><b>{{ $preview['duplicate_rows'] + $preview['existing_rows'] }}</b><span>Conflicts</span></div>
        </div>
    </header>

    @if($preview['invalid_rows'] > 0)
        <section class="admin-page-panel" style="margin-top:24px">
            <header class="admin-panel-head"><div><h2>Validation Errors ({{ $preview['invalid_rows'] }})</h2></div>
            <div class="admin-page-list">
                @foreach($preview['errors'] as $error)
                    <article class="admin-page-item" style="border-color:#fecaca;background:#fef2f2">
                        <span class="admin-item-icon" style="background:#fef2f2;color:#991b1b">⚠</span>
                        <div class="admin-item-body">
                            <strong>Row {{ $error['row'] }}: {{ $error['data']['name'] ?? 'Unnamed' }}</strong>
                            <small>{{ implode('; ', $error['errors']) }}</small>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($preview['duplicate_rows'] > 0 || $preview['existing_rows'] > 0)
        <section class="admin-page-panel" style="margin-top:24px">
            <header class="admin-panel-head"><div><h2>Conflicts</h2><p>These records conflict with existing data or duplicates in the file.</p></div>
            <div class="admin-page-list">
                @foreach(array_merge($preview['duplicates']->all(), $preview['existing']->all()) as $conflict)
                    <article class="admin-page-item" style="border-color:#fde68a;background:#fffaf0">
                        <span class="admin-item-icon" style="background:#fff9e8;color:#92400e">⚠</span>
                        <div class="admin-item-body">
                            <strong>Row {{ $conflict['row'] }}: {{ $conflict['name'] ?? $conflict['name'] ?? 'Unnamed' }}</strong>
                            <small>{{ $conflict['reason'] ?? $conflict['reason'] ?? 'Conflict detected' }}</small>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($preview['valid_rows'] > 0)
        <form method="POST" action="{{ route('admin.companies.import.store') }}" enctype="multipart/form-data" style="margin-top:24px">
            @csrf
            <input type="hidden" name="file" value="{{ request()->file('file')->getClientOriginalName() }}">
            <input type="hidden" name="confirm" value="1">
            <div class="admin-form" style="display:flex;gap:16px;align-items:end">
                <div><label>Confirm Import<input type="checkbox" name="confirm" value="1" required></label></div>
                <button class="button primary" type="submit" style="height:46px">✅ Import {{ $preview['valid_rows'] }} Companies</button>
            </div>
        </form>
    @endif
</section>
@endif
</section>
@endsection