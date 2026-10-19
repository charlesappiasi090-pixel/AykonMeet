@extends('layouts.app')

@section('content')
<style>
    .hybrid-create-page { max-width: 1200px; margin: 0 auto; }
    .hybrid-create-page [hidden] { display: none !important; }
    .hybrid-create-page .section-card { margin-bottom: 24px; }
    .hybrid-create-page .section-title { 
        display: flex; align-items: center; gap: 12px; 
        margin: 0 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--line);
        color: var(--navy); font: 600 18px "Space Grotesk", sans-serif;
    }
    .hybrid-create-page .section-icon { 
        display: grid; place-items: center; width: 36px; height: 36px; 
        background: var(--yellow); color: var(--navy); font-size: 18px; border-radius: 8px;
    }
    .hybrid-create-page .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .hybrid-create-page .form-grid .wide { grid-column: 1 / -1; }
    .hybrid-create-page .form-grid label { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .hybrid-create-page .form-grid label span { color: var(--navy); font: 600 13px "DM Sans", sans-serif; }
    .hybrid-create-page .form-grid label small { color: #6b8098; font-size: 11px; font-weight: 400; }
    .hybrid-create-page .form-grid input,
    .hybrid-create-page .form-grid select,
    .hybrid-create-page .form-grid textarea {
        width: 100%; min-width: 0; min-height: 40px;
        padding: 10px 12px; border: 1px solid #c9d5e2; border-radius: 6px;
        background: #fff; color: var(--navy); font: 13px "DM Sans", sans-serif;
    }
    .hybrid-create-page .form-grid input:focus,
    .hybrid-create-page .form-grid select:focus,
    .hybrid-create-page .form-grid textarea:focus {
        outline: 3px solid rgba(247,201,72,.22); border-color: #b18800;
    }
    .hybrid-create-page .form-grid textarea { min-height: 100px; resize: vertical; }
    .hybrid-create-page .form-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; }
    .hybrid-create-page .form-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; accent-color: var(--yellow); }
    .hybrid-create-page .form-grid .checkbox-label span { flex: 1; }
    .hybrid-create-page .field-error { color: #c03b2d; font-size: 12px; margin-top: 4px; }
    .hybrid-create-page .required::after { content: " *"; color: #f04452; }
    .hybrid-create-page .recurrence-preview { 
        padding: 12px; background: #f5f9ff; border: 1px solid #d0e4f5; border-radius: 6px; 
        font-size: 13px; color: var(--navy); margin-top: 8px;
    }
    .hybrid-create-page .attendee-row { 
        display: grid; grid-template-columns: 1fr 1fr auto; gap: 8px; align-items: center; margin-bottom: 8px; 
    }
    .hybrid-create-page .btn-add-row { 
        padding: 8px 12px; border: 1px dashed #b18800; border-radius: 6px; 
        background: #fffaf0; color: #8a6d00; font: 600 12px "DM Sans", sans-serif; cursor: pointer;
    }
    .hybrid-create-page .btn-add-row:hover { background: #fff3d6; }
    .hybrid-create-page .btn-remove { 
        width: 36px; height: 36px; display: grid; place-items: center;
        border: 1px solid #e3b7b7; border-radius: 6px; background: #fff3f3; 
        color: #c03b2d; font-size: 18px; font-weight: 700; cursor: pointer;
    }
    .hybrid-create-page .btn-remove:hover { background: #fbe0e0; }
    .hybrid-create-page .form-actions { 
        display: flex; gap: 12px; align-items: center; justify-content: flex-end; 
        padding-top: 20px; border-top: 1px solid var(--line); margin-top: 24px;
    }
    .hybrid-create-page .button { 
        padding: 12px 24px; border-radius: 8px; font: 700 13px "DM Sans", sans-serif; 
        cursor: pointer; transition: all .15s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    }
    .hybrid-create-page .button.primary { border: 1px solid var(--yellow); background: var(--yellow); color: var(--navy); }
    .hybrid-create-page .button.primary:hover { background: #ffda69; border-color: #ffda69; }
    .hybrid-create-page .button.secondary { border: 1px solid var(--navy); background: var(--navy); color: #fff; }
    .hybrid-create-page .button.secondary:hover { background: #1a3b60; }
    .hybrid-create-page .button:disabled { opacity: .55; cursor: not-allowed; }
    .hybrid-create-page .radio-group { display: flex; gap: 24px; flex-wrap: wrap; }
    .hybrid-create-page .radio-label { display: flex; align-items: center; gap: 8px; cursor: pointer; }
    .hybrid-create-page .radio-label input { width: 18px; height: 18px; accent-color: var(--yellow); }
    .hybrid-create-page .radio-label span { font: 13px "DM Sans", sans-serif; color: var(--navy); }
    .hybrid-create-page .help-text { color: #596a7c; font-size: 12px; margin-top: 4px; }
    .hybrid-create-page .venue-unlimited-note { color: #596a7c; font-size: 12px; margin-top: 4px; }
    .hybrid-create-page .settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .hybrid-create-page .settings-grid label { display: flex; flex-direction: column; gap: 6px; }
    .hybrid-create-page .settings-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; }
    .hybrid-create-page .settings-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; }
    .hybrid-create-page .settings-grid .checkbox-label span { flex: 1; }

    @media (max-width: 900px) {
        .hybrid-create-page .form-grid { grid-template-columns: 1fr; }
        .hybrid-create-page .settings-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
        .hybrid-create-page .form-actions { flex-direction: column-reverse; }
        .hybrid-create-page .button { width: 100%; justify-content: center; }
    }
</style>

@section('content')
<div class="hybrid-create-page">
    <div class="back-link" style="margin-bottom: 16px;">
        <a href="{{ route('admin.dashboard') }}" style="display:inline-flex;align-items:center;gap:6px;color:#65758a;font-size:13px;font-weight:600;text-decoration:none">← Back to meetings</a>
    </div>

    <header style="margin-bottom: 24px;">
        <span class="eyebrow" style="color: var(--yellow); font: 800 11px 'Space Grotesk', sans-serif; letter-spacing: 2px; text-transform: uppercase;">CREATE HYBRID MEETING</span>
        <h1 style="margin: 8px 0 4px; color: var(--navy); font: 700 32px 'Fraunces', Georgia, serif;">New Hybrid Meeting</h1>
        <p style="color: #596a7c; font-size: 14px;">Schedule a meeting with both in-person and online participants</p>
    </header>

    <form class="form-panel" action="{{ route('meetings.store') }}" method="post" id="hybridCreateForm">
        @csrf
        <input type="hidden" name="meeting_type" value="hybrid">

        {{-- SECTION A: MEETING INFORMATION --}}
        <section class="section-card panel" style="padding: 24px;">
            <h2 class="section-title"><span class="section-icon">📋</span> A. Meeting Information</h2>
            <div class="form-grid">
                <label><span class="required">Meeting Title</span>
                    <input type="text" name="meeting_topic" value="{{ old('meeting_topic') }}" required maxlength="180" placeholder="Enter meeting title">
                    @error('meeting_topic')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="wide"><span>Description</span>
                    <textarea name="description" maxlength="2000" placeholder="Add a brief description about the meeting...">{{ old('description') }}</textarea>
                </label>
                <label><span class="required">Organizer Name</span>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required maxlength="120" placeholder="Your name">
                    @error('name')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label><span>Company / Organization</span>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" maxlength="160" placeholder="e.g. Aykon Group">
                </label>
                <label><span class="required">Email</span>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required maxlength="160" placeholder="your@email.com">
                    @error('email')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label><span class="required">Phone</span>
                    <input type="text" name="phone" value="{{ old('phone') }}" required maxlength="10" placeholder="10-digit number">
                    @error('phone')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        {{-- SECTION B: DATE AND TIME --}}
        <section class="section-card panel" style="padding: 24px;">
            <h2 class="section-title"><span class="section-icon">📅</span> B. Date and Time</h2>
            <div class="form-grid">
                <label><span class="required">Meeting Date</span>
                    <input type="date" name="start_date" value="{{ old('start_date', now()->addDay()->format('Y-m-d')) }}" required>
                    @error('start_date')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label><span class="required">Start Time</span>
                    <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required>
                </label>
                <label><span class="required">End Time</span>
                    <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}" required>
                </label>
                <label><span class="required">Time Zone</span>
                    <select name="timezone" required>
                        <option value="UTC" @selected(old('timezone', 'UTC') === 'UTC')>UTC</option>
                        <option value="America/New_York" @selected(old('timezone') === 'America/New_York')>Eastern Time (ET)</option>
                        <option value="America/Chicago" @selected(old('timezone') === 'America/Chicago')>Central Time (CT)</option>
                        <option value="America/Denver" @selected(old('timezone') === 'America/Denver')>Mountain Time (MT)</option>
                        <option value="America/Los_Angeles" @selected(old('timezone') === 'America/Los_Angeles')>Pacific Time (PT)</option>
                        <option value="Europe/London" @selected(old('timezone') === 'Europe/London')>London (GMT/BST)</option>
                        <option value="Europe/Paris" @selected(old('timezone') === 'Europe/Paris')>Paris (CET/CEST)</option>
                        <option value="Asia/Dubai" @selected(old('timezone') === 'Asia/Dubai')>Dubai (GST)</option>
                        <option value="Asia/Singapore" @selected(old('timezone') === 'Asia/Singapore')>Singapore (SGT)</option>
                        <option value="Asia/Tokyo" @selected(old('timezone') === 'Asia/Tokyo')>Tokyo (JST)</option>
                        <option value="Australia/Sydney" @selected(old('timezone') === 'Australia/Sydney')>Sydney (AEST/AEDT)</option>
                    </select>
                </label>
            </div>

            {{-- Recurrence --}}
            <div class="form-grid" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--line);">
                <div class="wide">
                    <h3 style="margin: 0 0 12px; color: var(--navy); font: 600 14px 'Space Grotesk', sans-serif;">Recurrence (Optional)</h3>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="recurrence_type" value="none" @checked(old('recurrence_type', 'none') === 'none')>
                            <span>Does not repeat</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="recurrence_type" value="daily" @checked(old('recurrence_type') === 'daily')>
                            <span>Daily</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="recurrence_type" value="weekly" @checked(old('recurrence_type') === 'weekly')>
                            <span>Weekly</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="recurrence_type" value="monthly" @checked(old('recurrence_type') === 'monthly')>
                            <span>Monthly</span>
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="recurrence_type" value="quarterly" @checked(old('recurrence_type') === 'quarterly')>
                            <span>Quarterly</span>
                        </label>
                    </div>
                </div>

                <div id="weekly-options" style="display: {{ old('recurrence_type') === 'weekly' ? 'block' : 'none' }};">
                    <div class="form-grid" style="margin-top: 12px;">
                        <label><span>Repeat every</span>
                            <input type="number" name="recurrence_interval" value="{{ old('recurrence_interval', 1) }}" min="1" max="52" style="width: 80px;">
                            <small>weeks</small>
                        </label>
                        <div class="wide">
                            <span style="font-weight: 600; color: var(--navy); display: block; margin-bottom: 8px;">Days of week:</span>
                            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                                @foreach([1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat',7=>'Sun'] as $num => $day)
                                <label style="display:flex;align-items:center;gap:6px;cursor:pointer">
                                    <input type="checkbox" name="recurrence_days_of_week[]" value="{{ $num }}" @checked(in_array($num, old('recurrence_days_of_week', []))) style="width:16px;height:16px;accent-color:var(--yellow)">
                                    <span style="font-size:12px">{{ $day }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div id="monthly-options" style="display: {{ old('recurrence_type') === 'monthly' ? 'block' : 'none' }};">
                    <div class="form-grid" style="margin-top: 12px;">
                        <label><span>Repeat every</span>
                            <input type="number" name="recurrence_interval" value="{{ old('recurrence_interval', 1) }}" min="1" max="12" style="width: 80px;">
                            <small>months</small>
                        </label>
                        <label><span>Day of month</span>
                            <input type="number" name="recurrence_day_of_month" value="{{ old('recurrence_day_of_month', 1) }}" min="1" max="28" style="width: 80px;">
                            <small>1-28 (use 28 for last day)</small>
                        </label>
                    </div>
                </div>

                <div id="quarterly-options" style="display: {{ old('recurrence_type') === 'quarterly' ? 'block' : 'none' }};">
                    <div class="form-grid" style="margin-top: 12px;">
                        <label><span>Day of month</span>
                            <input type="number" name="recurrence_day_of_month" value="{{ old('recurrence_day_of_month', 1) }}" min="1" max="28" style="width: 80px;">
                            <small>1-28 (use 28 for last day)</small>
                        </label>
                    </div>
                </div>

                <div class="wide" style="margin-top: 12px;">
                    <label><span>Ends</span>
                        <select name="recurrence_end_type" id="recurrence-end-type">
                            <option value="never" @selected(old('recurrence_end_type', 'never') === 'never')>Never</option>
                            <option value="on_date" @selected(old('recurrence_end_type') === 'on_date')>On date</option>
                            <option value="after_occurrences" @selected(old('recurrence_end_type') === 'after_occurrences')>After</option>
                        </select>
                    </label>
                </div>
                <div id="recurrence-end-date-field" style="display: {{ old('recurrence_end_type') === 'on_date' ? 'block' : 'none' }};">
                    <label class="wide"><span>End date</span>
                        <input type="date" name="recurrence_end_date" value="{{ old('recurrence_end_date') }}" min="{{ old('start_date', now()->format('Y-m-d')) }}">
                    </label>
                </div>
                <div id="recurrence-end-occurrences-field" style="display: {{ old('recurrence_end_type') === 'after_occurrences' ? 'block' : 'none' }};">
                    <label><span>Occurrences</span>
                        <input type="number" name="recurrence_max_occurrences" value="{{ old('recurrence_max_occurrences', 10) }}" min="1" max="1000" style="width: 100px;">
                    </label>
                </div>

                <div class="wide recurrence-preview" id="recurrence-preview">
                    <strong>Preview:</strong> <span id="recurrence-preview-text">This meeting will not repeat.</span>
                </div>
            </div>
        </section>

        {{-- SECTION C: PHYSICAL VENUE --}}
        <section class="section-card panel" style="padding: 24px;">
            <h2 class="section-title"><span class="section-icon">🏢</span> C. Physical Venue</h2>
            <div class="form-grid">
                <label class="wide"><span class="required">Venue Name</span>
                    <input type="text" name="venue_name" value="{{ old('venue_name') }}" required maxlength="255" placeholder="e.g. Aykon Conference Center">
                    @error('venue_name')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="wide"><span class="required">Physical Address</span>
                    <textarea name="venue_address" required rows="2" placeholder="Street address, city, state, ZIP">{{ old('venue_address') }}</textarea>
                    @error('venue_address')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label><span>Building / Room</span>
                    <input type="text" name="venue_building_room" value="{{ old('venue_building_room') }}" maxlength="255" placeholder="e.g. Building A, Room 304">
                </label>
                <label class="wide"><span>Additional Instructions</span>
                    <textarea name="venue_instructions" rows="2" placeholder="Parking info, entrance details, accessibility notes...">{{ old('venue_instructions') }}</textarea>
                </label>
                <label><span>Venue Capacity</span>
                    <input type="number" name="venue_capacity" value="{{ old('venue_capacity') }}" min="1" placeholder="Leave blank for unlimited">
                    <small class="venue-unlimited-note">Leave blank for unlimited capacity (unless organization policy imposes a limit)</small>
                    @error('venue_capacity')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>
        </section>

        {{-- SECTION D: ONLINE MEETING --}}
        <section class="section-card panel" style="padding: 24px;">
            <h2 class="section-title"><span class="section-icon">💻</span> D. Online Meeting</h2>
            <p style="color: #596a7c; font-size: 13px; margin-bottom: 16px;">A secure AykonMeet video call link will be automatically generated when the meeting is created.</p>
            <div class="form-grid">
                <div class="wide" style="background: #f5f9ff; border: 1px solid #d0e4f5; border-radius: 8px; padding: 16px;">
                    <strong style="color: var(--navy);">🔗 Online Meeting Link</strong>
                    <p style="margin: 8px 0 0; color: #596a7c; font-size: 13px;">Generated automatically on save. Participants join through the built-in video call.</p>
                </div>
            </div>

            <h3 style="margin: 20px 0 12px; color: var(--navy); font: 600 14px 'Space Grotesk', sans-serif;">Online Meeting Settings</h3>
            <div class="settings-grid">
                <label class="checkbox-label">
                    <input type="checkbox" name="waiting_room" value="1" @checked(old('waiting_room', true))>
                    <span>Waiting room (host admits participants)</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="mute_on_entry" value="1" @checked(old('mute_on_entry'))>
                    <span>Mute participants on entry</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_participant_unmute" value="1" @checked(old('allow_participant_unmute', true))>
                    <span>Allow participants to unmute themselves</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_participant_camera" value="1" @checked(old('allow_participant_camera', true))>
                    <span>Allow participants to turn on camera</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_screen_share" value="1" @checked(old('allow_screen_share', true))>
                    <span>Allow screen sharing</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_chat" value="1" @checked(old('allow_chat', true))>
                    <span>Allow chat</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_recording" value="1" @checked(old('allow_recording'))>
                    <span>Allow recording (host only)</span>
                </label>
                <label>
                    <span>Max Participants</span>
                    <input type="number" name="max_participants" value="{{ old('max_participants') }}" min="1" placeholder="Unlimited">
                </label>
                <label>
                    <span>Waiting Room Message</span>
                    <input type="text" name="waiting_room_message" value="{{ old('waiting_room_message') }}" maxlength="500" placeholder="Custom message for waiting participants">
                </label>
            </div>

            <h3 style="margin: 20px 0 12px; color: var(--navy); font: 600 14px 'Space Grotesk', sans-serif;">Meeting Reminders</h3>
            <div class="settings-grid">
                <label>
                    <span>Minutes before</span>
                    <input type="number" name="reminder_before_minutes" value="{{ old('reminder_before_minutes') }}" min="5" placeholder="e.g. 15">
                </label>
                <label>
                    <span>Hours before</span>
                    <input type="number" name="reminder_before_hours" value="{{ old('reminder_before_hours') }}" min="1" placeholder="e.g. 1">
                </label>
                <label>
                    <span>Days before</span>
                    <input type="number" name="reminder_before_days" value="{{ old('reminder_before_days') }}" min="1" placeholder="e.g. 1">
                </label>
            </div>
        </section>

        {{-- SECTION E: PARTICIPANTS AND INVITATIONS --}}
        <section class="section-card panel" style="padding: 24px;">
            <h2 class="section-title"><span class="section-icon">👥</span> E. Participants and Invitations</h2>
            <div class="form-grid">
                <div class="wide">
                    <label class="checkbox-label" style="margin-bottom: 16px;">
                        <input type="checkbox" name="require_registration" value="1" @checked(old('require_registration'))>
                        <span>Require registration (participants must RSVP before joining)</span>
                    </label>
                    <label class="checkbox-label" style="margin-right: 24px;">
                        <input type="checkbox" name="allow_rsvp_maybe" value="1" @checked(old('allow_rsvp_maybe', true))>
                        <span>Allow "Maybe" RSVP option</span>
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="allow_rsvp_another_time" value="1" @checked(old('allow_rsvp_another_time', true))>
                        <span>Allow "Request Another Time" RSVP option</span>
                    </label>
                </div>
            </div>

            <h3 style="margin: 20px 0 12px; color: var(--navy); font: 600 14px 'Space Grotesk', sans-serif;">Invite Participants</h3>
            <p class="help-text">Add participants who will receive individual invitation links with RSVP options.</p>
            <div id="attendeeRows">
                @forelse(old('attendees', [['full_name' => '', 'email' => '']]) as $index => $attendee)
                <div class="attendee-row">
                    <label class="mdd-field" style="margin:0"><span>Full Name</span>
                        <input type="text" name="attendees[{{ $index }}][full_name]" value="{{ $attendee['full_name'] ?? '' }}" required maxlength="160" placeholder="e.g. John Doe"></label>
                    <label class="mdd-field" style="margin:0"><span>Email</span>
                        <input type="email" name="attendees[{{ $index }}][email]" value="{{ $attendee['email'] ?? '' }}" required maxlength="160" placeholder="e.g. john@example.com"></label>
                    <button type="button" class="btn-remove" aria-label="Remove participant" onclick="this.closest('.attendee-row').remove(); updateAttendeeIndices();">&times;</button>
                </div>
                @empty
                <div class="attendee-row">
                    <label class="mdd-field" style="margin:0"><span>Full Name</span>
                        <input type="text" name="attendees[0][full_name]" required maxlength="160" placeholder="e.g. John Doe"></label>
                    <label class="mdd-field" style="margin:0"><span>Email</span>
                        <input type="email" name="attendees[0][email]" required maxlength="160" placeholder="e.g. john@example.com"></label>
                    <button type="button" class="btn-remove" aria-label="Remove participant" style="visibility: hidden;">&times;</button>
                </div>
                @endforelse
            </div>
            <button type="button" class="btn-add-row" onclick="addAttendeeRow()">+ Add another participant</button>

            <div style="margin-top: 16px; padding: 12px; background: #f5f9ff; border: 1px solid #d0e4f5; border-radius: 6px; font-size: 12px; color: var(--navy);">
                <strong>RSVP Options available to invitees:</strong>
                <ul style="margin: 8px 0 0; padding-left: 20px;">
                    <li><strong>Accept</strong> - Will attend</li>
                    <li><strong>Decline</strong> - Cannot attend</li>
                    <li @if(!old('allow_rsvp_maybe', true)) style="color: #999; text-decoration: line-through;" @endif><strong>Maybe</strong> - Might attend</li>
                    <li @if(!old('allow_rsvp_another_time', true)) style="color: #999; text-decoration: line-through;" @endif><strong>Request Another Time</strong> - Propose alternative</li>
                </ul>
            </div>
        </section>

        {{-- SECTION F: ADDITIONAL SETTINGS --}}
        <section class="section-card panel" style="padding: 24px;">
            <h2 class="section-title"><span class="section-icon">⚙️</span> F. Additional Settings</h2>
            <div class="settings-grid">
                <label class="checkbox-label">
                    <input type="checkbox" name="enable_document_sharing" value="1" @checked(old('enable_document_sharing', true))>
                    <span>Enable document sharing for this meeting</span>
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="allow_participant_upload" value="1" @checked(old('allow_participant_upload'))>
                    <span>Allow participants to upload documents</span>
                </label>
            </div>
        </section>

        {{-- SUBMIT ACTIONS --}}
        <div class="form-actions">
            <button type="button" class="button secondary" onclick="window.history.back()">Cancel</button>
            <button type="submit" class="button primary" id="submitBtn">
                <span>Create Hybrid Meeting</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="transition: transform .15s ease;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recurrence type handler
    const recurrenceTypeRadios = document.querySelectorAll('input[name="recurrence_type"]');
    const weeklyOptions = document.getElementById('weekly-options');
    const monthlyOptions = document.getElementById('monthly-options');
    const quarterlyOptions = document.getElementById('quarterly-options');
    const recurrenceEndType = document.getElementById('recurrence-end-type');
    const recurrenceEndDateField = document.getElementById('recurrence-end-date-field');
    const recurrenceEndOccurrencesField = document.getElementById('recurrence-end-occurrences-field');
    const previewText = document.getElementById('recurrence-preview-text');

    function updateRecurrenceUI() {
        const type = document.querySelector('input[name="recurrence_type"]:checked').value;
        weeklyOptions.style.display = type === 'weekly' ? 'block' : 'none';
        monthlyOptions.style.display = type === 'monthly' ? 'block' : 'none';
        quarterlyOptions.style.display = type === 'quarterly' ? 'block' : 'none';
        updateRecurrencePreview();
    }

    function updateRecurrenceEndUI() {
        const endType = recurrenceEndType.value;
        recurrenceEndDateField.style.display = endType === 'on_date' ? 'block' : 'none';
        recurrenceEndOccurrencesField.style.display = endType === 'after_occurrences' ? 'block' : 'none';
        updateRecurrencePreview();
    }

    function updateRecurrencePreview() {
        const type = document.querySelector('input[name="recurrence_type"]:checked').value;
        const interval = parseInt(document.querySelector('input[name="recurrence_interval"]')?.value) || 1;
        const endType = recurrenceEndType.value;
        const endDate = document.querySelector('input[name="recurrence_end_date"]')?.value;
        const maxOccurrences = document.querySelector('input[name="recurrence_max_occurrences"]')?.value;

        if (type === 'none') {
            previewText.textContent = 'This meeting will not repeat.';
            return;
        }

        const freqLabels = { daily: 'day', weekly: 'week', monthly: 'month', quarterly: 'quarter' };
        let text = `Repeats every ${interval > 1 ? interval + ' ' : ''}${freqLabels[type]}${interval > 1 ? 's' : ''}`;

        if (type === 'weekly') {
            const checkedDays = document.querySelectorAll('#weekly-options input[type="checkbox"]:checked');
            if (checkedDays.length > 0) {
                const dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
                const days = Array.from(checkedDays).map(cb => dayNames[parseInt(cb.value) - 1]);
                text += ' on ' + days.join(', ');
            }
        } else if (type === 'monthly' || type === 'quarterly') {
            const dayOfMonth = document.querySelector('input[name="recurrence_day_of_month"]')?.value;
            if (dayOfMonth) text += ` on day ${dayOfMonth}`;
        }

        if (endType === 'on_date' && endDate) {
            text += `, ends on ${endDate}`;
        } else if (endType === 'after_occurrences' && maxOccurrences) {
            text += `, ends after ${maxOccurrences} occurrences`;
        } else {
            text += ', never ends';
        }

        previewText.textContent = text + '.';
    }

    recurrenceTypeRadios.forEach(radio => radio.addEventListener('change', updateRecurrenceUI));
    recurrenceEndType.addEventListener('change', updateRecurrenceEndUI);
    document.querySelector('input[name="recurrence_interval"]')?.addEventListener('input', updateRecurrencePreview);
    document.querySelector('input[name="recurrence_end_date"]')?.addEventListener('input', updateRecurrencePreview);
    document.querySelector('input[name="recurrence_max_occurrences"]')?.addEventListener('input', updateRecurrencePreview);
    document.querySelectorAll('#weekly-options input[type="checkbox"]').forEach(cb => 
        cb.addEventListener('change', updateRecurrencePreview)
    );
    document.querySelector('input[name="recurrence_day_of_month"]')?.addEventListener('input', updateRecurrencePreview);

    // Initialize
    updateRecurrenceUI();
    updateRecurrenceEndUI();

    // Combine date and time for start_date/end_date
    const form = document.getElementById('hybridCreateForm');
    form.addEventListener('submit', function(e) {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endDate = document.querySelector('input[name="start_date"]').value; // Same date for now
        const endTime = document.querySelector('input[name="end_time"]').value;

        // Create hidden inputs for datetime
        const startDateTime = document.createElement('input');
        startDateTime.type = 'hidden';
        startDateTime.name = 'start_date';
        startDateTime.value = startDate + 'T' + startTime;
        form.appendChild(startDateTime);

        const endDateTime = document.createElement('input');
        endDateTime.type = 'hidden';
        endDateTime.name = 'end_date';
        endDateTime.value = endDate + 'T' + endTime;
        form.appendChild(endDateTime);

        // Disable submit button to prevent double submission
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Creating...</span><svg class="spinner" width="16" height="16" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 100" style="animation: spin 1s linear infinite;"></svg>';
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    });

    // Attendee row management
    let attendeeIndex = document.querySelectorAll('#attendeeRows .attendee-row').length - 1;

    function addAttendeeRow() {
        attendeeIndex++;
        const container = document.getElementById('attendeeRows');
        const template = container.querySelector('.attendee-row');
        const clone = template.cloneNode(true);
        clone.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, '[' + attendeeIndex + ']');
            input.value = '';
        });
        clone.querySelector('.btn-remove').style.visibility = 'visible';
        clone.querySelector('.btn-remove').onclick = function() {
            this.closest('.attendee-row').remove();
            updateAttendeeIndices();
        };
        container.appendChild(clone);
    }

    function updateAttendeeIndices() {
        const rows = document.querySelectorAll('#attendeeRows .attendee-row');
        rows.forEach((row, index) => {
            row.querySelectorAll('input').forEach(input => {
                input.name = input.name.replace(/\[\d+\]/, '[' + index + ']');
            });
            // Hide remove button if only one row
            const removeBtn = row.querySelector('.btn-remove');
            if (rows.length === 1) removeBtn.style.visibility = 'hidden';
            else removeBtn.style.visibility = 'visible';
        });
    }

    // Make remove buttons work
    document.querySelectorAll('.btn-remove').forEach(btn => {
        btn.onclick = function() {
            const rows = document.querySelectorAll('#attendeeRows .attendee-row');
            if (rows.length > 1) {
                this.closest('.attendee-row').remove();
                updateAttendeeIndices();
            }
        };
    });
});
</script>
@endpush