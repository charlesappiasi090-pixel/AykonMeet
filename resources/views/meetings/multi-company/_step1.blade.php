<div class="mcf-card">
    <div class="mcf-card-header">
        <div class="mcf-badge">1</div>
        <div>
            <h3 class="mcf-card-title">Meeting Details</h3>
            <p class="mcf-card-sub">Basic meeting information, schedule, and host details</p>
        </div>
    </div>

    <div class="mcf-grid mcf-grid-2">
        <div class="mcf-field">
            <label class="mcf-label">Meeting Title <span class="mcf-req">*</span></label>
            <input type="text" name="meeting_title" value="{{ old('meeting_title', $wizardData['meeting_title'] ?? '') }}" required maxlength="180" placeholder="e.g. Q4 Partner Alignment">
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Host Company <span class="mcf-req">*</span></label>
            <input type="text" name="host_company" value="{{ old('host_company', $wizardData['host_company'] ?? '') }}" required maxlength="160" placeholder="e.g. Aykon Technologies">
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Host Name <span class="mcf-req">*</span></label>
            <input type="text" name="host_name" value="{{ old('host_name', $wizardData['host_name'] ?? '') }}" required maxlength="120" placeholder="e.g. Sarah Mitchell">
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Host Email <span class="mcf-req">*</span></label>
            <input type="email" name="host_email" value="{{ old('host_email', $wizardData['host_email'] ?? '') }}" required maxlength="160" placeholder="e.g. sarah@aykon.com">
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Host Phone <span class="mcf-req">*</span></label>
            <input type="tel" name="host_phone" value="{{ old('host_phone', $wizardData['host_phone'] ?? '') }}" required maxlength="40" placeholder="e.g. +1 555 123 4567">
        </div>

        <div class="mcf-field" style="grid-column: 1 / -1;">
            <label class="mcf-label">Description</label>
            <textarea name="description" maxlength="3000" rows="4" placeholder="Meeting description, objectives, and context...">{{ old('description', $wizardData['description'] ?? '') }}</textarea>
        </div>
    </div>

    <div class="mcf-sub-divider"><span>Schedule & Access</span></div>

    <div class="mcf-grid mcf-grid-2">
        <div class="mcf-field">
            <label class="mcf-label">Meeting Date <span class="mcf-req">*</span></label>
            <input type="date" name="meeting_date" value="{{ old('meeting_date', $wizardData['meeting_date'] ?? now()->addDay()->format('Y-m-d')) }}" required>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Start Time <span class="mcf-req">*</span></label>
            <input type="time" name="start_time" value="{{ old('start_time', $wizardData['start_time'] ?? '09:00') }}" required>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Duration (minutes) <span class="mcf-req">*</span></label>
            <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $wizardData['duration_minutes'] ?? 60) }}" required min="5" max="1440" placeholder="60">
            <span class="mcf-hint">5 to 1440 minutes (24 hours max)</span>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Timezone <span class="mcf-req">*</span></label>
            <select name="timezone" required>
                @foreach($timezones as $value => $label)
                    <option value="{{ $value }}" {{ (old('timezone', $wizardData['timezone'] ?? 'UTC') === $value) ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Meeting Type</label>
            <input type="hidden" name="meeting_type" value="online">
            <div style="height:46px;padding:0 14px;border:1.5px solid #d5e0ea;border-radius:11px;background:#f8fafc;color:#112b49;font:500 14px 'DM Sans',sans-serif;display:flex;align-items:center;gap:10px">
                🌐 <span>Online (Multi-Company Session)</span>
            </div>
            <span class="mcf-hint">Multi-company meetings are always held online — all companies share one session.</span>
        </div>
    </div>

    <div class="mcf-sub-divider"><span>Permissions & Security</span></div>

    <div class="mcf-grid mcf-grid-2">
        <div class="mcf-field">
            <label class="mcf-label">Who Can Join <span class="mcf-req">*</span></label>
            <select name="who_can_join" required>
                <option value="invited_only" {{ (old('who_can_join', $wizardData['who_can_join'] ?? 'invited_only') === 'invited_only') ? 'selected' : '' }}>Invited Companies Only</option>
                <option value="approved_link" {{ (old('who_can_join', $wizardData['who_can_join'] ?? '') === 'approved_link') ? 'selected' : '' }}>Anyone with Approved Link</option>
            </select>
            <span class="mcf-hint"><strong>Invited Companies Only:</strong> Only invited representatives can join.<br><strong>Approved Link:</strong> Anyone with the link can request to join (host approval required).</span>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Max Companies <span class="mcf-req">*</span></label>
            <input type="number" name="max_companies" value="{{ old('max_companies', $wizardData['max_companies'] ?? 50) }}" required min="1" max="100" placeholder="50">
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Participant Approval <span class="mcf-req">*</span></label>
            <select name="participant_approval" required>
                <option value="automatic" {{ (old('participant_approval', $wizardData['participant_approval'] ?? 'automatic') === 'automatic') ? 'selected' : '' }}>Automatic Admission</option>
                <option value="host_approval" {{ (old('participant_approval', $wizardData['participant_approval'] ?? '') === 'host_approval') ? 'selected' : '' }}>Host Approval Required</option>
            </select>
            <span class="mcf-hint"><strong>Automatic:</strong> Invited participants join immediately.<br><strong>Host Approval:</strong> Host must admit each participant from waiting room.</span>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Waiting Room</label>
            <div class="mcf-toggle-item">
                <input type="checkbox" name="waiting_room" value="1" {{ old('waiting_room', $wizardData['waiting_room'] ?? true) ? 'checked' : '' }}>
                <div class="mcf-toggle-text">
                    <strong>Enable Waiting Room</strong>
                    <span>Participants wait for host admission</span>
                </div>
            </div>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Mute on Entry</label>
            <div class="mcf-toggle-item">
                <input type="checkbox" name="mute_on_entry" value="1" {{ old('mute_on_entry', $wizardData['mute_on_entry'] ?? true) ? 'checked' : '' }}>
                <div class="mcf-toggle-text">
                    <strong>Mute Microphone on Entry</strong>
                    <span>Participants join muted</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mcf-sub-divider"><span>Media & Collaboration</span></div>

    <div class="mcf-grid mcf-grid-2">
        <div class="mcf-field">
            <label class="mcf-label">Screen Sharing <span class="mcf-req">*</span></label>
            <select name="allow_screen_share" required>
                <option value="host_only" {{ (old('allow_screen_share', $wizardData['allow_screen_share'] ?? 'host_only') === 'host_only') ? 'selected' : '' }}>Host Only</option>
                <option value="presenters" {{ (old('allow_screen_share', $wizardData['allow_screen_share'] ?? '') === 'presenters') ? 'selected' : '' }}>Presenters</option>
                <option value="all_approved" {{ (old('allow_screen_share', $wizardData['allow_screen_share'] ?? '') === 'all_approved') ? 'selected' : '' }}>All Approved Participants</option>
            </select>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Document Sharing <span class="mcf-req">*</span></label>
            <select name="document_sharing" required>
                <option value="host_only" {{ (old('document_sharing', $wizardData['document_sharing'] ?? 'host_only') === 'host_only') ? 'selected' : '' }}>Host Only</option>
                <option value="approved_participants" {{ (old('document_sharing', $wizardData['document_sharing'] ?? '') === 'approved_participants') ? 'selected' : '' }}>Approved Participants</option>
            </select>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Recording <span class="mcf-req">*</span></label>
            <select name="allow_recording" required>
                <option value="host" {{ (old('allow_recording', $wizardData['allow_recording'] ?? 'host') === 'host') ? 'selected' : '' }}>Host Only</option>
                <option value="authorized_users" {{ (old('allow_recording', $wizardData['allow_recording'] ?? '') === 'authorized_users') ? 'selected' : '' }}>Authorized Users</option>
                <option value="none" {{ (old('allow_recording', $wizardData['allow_recording'] ?? '') === 'none') ? 'selected' : '' }}>Disabled</option>
            </select>
        </div>

        <div class="mcf-field">
            <label class="mcf-label">Chat Permissions <span class="mcf-req">*</span></label>
            <select name="chat_permissions" required>
                <option value="everyone" {{ (old('chat_permissions', $wizardData['chat_permissions'] ?? 'everyone') === 'everyone') ? 'selected' : '' }}>Everyone</option>
                <option value="representatives_only" {{ (old('chat_permissions', $wizardData['chat_permissions'] ?? '') === 'representatives_only') ? 'selected' : '' }}>Company Representatives Only</option>
                <option value="host_only" {{ (old('chat_permissions', $wizardData['chat_permissions'] ?? '') === 'host_only') ? 'selected' : '' }}>Host Only</option>
            </select>
        </div>
    </div>

    <div class="mcf-sub-divider"><span>Agenda</span></div>

    <div class="mcf-field" style="grid-column: 1 / -1;">
        <label class="mcf-label">Agenda Document (Optional)</label>
        <div class="mcf-upload-box" onclick="this.querySelector('input').click()">
            <label class="mcf-upload-label">
                <span class="mcf-upload-icon">📄</span>
                <div>
                    <strong>Upload Agenda Document</strong>
                    <p>PDF, DOC, DOCX, or TXT — max 10MB</p>
                </div>
            </label>
            <input type="file" name="agenda_file" accept=".pdf,.doc,.docx,.txt" style="display:none" onchange="this.form.dispatchEvent(new Event('change'))">
        </div>
        @if($wizardData['agenda_file_name'] ?? false)
            <div style="margin-top:10px;padding:10px;background:#f0f7ff;border:1px solid #cce3fe;border-radius:8px;display:flex;align-items:center;gap:10px">
                <span style="font-size:20px">📄</span>
                <strong style="color:#112b49">{{ $wizardData['agenda_file_name'] }}</strong>
            </div>
        @endif
    </div>
</div>

<div class="mcf-footer-actions">
    <div>
        <button type="submit" name="action" value="cancel"
                class="mcf-btn mcf-btn-outline-danger"
                onclick="return confirm('Cancel meeting creation? Any unsaved data will be lost.')"
                style="height:44px;padding:0 20px;font-size:13px">
            ✕ Cancel
        </button>
    </div>
    <div class="mcf-footer-right">
        <button type="submit" name="action" value="save_draft"
                class="mcf-btn mcf-btn-outline"
                style="height:44px;padding:0 20px;font-size:13px">
            💾 Save as Draft
        </button>
        <button type="submit" name="action" value="next"
                class="mcf-btn mcf-btn-gold"
                style="height:48px;padding:0 28px">
            Continue to Add Companies &rarr;
        </button>
    </div>
</div>