@extends('layouts.app')

@section('content')
<div class="page-shell">
    <header class="page-header">
        <div class="page-header-inner">
            <div class="page-header-copy">
                <span class="eyebrow">Edit Series Occurrence</span>
                <h1 class="page-title">{{ $meeting->meeting_topic }}</h1>
                <p class="page-subtitle">Occurrence #{{ $meeting->occurrence_number }} of recurring series</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('meetings.details', $meeting->series) }}" class="button secondary">Back to Series</a>
            </div>
        </div>
    </header>

    <main class="page-main">
        <div class="form-panel">
            <form action="{{ route('meetings.series.update', $meeting) }}" method="post">
                @csrf
                @method('PATCH')

                <div class="form-grid">
                    <div class="form-section">
                        <h3>Edit Scope</h3>
                        <p class="form-hint">Choose which occurrences to update:</p>

                        <div class="scope-options">
                            <label class="scope-option">
                                <input type="radio" name="scope" value="this_only" required checked>
                                <span class="scope-label">
                                    <strong>This occurrence only</strong>
                                    <small>Creates an exception for this single meeting</small>
                                </span>
                            </label>

                            <label class="scope-option">
                                <input type="radio" name="scope" value="all_future">
                                <span class="scope-label">
                                    <strong>This and all future occurrences</strong>
                                    <small>Updates this meeting and all upcoming occurrences in the series</small>
                                </span>
                            </label>

                            <label class="scope-option">
                                <input type="radio" name="scope" value="all_occurrences">
                                <span class="scope-label">
                                    <strong>All occurrences in the series</strong>
                                    <small>Updates every occurrence including past ones (use with caution)</small>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Meeting Details</h3>

                        <label>Start Date & Time
                            <input type="datetime-local" name="start_date" value="{{ $meeting->start_date->format('Y-m-d\TH:i') }}" required>
                        </label>

                        <label>End Date & Time
                            <input type="datetime-local" name="end_date" value="{{ $meeting->end_date->format('Y-m-d\TH:i') }}" required>
                        </label>

                        @if($meeting->meeting_room_id || $meeting->isHybrid())
                        <label>Meeting Room
                            <select name="meeting_room_id">
                                <option value="">Select a room</option>
                                @foreach($rooms ?? [] as $room)
                                <option value="{{ $room->id }}" @selected($meeting->meeting_room_id == $room->id)>{{ $room->name }} · {{ $room->number }} · Floor {{ $room->floor }}</option>
                                @endforeach
                            </select>
                        </label>
                        @endif

                        @if($meeting->isHybrid() || !$meeting->is_online)
                        <label>Physical Location
                            <input type="text" name="physical_location" value="{{ $meeting->physical_location }}" maxlength="255" placeholder="Building, floor, room details">
                        </label>
                        @endif

                        <label>Status
                            <select name="status">
                                <option value="scheduled" @selected($meeting->status === 'scheduled')>Scheduled</option>
                                <option value="cancelled" @selected($meeting->status === 'cancelled')>Cancelled</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('meetings.details', $meeting->series) }}" class="button secondary">Cancel</a>
                    <button type="submit" class="button primary">Save Changes</button>
                </div>
            </form>
        </div>

        {{-- Series Info --}}
        <div class="info-panel" style="margin-top:24px">
            <h3>Series Information</h3>
            <div class="series-info-grid">
                <div class="info-item">
                    <span class="info-label">Series</span>
                    <span class="info-value">{{ $meeting->series->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Frequency</span>
                    <span class="info-value">{{ $meeting->series->getRecurrenceDescription() }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">End Condition</span>
                    <span class="info-value">{{ $meeting->series->getEndConditionDescription() }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Occurrences Generated</span>
                    <span class="info-value">{{ $meeting->series->occurrences_count }}</span>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.form-section { margin-bottom: 24px; }
.form-section h3 { margin: 0 0 8px; color: var(--navy); font: 600 14px "Space Grotesk", sans-serif; }
.form-hint { color: #596a7c; font-size: 13px; margin: 0 0 12px; }
.scope-options { display: grid; gap: 8px; }
.scope-option { display: flex; align-items: flex-start; gap: 12px; padding: 16px; border: 1px solid var(--line); border-radius: 8px; background: var(--white); cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; }
.scope-option:hover { border-color: var(--yellow); box-shadow: 0 2px 8px rgba(247,201,72,.15); }
.scope-option input { margin-top: 2px; width: 18px; height: 18px; accent-color: var(--yellow); }
.scope-label strong { display: block; color: var(--navy); font-size: 13px; }
.scope-label small { display: block; margin-top: 4px; color: #596a7c; font-size: 12px; }
.form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid var(--line); margin-top: 24px; }
.series-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.info-item { padding: 12px 16px; background: var(--paper); border: 1px solid var(--line); border-radius: 8px; }
.info-label { display: block; color: #596a7c; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 4px; }
.info-value { color: var(--navy); font: 500 14px "Space Grotesk", sans-serif; }
</style>
@endsection