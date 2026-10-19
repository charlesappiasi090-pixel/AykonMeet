@extends('layouts.app')

@section('content')
@include('admin._styles')
<header class="admin-hero">
    <div>
        <p class="admin-kicker"><span class="dot"></span>Admin console / Settings</p>
        <h1>Recording &amp; Storage</h1>
        <p class="admin-sub">Manage recording access, private storage, and downloads for every meeting in the workspace.</p>
        <div class="admin-actions">
            <a class="admin-btn admin-btn-gold" href="{{ route('admin.dashboard') }}">Back to workspace <i>&rarr;</i></a>
            <a class="admin-btn admin-btn-ghost" href="{{ route('meetings.index') }}">Meetings console</a>
        </div>
    </div>
    <div class="admin-tiles" aria-label="Account overview">
        <div class="admin-tile gold"><b>{{ number_format($recordingCount) }}</b><span>Saved recordings</span><small>Completed recordings</small></div>
        <div class="admin-tile"><b class="text">{{ $storageBytes >= 1073741824 ? number_format($storageBytes / 1073741824, 2).' GB' : number_format($storageBytes / 1048576, 1).' MB' }}</b><span>Storage used</span><small>Private application storage</small></div>
        <div class="admin-tile"><b class="text">{{ $recordingSettings->recording_enabled ? 'Enabled' : 'Disabled' }}</b><span>Recording status</span><small>Workspace policy</small></div>
        <div class="admin-tile"><b class="text">Private</b><span>Storage location</span><small>Not web-accessible</small></div>
    </div>
</header>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Recording policy</h2><p>Choose whether recordings are allowed, whether invited attendees can record, and who can download recordings. Admins can always download recordings.</p></div>
    </header>
    <form class="admin-form" method="post" action="{{ route('settings.recordings.update') }}">
        @csrf
        <label>Workspace recording
            <select name="recording_enabled" required>
                <option value="1" @selected($recordingSettings->recording_enabled)>Enabled</option>
                <option value="0" @selected(!$recordingSettings->recording_enabled)>Disabled for everyone</option>
            </select>
        </label>
        <label>Invited attendee recording
            <select name="allow_attendee_recording" required>
                <option value="1" @selected($recordingSettings->allow_attendee_recording)>Attendees may record</option>
                <option value="0" @selected(!$recordingSettings->allow_attendee_recording)>Only permitted meeting hosts may record</option>
            </select>
        </label>
        <label>Recording downloads
            <select name="downloads_for_hosts" required>
                <option value="0" @selected(!$recordingSettings->downloads_for_hosts)>Admins only</option>
                <option value="1" @selected($recordingSettings->downloads_for_hosts)>Admins and meeting hosts</option>
            </select>
        </label>
        <div class="admin-page-actions"><button class="button primary" type="submit">Save recording settings</button></div>
    </form>
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>Who can record</h2><p>Control recording access for each workspace account. Invited attendees are controlled by the recording policy above.</p></div>
    </header>
    <div style="overflow-x:auto">
        <table class="admin-page-table">
            <thead><tr><th>Account</th><th>Role</th><th>Recording</th><th></th></tr></thead>
            <tbody>
            @foreach($recordingUsers as $recordingUser)
                <tr>
                    <td><strong>{{ $recordingUser->name }}</strong><small>{{ $recordingUser->email }}</small></td>
                    <td>{{ $recordingUser->isAdmin() ? 'Admin / host' : 'Workspace user' }}</td>
                    <td colspan="2">
                        <form method="post" action="{{ route('settings.recording-users.update', $recordingUser) }}" style="display:flex;gap:10px;align-items:center">
                            @csrf
                            @method('patch')
                            <select name="can_record_sessions" aria-label="Recording permission for {{ $recordingUser->name }}">
                                <option value="1" @selected($recordingUser->can_record_sessions)>Can record</option>
                                <option value="0" @selected(!$recordingUser->can_record_sessions)>Cannot record</option>
                            </select>
                            <button class="button secondary" type="submit">Save</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="admin-page-panel">
    <header class="admin-panel-head">
        <div><h2>All meeting recordings</h2><p>Files are kept in private application storage and organized under their meeting. Delete recordings here to permanently free storage.</p></div>
        <span class="admin-chip">{{ $recordings->count() }} {{ \Illuminate\Support\Str::plural('file', $recordings->count()) }}</span>
    </header>
    <div style="overflow-x:auto">
        <table class="admin-page-table">
            <thead><tr><th>Meeting</th><th>Recorded by</th><th>Date / size</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($recordings as $recording)
                <tr>
                    <td><strong>{{ $recording->meeting?->meeting_topic ?? 'Meeting removed' }}</strong><small>{{ $recording->meeting?->meeting_code ?? '—' }}</small></td>
                    <td>{{ $recording->participant?->display_name ?? 'Unknown participant' }}</td>
                    <td>{{ $recording->started_at?->format('M j, Y H:i') ?? '—' }}<small>{{ number_format(($recording->file_size ?? 0) / 1048576, 1) }} MB</small></td>
                    <td><span class="admin-pill {{ $recording->status === 'ready' ? 'ok' : 'off' }}">{{ ucfirst($recording->status) }}</span></td>
                    <td style="white-space:nowrap">
                        <div class="admin-page-actions">
                            @if($recording->status === 'ready' && $recording->meeting)
                                <a class="admin-act gold" href="{{ route('sessions.recordings.download', [$recording->meeting, $recording]) }}">Download</a>
                            @endif
                            @if($recording->meeting)
                                <form method="post" action="{{ route('sessions.recordings.delete', [$recording->meeting, $recording]) }}" style="display:inline" onsubmit="return confirm('Permanently delete this recording?')">
                                    @csrf
                                    @method('delete')
                                    <button class="admin-act" type="submit" style="color:#a5382c">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><span style="color:var(--muted);font-size:13px">No meeting recordings have been saved.</span></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection