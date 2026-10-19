@extends('layouts.app')

@section('title', 'Import History · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management / Import History</p>
        <h1>Import History</h1>
        <p class="admin-sub">View all company import operations and their results.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('admin.companies.import.create') }}">⬆ Import Companies</a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.import.template') }}">⬇ Download Template</a>
        </div>
    </div>
</header>

<div class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Import History</h2><p>{{ $imports->total() }} total import operations</p></div>
    </header>

    @if($imports->isEmpty())
        <p class="admin-page-empty">No import operations yet. <a href="{{ route('admin.companies.import.create') }}">Start your first import</a>.</p>
    @else
        <div style="overflow-x:auto">
            <table class="admin-page-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Created</th>
                        <th>Skipped</th>
                        <th>Failed</th>
                        <th>Success Rate</th>
                        <th>Imported By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $import)
                        <tr>
                            <td><strong>{{ $import->filename }}</strong></td>
                            <td>{{ $import->created_at->format('M j, Y g:i A') }}</td>
                            <td><span class="admin-pill {{ $import->statusTone() }}">{{ $import->statusLabel() }}</span></td>
                            <td>{{ $import->total_rows }}</td>
                            <td><span class="admin-pill ok">{{ $import->created_count }}</span></td>
                            <td><span class="admin-pill warn">{{ $import->skipped_count }}</span></td>
                            <td><span class="admin-pill off">{{ $import->failed_count }}</span></td>
                            <td><strong>{{ $import->successRate() }}%</strong></td>
                            <td>{{ $import->creator->name ?? '—' }}</td>
                            <td style="text-align:right">
                                <a class="admin-page-list-link" href="{{ route('admin.companies.imports.show', $import) }}">View &rarr;</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="admin-page-empty">No import history yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $imports->links() }}
    @endif
</section>
@endsection