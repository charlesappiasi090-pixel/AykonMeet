@extends('layouts.app')

@section('content')
<style>
    .edit-page { max-width: 860px; margin: 0 auto; }
    .edit-page [hidden] { display: none !important; }
    .edit-page .panel-title { margin-bottom: 30px; }
    .edit-page .form-grid { margin-bottom: 24px; }
    .edit-page .form-grid .wide { grid-column: 1 / -1; }
    .edit-page textarea { min-height: 140px; resize: vertical; }
    .edit-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
</style>
<section class="edit-page">
    <a class="back-link" href="{{ route('admin.dashboard') }}">← Back to upcoming meetings</a>
    <div class="panel" style="margin-top: 22px;">
        <div class="panel-title">
            <span class="icon-box yellow">✎</span>
            <div>
                <p class="eyebrow">MEETING / {{ $meeting->meeting_code }}</p>
                <h2>Edit meeting details</h2>
                <p>Update the details for <strong>{{ $meeting->meeting_topic }}</strong>. Attendees keep their codes.</p>
            </div>
        </div>
        <form class="form-grid" method="post" action="{{ route('meetings.update', $meeting) }}">
            @csrf
            @method('PATCH')
            <label>Host name<input name="name" value="{{ old('name', $meeting->name) }}" required @error('name') class="invalid" @enderror></label>
            @error('name')<small class="field-error">{{ $message }}</small>@enderror
            <label>Company name<input name="company_name" value="{{ old('company_name', $meeting->company_name) }}"></label>
            <label>Email<input type="email" name="email" value="{{ old('email', $meeting->email) }}" required @error('email') class="invalid" @enderror></label>
            @error('email')<small class="field-error">{{ $message }}</small>@enderror
            <label>Phone number<input name="phone" value="{{ old('phone', $meeting->phone) }}" required @error('phone') class="invalid" @enderror></label>
            @error('phone')<small class="field-error">{{ $message }}</small>@enderror
            <label class="wide">Meeting topic<input name="meeting_topic" value="{{ old('meeting_topic', $meeting->meeting_topic) }}" required @error('meeting_topic') class="invalid" @enderror></label>
            @error('meeting_topic')<small class="field-error">{{ $message }}</small>@enderror
            @if($meeting->isHybrid())
            @php
                $topicOptions = ['Planning', 'Team meeting', 'Training', 'Workshop', 'Interview', 'Other'];
                $topicValue = old('topic_category', $meeting->topic_category);
                $customTopic = old('custom_topic', $topicValue && ! in_array($topicValue, $topicOptions, true) ? $topicValue : '');
                $usesCustomTopic = $topicValue === 'Custom' || ($topicValue && ! in_array($topicValue, $topicOptions, true));
            @endphp
            <label class="wide">Description<textarea name="description" maxlength="2000">{{ old('description', $meeting->description) }}</textarea></label>
            <label class="wide">Meeting category<select name="topic_category" id="edit-topic-category"><option value="">Select a meeting topic</option>@foreach($topicOptions as $topic)<option value="{{ $topic }}" @selected($topicValue === $topic)>{{ $topic }}</option>@endforeach<option value="Custom" @selected($usesCustomTopic)>Type your own topic</option></select></label>
            <label class="wide" id="edit-custom-topic-field" @if(! $usesCustomTopic) hidden @endif>Custom meeting topic <span class="hybrid-create-required">*</span><input name="custom_topic" id="edit-custom-topic" value="{{ $customTopic }}" maxlength="80" placeholder="Enter your meeting topic" @required($usesCustomTopic)></label>
            @endif
            <label>Start date & time<input type="datetime-local" name="start_date" value="{{ old('start_date', $meeting->start_date->format('Y-m-d\TH:i')) }}" required @error('start_date') class="invalid" @enderror></label>
            @error('start_date')<small class="field-error">{{ $message }}</small>@enderror
            <label>End date & time<input type="datetime-local" name="end_date" value="{{ old('end_date', $meeting->end_date->format('Y-m-d\TH:i')) }}" required @error('end_date') class="invalid" @enderror></label>
            @error('end_date')<small class="field-error">{{ $message }}</small>@enderror
            <label>From location<input name="from_location" value="{{ old('from_location', $meeting->from_location) }}" placeholder="Optional"></label>
            <label>To location<input name="to_location" value="{{ old('to_location', $meeting->to_location) }}" placeholder="Optional"></label>
            <label class="wide">{{ $meeting->isHybrid() ? 'Physical Location / Meeting Room' : 'Meeting room' }}@if($meeting->isHybrid()) <span class="hybrid-create-required">*</span>@endif<select name="meeting_room_id" @required($meeting->isHybrid())><option value="">Select a room</option>@foreach($rooms as $room)<option value="{{ $room->id }}" @selected(old('meeting_room_id', $meeting->meeting_room_id) == $room->id)>{{ $room->name }} · {{ $room->number }} · Floor {{ $room->floor }}</option>@endforeach</select></label>
            <label class="wide">Minutes & notes<textarea name="notes" placeholder="Key points, decisions, action items...">{{ old('notes', $meeting->notes) }}</textarea></label>
            <div class="edit-actions">
                <button class="button secondary" type="submit">Save changes <span>→</span></button>
                <a class="text-button" href="{{ route('meetings.attendance', $meeting) }}">Cancel</a>
            </div>
        </form>
    </div>
</section>
@if($meeting->isHybrid())
<script>
    (function () {
        const category = document.getElementById('edit-topic-category');
        const field = document.getElementById('edit-custom-topic-field');
        const input = document.getElementById('edit-custom-topic');
        category.addEventListener('change', function () {
            const custom = category.value === 'Custom';
            field.hidden = !custom;
            input.required = custom;
            if (!custom) input.value = '';
        });
    })();
</script>
@endif
@endsection