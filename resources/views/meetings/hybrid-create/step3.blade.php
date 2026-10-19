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
    .wizard-page .form-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; }
    .wizard-page .form-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; accent-color: var(--yellow); }
    .wizard-page .form-grid .checkbox-label span { flex: 1; }
    .wizard-page .attendee-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 8px; align-items: center; margin-bottom: 8px; }
    .wizard-page .btn-add-row { padding: 8px 12px; border: 1px dashed #b18800; border-radius: 6px; background: #fffaf0; color: #8a6d00; font: 600 12px "DM Sans", sans-serif; cursor: pointer; }
    .wizard-page .btn-add-row:hover { background: #fff3d6; }
    .wizard-page .btn-remove { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid #e3b7b7; border-radius: 6px; background: #fff3f3; color: #c03b2d; font-size: 18px; font-weight: 700; cursor: pointer; }
    .wizard-page .btn-remove:hover { background: #fbe0e0; }
    .wizard-page .field-error { color: #c03b2d; font-size: 12px; margin-top: 4px; }
    .wizard-page .required::after { content: " *"; color: #f04452; }
    .wizard-page .help-text { color: #596a7c; font-size: 12px; margin-top: 4px; }
    .wizard-page .settings-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .wizard-page .settings-grid label { display: flex; flex-direction: column; gap: 6px; }
    .wizard-page .settings-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; }
    .wizard-page .settings-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; }
    .wizard-page .settings-grid .checkbox-label span { flex: 1; }
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
    @media (max-width: 600px) { .wizard-actions { flex-direction: column-reverse; } .wizard-page .button { width: 100%; justify-content: center; } }
</style>

@section('content')
<div class="wizard-page">
    <div class="wizard-progress">
        <div class="wizard-steps">
            <div class="wizard-step completed">
                <div class="wizard-step-number">✓</div>
                <span class="wizard-step-label">Meeting Details</span>
            </div>
            <div class="wizard-step completed">
                <div class="wizard-step-number">✓</div>
                <span class="wizard-step-label">Venue & Online</span>
            </div>
            <div class="wizard-step active">
                <div class="wizard-step-number">3</div>
                <span class="wizard-step-label">Participants</span>
            </div>
        </div>
    </div>

    <header style="margin-bottom: 24px;">
        <span class="eyebrow" style="color: var(--yellow); font: 800 11px 'Space Grotesk', sans-serif; letter-spacing: 2px; text-transform: uppercase;">CREATE HYBRID MEETING — Step 3 of 3</span>
        <h1 style="margin: 8px 0 4px; color: var(--navy); font: 700 32px 'Fraunces', Georgia, serif;">Participants & Settings</h1>
        <p style="color: #596a7c; font-size: 14px;">Invite attendees and configure final settings</p>
    </header>

    <form class="form-panel" action="{{ route('meetings.create.hybrid.step3.store') }}" method="post" id="step3Form">
        @csrf

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
            <p class="help-text">Add participants who will receive individual invitation links with RSVP options.</            <div id="attendeeRows">
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

        {{-- REVIEW SUMMARY --}}
        <section class="section-card panel" style="padding: 24px; background: #f5f9ff; border: 1px solid #d0e4f5;">
            <h2 class="section-title"><span class="section-icon">📋</span> Review & Confirm</h2>
            <p style="color: #596a7c; font-size: 13px; margin-bottom: 16px;">Please review the meeting details before creating:</p>
            <div class="form-grid" style="font-size: 13px;">
                <div><strong>Meeting:</strong> <span id="review-topic">{{ old('meeting_topic') }}</span></div>
                <div><strong>Date:</strong> <span id="review-date">{{ old('start_date') }} at {{ old('start_time') }} – {{ old('end_time') }} {{ old('timezone') }}</span></div>
                <div><strong>Venue:</strong> <span id="review-venue">{{ old('venue_name') }}, {{ old('venue_address') }}</span></div>
                <div><strong>Room:</strong> <span id="review-room">{{ old('meeting_room_id') ? 'Selected' : 'Not selected' }}</span></div>
                <div><strong>Attendees:</strong> <span id="review-attendees">{{ count(old('attendees', [])) }} participant(s)</span></div>
                <div><strong>Recurrence:</strong> <span id="review-recurrence">{{ old('recurrence_type') === 'none' ? 'One-time meeting' : ucfirst(old('recurrence_type')) . (old('recurrence_interval') > 1 ? ' every ' . old('recurrence_interval') . ' ' . old('recurrence_type') . 's' : '') }}</span></div>
            </div>
        </section>

        {{-- SUBMIT ACTIONS --}}
        <div class="wizard-actions">
            <button type="button" class="button secondary" formaction="{{ route('meetings.create.hybrid.back') }}" formmethod="post">
                <input type="hidden" name="step" value="3">
                ← Back: Venue & Online
            </button>
            <button type="submit" class="button primary" id="submitBtn">
                <span>Create Hybrid Meeting</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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

    // Update review summary from session data
    function updateReview() {
        // This will be populated from session data on page load
    }

    // Disable submit button to prevent double submission
    const form = document.getElementById('step3Form');
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Creating...</span><svg class="spinner" width="16" height="16" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 100" style="animation: spin 1s linear infinite;"></svg>';
        const style = document.createElement('style');
        style.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(style);
    });

    // Populate review from session
    document.addEventListener('DOMContentLoaded', function() {
        // The review will be populated from old() values which come from session
    });
});
</script>
@endpush