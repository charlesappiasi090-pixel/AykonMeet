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
    .wizard-page .form-grid .radio-group { display: flex; gap: 16px; flex-wrap: wrap; }
    .wizard-page .form-grid .radio-label { display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 8px 12px; border-radius: 6px; border: 1px solid transparent; transition: all .15s ease; }
    .wizard-page .form-grid .radio-label:hover { background: #f5f9ff; border-color: #d0e4f5; }
    .wizard-page .form-grid .radio-label input { width: 18px; height: 18px; accent-color: var(--yellow); }
    .wizard-page .form-grid .radio-label input:focus-visible { outline: 3px solid rgba(247,201,72,.5); outline-offset: 2px; }
    .wizard-page .form-grid .radio-label span { font: 13px "DM Sans", sans-serif; color: var(--navy); }
    .wizard-page .form-grid .checkbox-label { flex-direction: row; align-items: center; gap: 8px; cursor: pointer; padding: 8px 12px; border-radius: 6px; border: 1px solid transparent; transition: all .15s ease; }
    .wizard-page .form-grid .checkbox-label:hover { background: #f5f9ff; border-color: #d0e4f5; }
    .wizard-page .form-grid .checkbox-label input { width: auto; min-width: 18px; height: 18px; accent-color: var(--yellow); }
    .wizard-page .form-grid .checkbox-label input:focus-visible { outline: 3px solid rgba(247,201,72,.5); outline-offset: 2px; }
    .wizard-page .form-grid .checkbox-label span { flex: 1; }
    .wizard-page .field-error { color: #c03b2d; font-size: 12px; margin-top: 4px; }
    .wizard-page .required::after { content: " *"; color: #f04452; }
    .wizard-page .recurrence-preview { padding: 12px; background: #f5f9ff; border: 1px solid #d0e4f5; border-radius: 6px; font-size: 13px; color: var(--navy); margin-top: 8px; }
    .wizard-page .help-text { color: #596a7c; font-size: 12px; margin-top: 4px; }
    .wizard-actions { display: flex; gap: 12px; justify-content: flex-end; padding-top: 20px; border-top: 1px solid var(--line); margin-top: 24px; }
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
    @media (max-width: 900px) { .wizard-page .form-grid { grid-template-columns: 1fr; } }
    @media (max-width: 600px) { .wizard-actions { flex-direction: column-reverse; } .wizard-page .button { width: 100%; justify-content: center; } }
</style>

@section('content')
<div class="wizard-page">
    <div class="wizard-progress">
        <div class="wizard-steps">
            <div class="wizard-step active">
                <div class="wizard-step-number">1</div>
                <span class="wizard-step-label">Meeting Details</span>
            </div>
            <div class="wizard-step">
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
        <span class="eyebrow" style="color: var(--yellow); font: 800 11px 'Space Grotesk', sans-serif; letter-spacing: 2px; text-transform: uppercase;">CREATE HYBRID MEETING — Step 1 of 3</span>
        <h1 style="margin: 8px 0 4px; color: var(--navy); font: 700 32px 'Fraunces', Georgia, serif;">Meeting Details</h1>
        <p style="color: #596a7c; font-size: 14px;">Basic information and scheduling</p>
    </header>

    <form class="form-panel" action="{{ route('meetings.create.hybrid.step1') }}" method="post" id="step1Form">
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
                        @php
                            $zones = [];
                            foreach (timezone_identifiers_list() as $zone) {
                                $parts = explode('/', $zone);
                                $region = $parts[0] ?? 'Other';
                                $zones[$region][] = $zone;
                            }
                            ksort($zones);
                        @endphp
                        <optgroup label="UTC">
                            <option value="UTC" @selected(old('timezone', 'UTC') === 'UTC')>UTC</option>
                        </optgroup>
                        @foreach($zones as $region => $list)
                            <optgroup label="{{ $region }}">
                                @foreach($list as $zone)
                                    <option value="{{ $zone }}" @selected(old('timezone') === $zone)>{{ $zone }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
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

        {{-- SUBMIT ACTIONS --}}
        <div class="wizard-actions">
            <button type="button" class="button secondary" onclick="window.history.back()">Cancel</button>
            <button type="submit" class="button primary">
                <span>Next: Venue & Online →</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
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

    // Combine date and time for start_date/end_date before submit
    const form = document.getElementById('step1Form');
    form.addEventListener('submit', function(e) {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const startTime = document.querySelector('input[name="start_time"]').value;
        const endTime = document.querySelector('input[name="end_time"]').value;

        const startDateTime = document.createElement('input');
        startDateTime.type = 'hidden';
        startDateTime.name = 'start_date';
        startDateTime.value = startDate + 'T' + startTime;
        form.appendChild(startDateTime);

        const endDateTime = document.createElement('input');
        endDateTime.type = 'hidden';
        endDateTime.name = 'end_date';
        endDateTime.value = startDate + 'T' + endTime;
        form.appendChild(endDateTime);
    });
});
</script>
@endpush