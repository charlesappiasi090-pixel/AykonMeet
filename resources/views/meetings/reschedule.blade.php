@extends('layouts.app')

@section('content')
<style>
    .reschedule-page { max-width: 860px; margin: 0 auto; }
    .reschedule-page .panel-title { margin-bottom: 30px; }
    .reschedule-page .form-grid { margin-bottom: 24px; }
    .reschedule-page .button { min-width: 190px; }
</style>
<section class="reschedule-page">
    <a class="back-link" href="{{ route('admin.dashboard') }}">← Back to upcoming meetings</a>
    <div class="panel" style="margin-top: 22px;">
        <div class="panel-title">
            <span class="icon-box yellow">↻</span>
            <div>
                <p class="eyebrow">MEETING / {{ $meeting->meeting_code }}</p>
                <h2>Reschedule meeting</h2>
                <p>Choose a free date and time for <strong>{{ $meeting->meeting_topic }}</strong>.</p>
            </div>
        </div>
        <form class="form-grid" method="post" action="{{ route('meetings.reschedule', $meeting) }}">
            @csrf
            @method('PATCH')
            <label>New start date & time
                <input type="datetime-local" name="start_date" value="{{ old('start_date', $meeting->start_date->format('Y-m-d\TH:i')) }}" required>
            </label>
            <label>New end date & time
                <input type="datetime-local" name="end_date" value="{{ old('end_date', $meeting->end_date->format('Y-m-d\TH:i')) }}" required>
            </label>
            <button class="button secondary wide" type="submit">Save new time <span>↻</span></button>
        </form>
    </div>
</section>
@endsection
