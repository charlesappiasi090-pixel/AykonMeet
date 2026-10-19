@extends('layouts.app')

@section('title', 'Edit ' . $company->name . ' · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management / Edit</p>
        <h1>Edit {{ $company->name }}</h1>
        <p class="admin-sub">Update the company information below.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.show', $company) }}">← Back to Company</a>
        </div>
    </div>
</header>

<div class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Edit Company</h2><p>Update the company information below.</p></div>
    </header>

    <form class="admin-form" method="POST" action="{{ route('admin.companies.update', $company) }}" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <label>Company Name <span style="color:#e53e3e">*</span>
            <input type="text" name="name" value="{{ old('name', $company->name) }}" required maxlength="160" placeholder="e.g. Acme Corporation">
        </label>

        <label>Slug
            <input type="text" name="slug" value="{{ old('slug', $company->slug) }}" maxlength="180" placeholder="Auto-generated from name">
            <small>Auto-generated from name if left blank. Must be unique.</small>
        </label>

        <label>Email
            <input type="email" name="email" value="{{ old('email', $company->email) }}" maxlength="160" placeholder="contact@company.com">
        </label>

        <label>Phone
            <input type="tel" name="phone" value="{{ old('phone', $company->phone) }}" maxlength="40" placeholder="+1-555-123-4567">
        </label>

        <label>Website
            <input type="url" name="website" value="{{ old('website', $company->website) }}" maxlength="255" placeholder="https://company.com">
        </label>

        <label>Registration Number
            <input type="text" name="registration_number" value="{{ old('registration_number', $company->registration_number) }}" maxlength="80" placeholder="REG-12345">
        </label>

        <label>Company Type
            <select name="company_type">
                <option value="">Select Type</option>
                @foreach($companyTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('company_type', $company->company_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>Address
            <input type="text" name="address" value="{{ old('address', $company->address) }}" maxlength="255" placeholder="123 Main St, Suite 100">
        </label>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <label>City<input type="text" name="city" value="{{ old('city', $company->city) }}" maxlength="100" placeholder="New York"></label>
            <label>State/Province<input type="text" name="state" value="{{ old('state', $company->state) }}" maxlength="100" placeholder="NY"></label>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <label>Postal Code<input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" maxlength="20" placeholder="10001"></label>
            <label>Country<input type="text" name="country" value="{{ old('country', $company->country) }}" maxlength="100" placeholder="USA"></label>
        </div>

        <label>Notes
            <textarea name="notes" maxlength="2000" rows="3" placeholder="Internal notes...">{{ old('notes', $company->notes) }}</textarea>
        </label>

        <label>Status
            <select name="status" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ old('status', $company->status) === $status ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </label>

        <label>Logo
            <input type="file" name="logo" accept="image/*">
            <small>PNG, JPG, or SVG — max 2MB. Square aspect ratio recommended.</small>
            @if($company->logo_path)
                <div style="margin-top:8px">
                    <img src="{{ asset('storage/' . $company->logo_path) }}" alt="{{ $company->name }}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0">
                </div>
            @endif
        </label>

        <div class="admin-page-actions">
            <button class="button primary" type="submit">Save Changes</button>
            <a class="button secondary" href="{{ route('admin.companies.show', $company) }}">Cancel</a>
        </div>
    </form>
</section>
@endsection