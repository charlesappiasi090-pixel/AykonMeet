@extends('layouts.app')

@section('title', 'Your dashboard · AykonMeet')

@section('content')
<style>
.ud-wrap{max-width:980px;margin:0 auto;padding:8px 0 48px}
.ud-hero{background:#112b49;color:#fff;border-radius:8px;padding:34px 36px;margin-bottom:26px;border-top:4px solid #f7c948}
.ud-kicker{margin:0 0 10px;font:700 11px "DM Sans",sans-serif;letter-spacing:2px;text-transform:uppercase;color:#f7c948}
.ud-hero h1{margin:0 0 8px;font-family:Fraunces,Georgia,serif;font-size:30px;font-weight:600}
.ud-hero p{margin:0;color:#c8d6e6;font:400 14px/1.6 "DM Sans",sans-serif;max-width:560px}
.ud-section{margin-bottom:30px}
.ud-section-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin:0 0 14px}
.ud-section-head h2{margin:0;font-family:"Space Grotesk",sans-serif;font-size:17px;color:#112b49;letter-spacing:-.2px}
.ud-count{font:600 11px "DM Sans",sans-serif;color:#8a6d00;background:#fdf3d0;border:1px solid #f2e0a0;border-radius:999px;padding:4px 10px}
.ud-list{list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:10px}
.ud-item{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;background:#fff;border:1px solid #dbe3ea;border-left:3px solid #112b49;border-radius:6px;padding:15px 18px}
.ud-item.unread{border-left-color:#f7c948;background:#fffdf5}
.ud-item strong{display:block;font:600 14px "DM Sans",sans-serif;color:#112b49}
.ud-item p{margin:4px 0 0;font:400 13px/1.55 "DM Sans",sans-serif;color:#4a5568}
.ud-item small{display:block;margin-top:6px;font:500 11px "DM Sans",sans-serif;color:#9aa7b2}
.ud-item a{flex:0 0 auto;font:600 12px "DM Sans",sans-serif;color:#112b49;text-decoration:none;border-bottom:2px solid #f7c948;padding-bottom:1px;white-space:nowrap}
.ud-item a:hover{color:#b18800}
.ud-empty{background:#f7f9fb;border:1px dashed #cfdce8;border-radius:6px;padding:22px;text-align:center;color:#718096;font:400 13px "DM Sans",sans-serif}
.ud-table{width:100%;border-collapse:collapse;background:#fff;border:1px solid #dbe3ea;border-radius:6px;overflow:hidden}
.ud-table th{text-align:left;font:700 10px "DM Sans",sans-serif;letter-spacing:1.2px;text-transform:uppercase;color:#8a99a8;padding:12px 16px;border-bottom:1px solid #dbe3ea;background:#f7f9fb}
.ud-table td{padding:14px 16px;border-bottom:1px solid #eef2f6;font:400 13px "DM Sans",sans-serif;color:#172437;vertical-align:top}
.ud-table tr:last-child td{border-bottom:0}
.ud-table td strong{color:#112b49;font-weight:600}
.ud-pill{display:inline-block;font:700 10px "DM Sans",sans-serif;letter-spacing:.6px;text-transform:uppercase;border-radius:999px;padding:4px 10px}
.ud-pill.accepted{background:#e3f6ea;color:#1c7a43}
.ud-pill.declined{background:#fde8e8;color:#b3261e}
.ud-pill.sent{background:#e8f0fb;color:#1b4f8a}
.ud-pill.pending{background:#f1f4f8;color:#64748b}
.ud-open{font:600 12px "DM Sans",sans-serif;color:#112b49;text-decoration:none;border-bottom:2px solid #f7c948;padding-bottom:1px}
.ud-open:hover{color:#b18800}
@media(max-width:640px){.ud-table th:nth-child(3),.ud-table td:nth-child(3){display:none}.ud-hero{padding:26px 22px}.ud-item{flex-direction:column}}
</style>

<div class="ud-wrap">
    <header class="ud-hero">
        <p class="ud-kicker">Your workspace</p>
        <h1>Welcome back, {{ $user->name }}.</h1>
        <p>Review your meeting invitations and the latest updates from organizers. Responding here keeps your attendance and meeting access up to date.</p>
    </header>

    <section class="ud-section" id="notifications">
        <div class="ud-section-head">
            <h2>Notifications</h2>
            @if($unreadCount > 0)<span class="ud-count">{{ $unreadCount }} new</span>@endif
        </div>

        @if($inbox->isEmpty())
            <p class="ud-empty">You have no notifications yet. Invitation updates and meeting changes will appear here.</p>
        @else
            <ul class="ud-list">
                @foreach($inbox as $item)
                    <li class="ud-item {{ $item['read'] ? '' : 'unread' }}">
                        <div>
                            <strong>{{ $item['title'] }}</strong>
                            @if($item['message'])<p>{{ $item['message'] }}</p>@endif
                            <small>{{ $item['date']->format('D, M j, Y · g:i A') }}</small>
                        </div>
                        @if($item['url'])<a href="{{ $item['url'] }}">Open &rarr;</a>@endif
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="ud-section" id="invitations">
        <div class="ud-section-head">
            <h2>Your invitations</h2>
            <span class="ud-count">{{ $invitations->count() }} total</span>
        </div>

        @if($invitations->isEmpty())
            <p class="ud-empty">You have no meeting invitations yet.</p>
        @else
            <table class="ud-table">
                <thead>
                    <tr><th>Meeting</th><th>Organizer</th><th>When</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($invitations as $row)
                        <tr>
                            <td>
                                <strong>{{ $row['meeting']?->meeting_topic ?? 'Meeting' }}</strong>
                                @if($row['meeting']?->meeting_code)
                                    <div><small style="color:#9aa7b2">{{ $row['meeting']->meeting_code }}</small></div>
                                @endif
                            </td>
                            <td>{{ $row['meeting']?->name ?? '—' }}</td>
                            <td>
                                @if($row['meeting']?->start_date)
                                    {{ $row['meeting']->start_date->format('D, M j · g:i A') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td><span class="ud-pill {{ $row['tone'] }}">{{ $row['label'] }}</span></td>
                            <td><a class="ud-open" href="{{ $row['url'] }}">View &rarr;</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</div>
@endsection
