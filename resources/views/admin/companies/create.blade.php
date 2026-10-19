@extends('layouts.app')

@section('title', 'Add Company · Admin Console · AykonMeet')

@section('content')
@include('admin._styles')

<header class="admin-hero solo">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Company Management / Add Company</p>
        <h1>Add New Company</h1>
        <p class="admin-sub">Register a new company in the platform.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-ghost" href="{{ route('admin.companies.index') }}">← Back to Companies</a>
        </div>
    </div>
</header>

<div class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Company Details</h2><p>Enter the company information below.</p></div>
    </header>

    <form class="admin-form" method="POST" action="{{ route('admin.companies.store') }}" enctype="multipart/form-data">
        @csrf

        <label>Company Name <span style="color:#e53e3e">*</span>
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="160" placeholder="e.g. Acme Corporation">
        </label>

        <label>Slug
            <input type="text" name="slug" value="{{ old('slug') }}" maxlength="180" placeholder="Auto-generated from name">
            <small>Auto-generated from name if left blank. Must be unique.</small>
        </label>

        <label>Email
            <input type="email" name="email" value="{{ old('email') }}" maxlength="160" placeholder="contact@company.com">
        </label>

        <label>Phone
            <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="40" placeholder="+1-555-123-4567">
        </label>

        <label>Website
            <input type="url" name="website" value="{{ old('website') }}" maxlength="255" placeholder="https://company.com">
        </label>

        <label>Registration Number
            <input type="text" name="registration_number" value="{{ old('registration_number') }}" maxlength="80" placeholder="REG-12345">
        </label>

        <label>Company Type
            <select name="company_type">
                <option value="">Select Type</option>
                @foreach($companyTypes as $key => $label)
                    <option value="{{ $key }}" {{ old('company_type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>Address
            <input type="text" name="address" value="{{ old('address') }}" maxlength="255" placeholder="123 Main St, Suite 100">
        </label>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <label>City<input type="text" name="city" value="{{ old('city') }}" maxlength="100" placeholder="New York"></label>
            <label>State/Province<input type="text" name="state" value="{{ old('state') }}" maxlength="100" placeholder="NY"></label>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <label>Postal Code<input type="text" name="postal_code" value="{{ old('postal_code') }}" maxlength="20" placeholder="10001"></label>
            <label>Country<input type="text" name="country" value="{{ old('country') }}" maxlength="100" placeholder="USA"></label>
        </div>

        <label>Notes
            <textarea name="notes" maxlength="2000" rows="3" placeholder="Internal notes...">{{ old('notes') }}</textarea>
        </label>

        <label>Status
            <select name="status" required>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
        </label>

        <label>Logo
            <input type="file" name="logo" accept="image/*">
            <small>PNG, JPG, or SVG — max 2MB. Square aspect ratio recommended.</small>
        </label>

        <div class="admin-page-actions">
            <button class="button primary" type="submit">Create Company</button>
            <a class="button secondary" href="{{ route('admin.companies.index') }}">Cancel</a>
        </div>
    </form>
</section>
@endsection