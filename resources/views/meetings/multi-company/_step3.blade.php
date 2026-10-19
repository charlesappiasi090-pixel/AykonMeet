<div class="mcf-card">
    <div class="mcf-card-header">
        <div class="mcf-badge">3</div>
        <div>
            <h3 class="mcf-card-title">Review & Confirm</h3>
            <p class="mcf-card-sub">Review all details before creating the meeting and sending invitations</p>
        </div>
    </div>

    <div class="mcf-alert" style="background:#fffdf5;border-color:#ecc94b;color:#975a16">
        <strong>⚠ Confirmation Required:</strong> By submitting this form, the meeting will be created and invitations will be sent immediately to all company representatives. You can resend or revoke invitations later from the meeting dashboard.
    </div>

    <div class="mcf-grid mcf-grid-2" style="margin-bottom:24px;">
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Meeting Title</span>
            <span class="mcf-review-val">{{ $wizardData['meeting_title'] ?? '—' }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Host Company</span>
            <span class="mcf-review-val">{{ $wizardData['host_company'] ?? '—' }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Host Name</span>
            <span class="mcf-review-val">{{ $wizardData['host_name'] ?? '—' }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Host Email</span>
            <span class="mcf-review-val">{{ $wizardData['host_email'] ?? '—' }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Meeting Type</span>
            <span class="mcf-review-val">🌐 Online &mdash; Multi-Company Session</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Date &amp; Time</span>
            <span class="mcf-review-val">
                {{ $wizardData['meeting_date'] ?? '—' }} · {{ $wizardData['start_time'] ?? '—' }}
                ({{ $wizardData['duration_minutes'] ?? 60 }} min)
            </span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Timezone</span>
            <span class="mcf-review-val">{{ $wizardData['timezone'] ?? 'UTC' }}</span>
        </div>
    </div>

    <div class="mcf-sub-divider"><span>Permissions & Security</span></div>

    <div class="mcf-grid mcf-grid-2" style="margin-bottom:24px;">
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Who Can Join</span>
            <span class="mcf-review-val">
                @if(($wizardData['who_can_join'] ?? 'invited_only') === 'invited_only')
                    🔒 Invited Companies Only
                @else
                    🔗 Anyone with Approved Link
                @endif
            </span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Max Companies</span>
            <span class="mcf-review-val">{{ $wizardData['max_companies'] ?? 50 }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Participant Approval</span>
            <span class="mcf-review-val">
                @if(($wizardData['participant_approval'] ?? 'automatic') === 'automatic')
                    ✅ Automatic Admission
                @else
                    👤 Host Approval Required
                @endif
            </span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Waiting Room</span>
            <span class="mcf-review-val">{{ ($wizardData['waiting_room'] ?? true) ? 'Enabled' : 'Disabled' }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Mute on Entry</span>
            <span class="mcf-review-val">{{ ($wizardData['mute_on_entry'] ?? false) ? 'Enabled' : 'Disabled' }}</span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Screen Sharing</span>
            <span class="mcf-review-val">
                @switch($wizardData['allow_screen_share'] ?? 'host_only')
                    @case('host_only') 👤 Host Only @break
                    @case('presenters') 🎙️ Presenters @break
                    @case('all_approved') 👥 All Approved @break
                @endswitch
            </span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Document Sharing</span>
            <span class="mcf-review-val">
                @switch($wizardData['document_sharing'] ?? 'host_only')
                    @case('host_only') 👤 Host Only @break
                    @case('approved_participants') 👥 Approved Participants @break
                @endswitch
            </span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Recording</span>
            <span class="mcf-review-val">
                @switch($wizardData['allow_recording'] ?? 'host')
                    @case('host') 👤 Host Only @break
                    @case('authorized_users') 👥 Authorized Users @break
                    @case('none') 🚫 Disabled @break
                @endswitch
            </span>
        </div>
        <div class="mcf-review-cell">
            <span class="mcf-review-label">Chat Permissions</span>
            <span class="mcf-review-val">
                @switch($wizardData['chat_permissions'] ?? 'everyone')
                    @case('everyone') 👥 Everyone @break
                    @case('representatives_only') 🏢 Representatives Only @break
                    @case('host_only') 👤 Host Only @break
                @endswitch
            </span>
        </div>
    </div>

    <div class="mcf-sub-divider"><span>Invited Companies ({{ count($wizardData['companies'] ?? []) }})</span></div>

    <div class="mcf-companies-table-wrapper">
        <table class="mcf-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Company</th>
                    <th>Contact Email</th>
                    <th>Representative</th>
                    <th>Rep Email</th>
                    <th>Department</th>
                    <th>Custom Message</th>
                    <th>Logo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wizardData['companies'] ?? [] as $index => $company)
                    <tr>
                        <td><span class="mcf-table-idx">{{ $index + 1 }}</span></td>
                        <td><strong>{{ $company['name'] }}</strong></td>
                        <td><span class="mcf-email-badge">{{ $company['email'] }}</span></td>
                        <td>{{ $company['rep_name'] }}</td>
                        <td>{{ $company['rep_email'] }}</td>
                        <td>{{ $company['rep_department'] ?? '—' }}</td>
                        <td>{{ Str::limit($company['invitation_message'] ?? '', 60) }}</td>
                        <td>{{ ($company['logo_path'] ?? false) ? '✓ Uploaded' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mcf-delivery-list" style="margin-top:24px">
        <li><span class="mcf-delivery-check">1</span> Meeting created with all details and permissions</li>
        <li><span class="mcf-delivery-check">2</span> Host company registered and marked as accepted</li>
        <li><span class="mcf-delivery-check">3</span> {{ count($wizardData['companies'] ?? []) }} invited companies registered with secure tokens</li>
        <li><span class="mcf-delivery-check">4</span> Primary representatives pre-registered as attendees</li>
        <li><span class="mcf-delivery-check">5</span> Invitation emails sent to all company contacts</li>
        <li><span class="mcf-delivery-check">6</span> Signed meeting room URL generated</li>
    </div>
</div>

<div class="mcf-footer-actions">
    <div>
        <a href="{{ route('multi-company-meetings.create', ['step' => 2]) }}"
           class="mcf-btn mcf-btn-secondary"
           style="height:44px;padding:0 20px;font-size:13px">
            &larr; Back to Companies
        </a>
    </div>
    <div class="mcf-footer-right">
        <button type="submit" name="action" value="next"
                class="mcf-btn mcf-btn-primary"
                style="height:52px;padding:0 36px;font-size:15px;letter-spacing:0.3px">
            🚀 Create Meeting &amp; Send Invitations
        </button>
    </div>
</div>