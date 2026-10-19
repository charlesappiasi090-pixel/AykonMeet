<div class="mcw-hint" style="margin-bottom:24px">Please review all meeting details below. Once confirmed, the meeting will be created and invitations will be sent to all companies.</div>

<div class="mcw-grid mcw-grid-2" style="margin-bottom:24px;">
    <div class="mcw-permission-group" style="margin:0">
        <h4 class="mcw-permission-title" style="margin-bottom:12px">Meeting Details</h4>
        <dl style="display:grid;gap:8px">
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Title</dt>
            <dd style="margin:0;color:#112b49;font:600 14px DM Sans,sans-serif">{{ old('meeting_title') }}</dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Type</dt>
            <dd style="margin:0;color:#112b49">
                @switch(old('meeting_type'))
                    @case('online') 🌐 Online Only @break
                    @case('hybrid') 🏢 Hybrid @break
                    @case('in_person') 🏢 In-Person @break
                @endswitch
            </dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Category</dt>
            <dd style="margin:0;color:#112b49">{{ ucwords(str_replace('_', ' ', old('category'))) }}</dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Date & Time</dt>
            <dd style="margin:0;color:#112b49">{{ old('meeting_date') }} · {{ old('start_time') }} – {{ old('end_time') }} ({{ old('timezone') }})</dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Duration</dt>
            <dd style="margin:0;color:#112b49">
                @php
                    $start = \Carbon\Carbon::parse(old('start_time'));
                    $end = \Carbon\Carbon::parse(old('end_time'));
                    $diff = $start->diffInMinutes($end);
                    $h = floor($diff / 60);
                    $m = $diff % 60;
                    echo $h > 0 ? $h.'h '.($m > 0 ? $m.'m' : '') : $m.' min';
                @endphp
            </dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Timezone</dt>
            <dd style="margin:0;color:#112b49">{{ old('timezone') }}</dd>
        </dl>
    </div>

    <div class="mcw-permission-group" style="margin:0">
        <h4 class="mcw-permission-title" style="margin-bottom:12px">Host Information</h4>
        <dl style="display:grid;gap:8px">
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Host Name</dt>
            <dd style="margin:0;color:#112b49">{{ old('host_name') }}</dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Host Company</dt>
            <dd style="margin:0;color:#112b49">{{ old('host_company') ?: '—' }}</dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Host Email</dt>
            <dd style="margin:0;color:#112b49">{{ old('host_email') }}</dd>
            
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Host Phone</dt>
            <dd style="margin:0;color:#112b49">{{ old('host_phone') }}</dd>
        </dl>
    </div>
</div>

@if(old('location'))
<div class="mcw-permission-group" style="margin-bottom:24px;">
    <h4 class="mcw-permission-title" style="margin-bottom:12px">Location</h4>
    <p style="margin:0;color:#112b49">{{ old('location') }}</p>
@endif
@if(old('meeting_room_id'))
    <p style="margin:4px 0 0;color:#6b7d91;font-size:12px">
        Room: {{ \App\Models\MeetingRoom::find(old('meeting_room_id'))?->name ?? 'Unknown' }}
    </p>
@endif
</div>

@if(old('agenda_file'))
<div class="mcw-permission-group" style="margin-bottom:24px;">
    <h4 class="mcw-permission-title" style="margin-bottom:12px">Agenda Document</h4>
    <p style="margin:0;color:#112b49">{{ old('agenda_file') }}</p>
</div>
@endif

<div class="mcw-permission-group" style="margin-bottom:24px;">
    <h4 class="mcw-permission-title" style="margin-bottom:16px">Invited Companies ({{ count(old('companies', [])) }})</h4>
    <div style="display:grid;gap:12px">
        @foreach(old('companies', []) as $index => $company)
            <div class="mcw-company" style="padding:16px">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                    <div>
                        <strong style="color:#112b49;font:600 14px DM Sans,sans-serif">{{ $company['name'] }}</strong>
                        <span style="margin-left:8px;padding:2px 8px;border-radius:999px;background:#f1f5f9;color:#6b7d91;font:700 10px Space Grotesk,sans-serif;text-transform:uppercase">
                            {{ $index + 1 }}
                        </span>
                    </div>
                    <span class="mcw-pill pending" style="font-size:10px">Invitation Pending</span>
                </div>
                
                <div style="display:grid;gap:4px;font-size:12px;color:#6b7d91">
                    <div><strong>Contact Email:</strong> {{ $company['email'] }}</div>
                    <div><strong>Rep:</strong> {{ $company['rep_name'] }} ({{ $company['rep_email'] }})</div>
                    @if($company['phone'])
                        <div><strong>Phone:</strong> {{ $company['phone'] }}</div>
                    @endif
                    @if($company['rep_job_title'])
                        <div><strong>Rep Title:</strong> {{ $company['rep_job_title'] }}</div>
                    @endif
                    @if($company['rep_department'])
                        <div><strong>Department:</strong> {{ $company['rep_department'] }}</div>
                    @endif
                    @if($company['expected_participants'])
                        <div><strong>Expected:</strong> {{ $company['expected_participants'] }} participants</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="mcw-permission-group" style="margin-bottom:24px;">
    <h4 class="mcw-permission-title" style="margin-bottom:16px">Permissions & Security</h4>
    <dl style="display:grid;gap:8px">
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Who Can Join</dt>
        <dd style="margin:0;color:#112b49">
            @if(old('who_can_join') === 'invited_only')
                🔒 Invited Companies Only
            @else
                🔗 Anyone with Approved Link
            @endif
        </dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Participant Approval</dt>
        <dd style="margin:0;color:#112b49">
            @if(old('participant_approval') === 'automatic')
                ✅ Automatic Admission
            @else
                👤 Host Approval Required
            @endif
        </dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Waiting Room</dt>
        <dd style="margin:0;color:#112b49">{{ old('waiting_room') ? 'Enabled' : 'Disabled' }}</dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Mute on Entry</dt>
        <dd style="margin:0;color:#112b49">{{ old('mute_on_entry') ? 'Enabled' : 'Disabled' }}</dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Screen Sharing</dt>
        <dd style="margin:0;color:#112b49">
            @switch(old('allow_screen_share'))
                @case('host_only') 👤 Host Only @break
                @case('presenters') 🎙️ Presenters @break
                @case('all_approved') 👥 All Approved @break
            @endswitch
        </dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Document Sharing</dt>
        <dd style="margin:0;color:#112b49">
            @switch(old('document_sharing'))
                @case('host_only') 👤 Host Only @break
                @case('approved_participants') 👥 Approved Participants @break
            @endswitch
        </dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Chat Permissions</dt>
        <dd style="margin:0;color:#112b49">
            @switch(old('chat_permissions'))
                @case('everyone') 👥 Everyone @break
                @case('representatives_only') 🏢 Representatives Only @break
                @case('host_only') 👤 Host Only @break
            @endswitch
        </dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Recording</dt>
        <dd style="margin:0;color:#112b49">
            @switch(old('recording_permission'))
                @case('host') 👤 Host Only @break
                @case('authorized_users') 👥 Authorized Users @break
            @endswitch
        </dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Join Before Host</dt>
        <dd style="margin:0;color:#112b49">{{ old('join_before_host') ? 'Allowed' : 'Not Allowed' }}</dd>
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Allow Rejoin</dt>
        <dd style="margin:0;color:#112b49">{{ old('allow_rejoin') ? 'Allowed' : 'Not Allowed' }}</dd>
        
        @if(old('has_password'))
            <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Password Protected</dt>
            <dd style="margin:0;color:#112b49">Yes</dd>
        @endif
        
        <dt style="color:#6b7d91;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px">Company Group Privacy</dt>
        <dd style="margin:0;color:#112b49">{{ old('company_group_privacy') ? 'Enabled' : 'Disabled' }}</dd>
    </dl>
</div>

<div class="mcw-permission-group" style="margin-bottom:24px;background:#fff9e5;border:1px solid #f3e2ae;border-radius:12px;padding:16px">
    <p style="margin:0;color:#8a6d00;font-size:13px;line-height:1.6">
        <strong>⚠ Confirmation Required:</strong> By clicking "Create Meeting & Send Invitations", you confirm that all information above is correct. 
        Invitations will be sent immediately to all company representatives. You can resend or revoke invitations later from the meeting dashboard.
    </div>
</div>

<input type="hidden" name="confirm" value="1">
<input type="hidden" name="step" value="4">