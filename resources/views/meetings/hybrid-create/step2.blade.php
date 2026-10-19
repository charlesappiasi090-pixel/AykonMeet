@extends('layouts.app')

@section('content')
<style>
    .wizard-page { max-width: 860px; margin: 0 auto; }
    .wizard-progress { margin-bottom: 32px; }
    .wizard-steps { display: flex; gap: 8px; }
    .wizard-step { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; position: relative; }
    .wizard-step:not(:last-child)::after { content: ''; position: absolute; top: 16px; left: 50%; width: 100%; height: 3px; background: var(--line); z-index: 0; }
    .wizard-step.active::after { background: var(--yellow); }
    .wizard-step.completed::after { background: #2f9e5b; }
    .wizard-step-number { width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; font: 700 15px "Space Grotesk", sans-serif; background: var(--line); color: var(--navy); z-index: 1; border: 2px solid transparent; }
    .wizard-step.active .wizard-step-number { background: var(--yellow); color: var(--navy); border-color: var(--yellow); box-shadow: 0 0 0 4px rgba(247,201,72,.2); }
    .wizard-step.completed .wizard-step-number { background: #2f9e5b; color: #fff; border-color: #2f9e5b; }
    .wizard-step-label { font: 600 12px "DM Sans", sans-serif; color: var(--navy); text-align: center; white-space: nowrap; opacity: 0.6; }
    .wizard-step.active .wizard-step-label { color: var(--navy); opacity: 1; font-weight: 700; }
    .wizard-step.completed .wizard-step-label { color: #2f9e5b; opacity: 1; font-weight: 700; }
    .wizard-page [hidden] { display: none !important; }
    .wizard-page .section-card { margin-bottom: 24px; }
    .wizard-page .section-title { display: flex; align-items: center; gap: 12px; margin: 0 0 20px; padding-bottom: 12px; border-bottom: 1px solid var(--line); color: var(--navy); font: 600 18px "Space Grotesk", sans-serif; }
    .wizard-page .section-icon { display: grid; place-items: center; width: 36px; height: 36px; background: var(--yellow); color: var(--navy); font-size: 18px; border-radius: 8px; }
    .wizard-page .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
    .wizard-page .form-grid .wide { grid-column: 1 / -1; }
    .wizard-page .form-grid label { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
    .wizard-page .form-grid label span { color: var(--navy); font: 600 13px "DM Sans", sans-serif; }
    .wizard-page .form-grid label small { color: #6b8098; font-size: 11px; font-weight: 400; }
    .wizard-page .form-grid input, .wizard-page .form-grid select, .wizard-page .form-grid textarea { width: 100%; min-width: 0; min-height: 40px; padding: 10px 12px; border: 1px solid #c9d5e2; border-radius: 6px; background: #fff; color: var(--navy); font: 13px "DM Sans", sans-serif; }
    .wizard-page .form-grid input:focus, .wizard-page .form-grid select:focus, .wizard-page .form-grid textarea:focus { outline: 3px solid rgba(247,201,72,.22); border-color: #b18800; }
    .wizard-page .form-grid textarea { min-height: 100px; resize: vertical; }
    .wizard-page .form-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; cursor: pointer; padding: 8px 12px; border-radius: 6px; border: 1px solid transparent; transition: all .15s ease; }
    .wizard-page .form-grid .checkbox-label:hover { background: #f5f9ff; border-color: #d0e4f5; }
    .wizard-page .form-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; accent-color: var(--yellow); }
    .wizard-page .form-grid .checkbox-label input:focus-visible { outline: 3px solid rgba(247,201,72,.5); outline-offset: 2px; }
    .wizard-page .form-grid .checkbox-label span { flex: 1; }
    .wizard-page .settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .wizard-page .settings-grid label { display: flex; flex-direction: column; gap: 6px; }
    .wizard-page .settings-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; cursor: pointer; padding: 8px 12px; border-radius: 6px; border: 1px solid transparent; transition: all .15s ease; }
    .wizard-page .settings-grid .checkbox-label:hover { background: #f5f9ff; border-color: #d0e4f5; }
    .wizard-page .settings-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; accent-color: var(--yellow); }
    .wizard-page .settings-grid .checkbox-label input:focus-visible { outline: 3px solid rgba(247,201,72,.5); outline-offset: 2px; }
    .wizard-page .settings-grid .checkbox-label span { flex: 1; }
    .wizard-page .field-error { color: #c03b2d; font-size: 12px; margin-top: 4px; }
    .wizard-page .required::after { content: " *"; color: #f04452; }
    .wizard-page .help-text { color: #596a7c; font-size: 12px; margin-top: 4px; }
    .wizard-page .venue-unlimited-note { color: #596a7c; font-size: 12px; margin-top: 4px; }
    .wizard-actions { display: flex; gap: 12px; justify-content: space-between; padding-top: 20px; border-top: 1px solid var(--line); margin-top: 24px; }
    .wizard-page .button { padding: 12px 24px; border-radius: 8px; font: 700 13px "DM Sans", sans-serif; cursor: pointer; transition: all .15s ease; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
    .wizard-page .button { transition: all .15s ease; }
    .wizard-page .button.primary { border: 1px solid var(--yellow); background: var(--yellow); color: var(--navy); }
    .wizard-page .button.primary:hover { background: #ffda69; border-color: #ffda69; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(247,201,72,.3); }
    .wizard-page .button.primary:active { transform: translateY(0); box-shadow: 0 2px 6px rgba(247,201,72,.2); }
    .wizard-page .button.primary:focus-visible { outline: 3px solid rgba(247,201,72,.5); outline-offset: 2px; }
    .wizard-page .button.secondary { border: 1px solid var(--navy); background: var(--navy); color: #fff; }
    .wizard-page .button.secondary:hover { background: #1a3b60; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(17,43,73,.2); }
    .wizard-page .button.secondary:active { transform: translateY(0); }
    .wizard-page .button.secondary:focus-visible { outline: 3px solid rgba(17,43,73,.5); outline-offset: 2px; }
    .wizard-page .button:disabled { opacity: .55; cursor: not-allowed; transform: none !important; box-shadow: none !important; }
    @media (max-width: 900px) { .wizard-page .form-grid { grid-template-columns: 1fr; } .wizard-page .settings-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .wizard-actions { flex-direction: column; } .wizard-page .button { width: 100%; justify-content: center; } }
</style>

@section('content')
<div class="wizard-page">
    <div class="wizard-progress">
        <div class="wizard-steps">
            <div class="wizard-step completed">
                <div class="wizard-step-number">✓</div>
                <span class="wizard-step-label">Meeting Details</span>
            </div>
            <div class="wizard-step active">
                <div class="wizard-step-number">2</div>
                <span class="wizard-step-label">Venue & Online</span>
            </div>
            <div class="wizard-step">
                <div class="wizard-step-number">3</div>
                <span class="wizard-step-label">Participants</span>
            </div>
        </div>
    </div>

    <header style="margin-bottom: 24px;">
        <span class="eyebrow" style="color: var(--yellow); font: 800 11px 'Space Grotesk', sans-serif; letter-spacing: 2px; text-transform: uppercase;">CREATE HYBRID MEETING — Step 2 of 3</span>
        <h1 style="margin: 8px 0 4px; color: var(--navy); font: 700 32px 'Fraunces', Georgia, serif;">Venue & Online Meeting</h1>
        <p style="color: #596a7c; font-size: 14px;">Physical location and video call settings</p>
    </header>

    <form class="form-panel" action="{{ route('meetings.create.hybrid.step2.store') }}" method="post" id="step2Form">
        @csrf

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
                <label class="wide"><span class="required">Meeting Room</span>
                    <select name="meeting_room_id" required>
                        <option value="">Select a room</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('meeting_room_id') == $room->id)>{{ $room->name }} · {{ $room->number }} · Floor {{ $room->floor }}</option>
                        @endforeach
                    </select>
                    @error('meeting_room_id')<small class="field-error">{{ $message }}</small>@enderror
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

            <h3 style="margin: 20px 0 12px; color: var(--navy); font: 600 14px 'Space Grotesk', sans-serif;">Co-Host</h3>
            <p style="color: #596a7c; font-size: 13px; margin-bottom: 16px;">Designate a co-host who can manage participants, mute/unmute, admit from waiting room, and more.</p>            <div class="settings-grid">
                <label class="wide">
                    <span>Co-Host Name or Email</span>
                    <input type="text" name="co_host" value="{{ old('co_host') }}" maxlength="160" placeholder="Enter the name or email of the person to make co-host">
                </label>
                <div class="wide">
                    <span style="font-weight: 600; color: var(--navy); display: block; margin-bottom: 8px;">Co-Host Permissions</span>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        @foreach(array_keys(\App\Models\OnlineSessionParticipant::CO_HOST_PERMISSIONS) as $perm)
                            <label class="checkbox-label">
                                <input type="checkbox" name="co_host_permissions[]" value="{{ $perm }}" @checked(in_array($perm, old('co_host_permissions', [])))>
                                <span>{{ ucfirst(str_replace('_', ' ', $perm)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
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

        {{-- SUBMIT ACTIONS --}}
        <div class="wizard-actions">
            <button type="button" class="button secondary" formaction="{{ route('meetings.create.hybrid.back') }}" formmethod="post">
                <input type="hidden" name="step" value="2">
                ← Back: Meeting Details
            </button>
            <button type="submit" class="button primary">
                <span>Next: Participants & Settings →</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection