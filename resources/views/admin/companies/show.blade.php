@extends('layouts.app')

@section('title', $company->name . ' · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management / {{ $company->name }}</p>
        <h1>{{ $company->name }}</h1>
        <p class="admin-sub">Company details and management.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('admin.companies.edit', $company) }}">✏️ Edit</a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.index') }}">← Back to Companies</a>
        </div>
    </div>
    <div class="admin-tiles">
        <div class="admin-tile"><b>{{ $memberships->where('status', 'verified')->count() }}</b><span>Verified Members</span></div>
        <div class="admin-tile"><b>{{ $memberships->where('status', 'invited')->count() }}</b><span>Invited</span></div>
        <div class="admin-tile"><b>{{ $meetings->count() }}</b><span>Meetings</span></div>
        <div class="admin-tile"><b>{{ $company->meetingCompanies()->count() }}</b><span>Multi-Company Meetings</span></div>
    </div>
</header>

<div class="admin-tiles" aria-label="Company overview">
    <div class="admin-tile"><b>{{ $company->email ?: '—' }}</b><span>Contact Email</span></div>
    <div class="admin-tile"><b>{{ $company->phone ?: '—' }}</b><span>Phone</span></div>
    <div class="admin-tile"><b>{{ ucfirst($company->company_type ?? '—') }}</b><span>Type</span></div>
    <div class="admin-tile"><b>{{ $company->statusLabel() }}</b><span>Status</span></div>
    <div class="admin-tile"><b>{{ $company->registration_number ?: '—' }}</b><span>Reg. Number</span></div>
    <div class="admin-tile"><b>{{ $company->city ?: '—' }}, {{ $company->state ?: '—' }}</b><span>Location</span></div>
    <div class="admin-tile"><b>{{ $company->created_at->format('M j, Y') }}</b><span>Registered</span></div>
</div>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Company Details</h2></div>
    </header>

    @if($company->logo_path)
        <div style="float:right;margin-bottom:24px">
            <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" style="width:160px;height:160px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0">
        </div>
    @endif

    <dl style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px">
        <dt>Name</dt><dd><strong>{{ $company->name }}</strong></dd>
        <dt>Slug</dt><dd><code>{{ $company->slug }}</code></dd>
        <dt>Email</dt><dd>{{ $company->email ?: '—' }}</dd>
        <dt>Phone</dt><dd>{{ $company->phone ?: '—' }}</dd>
        <dt>Website</dt><dd>{{ $company->website ? '<a href="'.$company->website.'" target="_blank">'.$company->website.'</a>' : '—' }}</dd>
        <dt>Registration Number</dt><dd>{{ $company->registration_number ?: '—' }}</dd>
        <dt>Type</dt><dd>{{ ucfirst($company->company_type ?? '—') }}</dd>
        <dt>Address</dt><dd>{{ $company->address ?: '—' }}</dd>
        <dt>City</dt><dd>{{ $company->city ?: '—' }}</dd>
        <dt>State/Province</dt><dd>{{ $company->state ?: '—' }}</dd>
        <dt>Postal Code</dt><dd>{{ $company->postal_code ?: '—' }}</dd>
        <dt>Country</dt><dd>{{ $company->country ?: '—' }}</dd>
        <dt>Status</dt><dd><span class="admin-pill {{ $company->statusTone() }}">{{ $company->statusLabel() }}</span></dd>
        <dt>Created</dt><dd>{{ $company->created_at->format('M j, Y g:i A') }}</dd>
        <dt>Updated</dt><dd>{{ $company->updated_at->format('M j, Y g:i A') }}</dd>
    </dl>

    @if($company->notes)
    <div class="admin-page-note" style="margin-top:24px">
        <strong>Notes:</strong> {{ $company->notes }}
    </div>
    @endif
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Memberships ({{ $memberships->count() }})</h2><p>Users associated with this company.</p></div>
    </header>
    @if($memberships->isEmpty())
        <p class="admin-page-empty">No members yet. Memberships are created when users accept company invitations.</p>
    @else
        <div style="overflow-x:auto">
            <table class="admin-page-table">
                <thead><tr><th>Member</th><th>Role</th><th>Status</th><th>Verified</th><th>Joined</th></tr></thead>
                <tbody>
                    @foreach($memberships as $membership)
                        <tr>
                            <td><strong>{{ $membership->full_name }}</strong><small>{{ $membership->email }}</small></td>
                            <td>{{ ucfirst($membership->role) }}</td>
                            <td><span class="admin-pill {{ $membership->status === 'verified' ? 'ok' : ($membership->status === 'invited' ? 'warn' : 'off') }}">{{ ucfirst($membership->status) }}</span></td>
                            <td>{{ $membership->isVerified() ? '✓ Verified' : '—' }}</td>
                            <td>{{ $membership->created_at->format('M j, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Meetings ({{ $meetings->count() }})</h2><p>Multi-company meetings this organization is part of.</p></div>
    </header>
    @if($meetings->isEmpty())
        <p class="admin-page-empty">This company is not yet part of any multi-company meetings.</p>
    @else
        <div style="overflow-x:auto">
            <table class="admin-page-table">
                <thead><tr><th>Meeting</th><th>Date</th><th>Type</th><th>Status</th><th>Role</th></tr></thead>
                <tbody>
                    @foreach($meetings as $mc)
                        <tr>
                            <td><strong>{{ $mc->meeting->meeting_topic }}</strong><small>{{ $mc->meeting->meeting_code }}</small></td>
                            <td>{{ $mc->meeting->start_date->format('M j, Y g:i A') }}</td>
                            <td>{{ ucfirst($mc->meeting->meeting_type ?? 'online') }}</td>
                            <td><span class="admin-pill {{ $mc->meeting->dashboardStatus() === 'live' ? 'live' : ($mc->meeting->dashboardStatus() === 'completed' ? 'ok' : 'off') }}">{{ ucfirst($mc->meeting->dashboardStatus()) }}</span></td>
                            <td>{{ $mc->is_host_company ? '🏢 Host' : '🏢 Participant' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection