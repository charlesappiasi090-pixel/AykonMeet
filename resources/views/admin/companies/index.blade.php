@extends('layouts.app')

@section('title', 'Company Management · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management</p>
        <h1>Company Management</h1>
        <p class="admin-sub">Manage platform-wide companies, bulk imports, and organization settings.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('admin.companies.import.create') }}">
                <span>⬆</span> Import Companies
            </a>
            <a class="admin-btn admin-btn-gold" href="{{ route('admin.companies.import.template') }}">
                <span>⬇</span> Download CSV Template
            </a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.imports') }}">
                <span>📋</span> Import History
            </a>
        </div>
    </div>
</header>

<div class="admin-tiles" aria-label="Company management overview">
    <div class="admin-tile gold"><b>{{ $companies->where('status', 'active')->count() }}</b><span>Active Companies</span><small>Ready for meetings</small></div>
    <div class="admin-tile"><b>{{ $companies->where('status', 'pending_approval')->count() }}</b><span>Pending Approval</span><small>Awaiting review</small></div>
    <div class="admin-tile"><b>{{ $companies->where('status', 'inactive')->count() }}</b><span>Inactive</span><small>Not currently active</small></div>
    <div class="admin-tile"><b>{{ $companies->where('status', 'suspended')->count() }}</b><span>Suspended</span><small>Access restricted</small></div>
</div>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>All Companies</h2><p>View, search, and manage all registered companies.</p></div>
        <span class="admin-chip">{{ $companies->count() }} {{ \Illuminate\Support\Str::plural('company', $companies->count()) }}</span>
    </header>

    @if($companies->isEmpty())
        <p class="admin-page-empty">No companies registered yet. <a href="{{ route('admin.companies.import.create') }}">Import your first batch</a>.</p>
    @else
        <div class="admin-page-bars">
            @foreach($companies as $company)
                <div class="admin-page-bar">
                    <span>{{ $company->name }}</span>
                    <div class="admin-page-bar-track"><div class="admin-page-bar-fill" style="width:{{ $company->memberships()->where('status', 'verified')->count() > 0 ? 100 : 0 }}%"></div></div>
                    <strong>{{ $company->memberships()->where('status', 'verified')->count() }}</strong>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Search & Filter</h2><p>Find companies by name, email, status, or type.</p></div>
    </header>
    <form class="admin-form" method="GET" action="{{ route('admin.companies.index') }}">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
            <label>Search<span><input type="text" name="search" value="{{ request('search') }}" placeholder="Company name, email, or registration #"></span></label>
            <label>Status
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Company::STATUSES as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Type
                <select name="type">
                    <option value="">All Types</option>
                    @foreach(\App\Models\Company::COMPANY_TYPES as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Sort By
                <select name="sort">
                    <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="created_asc" {{ request('sort') === 'created_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="members_desc" {{ request('sort') === 'members_desc' ? 'selected' : '' }}>Most Members</option>
                </select>
            </label>
        </div>
        <div class="admin-page-actions"><button class="button primary" type="submit">🔍 Filter</button><a class="button secondary" href="{{ route('admin.companies.index') }}">Clear</a></div>
    </form>
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Companies</h2><p>{{ $companies->count() }} of {{ $companies->total() }} total</p></div>
    </header>

    <div style="overflow-x:auto">
        <table class="admin-page-table">
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Contact</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Members</th>
                    <th>Meetings</th>
                    <th>Created</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                    <tr>
                        <td>
                            <div class="mch-company-cell">
                                @if($company->logo_path)
                                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" class="mch-company-logo">
                                @else
                                    <div class="mch-company-logo-placeholder">
                                        {{ $company->initial() }}
                                    </div>
                                @endif
                                <div>
                                    <strong style="color:#112b49;display:block">{{ $company->name }}</strong>
                                    <small style="font-size:12px;color:#64748b">{{ $company->slug }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="color:#112b49;display:block">{{ $company->email ?: '—' }}</strong>
                            @if($company->phone)
                                <small style="font-size:12px;color:#64748b">{{ $company->phone }}</small>
                            @endif
                            @if($company->website)
                                <small style="font-size:12px;color:#64748b"><a href="{{ $company->website }}" target="_blank" style="color:#3b82f6">{{ $company->website }}</a></small>
                            @endif
                        </td>
                        <td><span class="admin-pill {{ in_array($company->company_type, ['corporation','llc']) ? 'ok' : 'warn' }}">{{ ucfirst($company->company_type ?? '—') }}</span></td>
                        <td><span class="admin-pill {{ $company->statusTone() }}">{{ $company->statusLabel() }}</span></td>
                        <td>{{ $company->memberships()->where('status', 'verified')->count() }}</td>
                        <td>{{ $company->meetingCompanies()->count() }}</td>
                        <td>{{ $company->created_at->format('M j, Y') }}</td>
                        <td style="text-align:right">
                            <div class="admin-page-actions">
                                <a class="admin-act" href="{{ route('admin.companies.show', $company) }}">View</a>
                                <a class="admin-act secondary" href="{{ route('admin.companies.edit', $company) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.companies.destroy', $company) }}" style="display:inline" onsubmit="return confirm('Delete {{ $company->name }}? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button class="admin-act danger" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="admin-page-empty">No companies found matching your criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $companies->links() }}
</section>
@endsection