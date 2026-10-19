<div class="mcf-card">
    <div class="mcf-card-header">
        <div class="mcf-badge">2</div>
        <div>
            <h3 class="mcf-card-title">Invited Companies</h3>
            <p class="mcf-card-sub">Add each company that will participate. Each receives a secure invitation link with a custom message.</p>
        </div>
    </div>

    {{-- CSV Upload Section --}}
    <div class="mcf-upload-box" style="margin-bottom: 24px; position: relative; cursor: pointer;">
        <form method="POST" action="{{ route('multi-company-meetings.import-companies') }}" enctype="multipart/form-data" style="width: 100%;" id="importCompaniesForm">
            @csrf
            <label class="mcf-upload-label" for="companiesFileInput">
                <span class="mcf-upload-icon">📁</span>
                <div>
                    <strong>Upload Companies from CSV / Excel</strong>
                    <p>Upload a CSV or XLSX file with columns: Company Name, Company Email, Company Phone, Representative Name, Representative Email, Representative Department (optional), Invitation Message (optional)</p>
                </div>
            </label>
            <input type="file" id="companiesFileInput" name="companies_file" accept=".csv,.xlsx,.xls" style="display:none" onchange="document.getElementById('importCompaniesForm').submit()">
            <div style="margin-top: 12px; font-size: 12px; color: #718096;">
                Or add companies manually below
            </div>
        </form>
    </div>

    <div id="multiCompanyRows">
        @forelse($wizardData['companies'] ?? [['name' => '', 'email' => '', 'phone' => '', 'rep_name' => '', 'rep_email' => '', 'rep_department' => '', 'invitation_message' => '', 'logo_path' => null]] as $index => $company)
            <div class="mcf-company" style="border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;background:#fafbfc">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
                    <h4 style="font:700 15px Space Grotesk,sans-serif;color:#112b49;margin:0">
                        Company {{ $index + 1 }}
                        @if($company['name'])
                            <span style="font-weight:500;color:#64748b;font-size:13px;margin-left:8px">{{ $company['name'] }}</span>
                        @endif
                    </h4>
                    @if(count($wizardData['companies'] ?? []) > 1)
                        <button type="button" class="mcf-btn-remove" onclick="this.closest('.mcf-company').remove()">Remove</button>
                    @endif
                </div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field">
                        <label class="mcf-label">Company Name <span class="mcf-req">*</span></label>
                        <input type="text" name="companies[{{ $index }}][name]" value="{{ $company['name'] ?? '' }}" required maxlength="160" placeholder="e.g. Acme Corporation">
                    </div>

                    <div class="mcf-field">
                        <label class="mcf-label">Company Email <span class="mcf-req">*</span></label>
                        <input type="email" name="companies[{{ $index }}][email]" value="{{ $company['email'] ?? '' }}" required maxlength="160" placeholder="e.g. contact@acme.com">
                    </div>
                </div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field">
                        <label class="mcf-label">Company Phone</label>
                        <input type="tel" name="companies[{{ $index }}][phone]" value="{{ $company['phone'] ?? '' }}" maxlength="40" placeholder="e.g. +1 555 123 4567">
                    </div>

                    <div class="mcf-field">
                        <label class="mcf-label">Rep Department</label>
                        <input type="text" name="companies[{{ $index }}][rep_department]" value="{{ $company['rep_department'] ?? '' }}" maxlength="160" placeholder="e.g. Engineering">
                    </div>
                </div>

                <div class="mcf-sub-divider"><span>Primary Representative (Required)</span></div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field">
                        <label class="mcf-label">Representative Name <span class="mcf-req">*</span></label>
                        <input type="text" name="companies[{{ $index }}][rep_name]" value="{{ $company['rep_name'] ?? '' }}" required maxlength="160" placeholder="e.g. Jane Smith">
                    </div>

                    <div class="mcf-field">
                        <label class="mcf-label">Representative Email <span class="mcf-req">*</span></label>
                        <input type="email" name="companies[{{ $index }}][rep_email]" value="{{ $company['rep_email'] ?? '' }}" required maxlength="160" placeholder="e.g. jane@acme.com">
                    </div>
                </div>

                <div class="mcf-sub-divider"><span>Custom Invitation Message (Optional)</span></div>

                <div class="mcf-field" style="grid-column: 1 / -1;">
                    <label class="mcf-label">Personalized message for this company's invitation</label>
                    <textarea name="companies[{{ $index }}][invitation_message]" maxlength="1000" rows="3" placeholder="Custom message to include in the invitation email...">{{ $company['invitation_message'] ?? '' }}</textarea>
                    <span class="mcf-hint">This message will be included in the company's invitation email</span>
                </div>

                <div class="mcf-sub-divider"><span>Optional: Company Logo</span></div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field" style="grid-column: 1 / -1;">
                        <label class="mcf-label">Company Logo</label>
                        <div class="mcf-upload-box" onclick="this.querySelector('input').click()">
                            <label class="mcf-upload-label">
                                <span class="mcf-upload-icon">🖼️</span>
                                <div>
                                    <strong>Upload Company Logo</strong>
                                    <p>PNG, JPG, or SVG — max 2MB</p>
                                </div>
                            </label>
                            <input type="file" name="companies[{{ $index }}][logo]" accept="image/png,image/jpeg,image/svg+xml" style="display:none">
                        </div>
                        @if($company['logo_path'] ?? false)
                            <div style="margin-top:10px;padding:10px;background:#f0f7ff;border:1px solid #cce3fe;border-radius:8px;display:flex;align-items:center;gap:10px">
                                <span style="font-size:20px">🖼️</span>
                                <strong style="color:#112b49">Logo uploaded</strong>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="mcf-company" style="border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin-bottom:16px;background:#fafbfc">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px">
                    <h4 style="font:700 15px Space Grotesk,sans-serif;color:#112b49;margin:0">Company 1</h4>
                </div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field">
                        <label class="mcf-label">Company Name <span class="mcf-req">*</span></label>
                        <input type="text" name="companies[0][name]" required maxlength="160" placeholder="e.g. Acme Corporation">
                    </div>

                    <div class="mcf-field">
                        <label class="mcf-label">Company Email <span class="mcf-req">*</span></label>
                        <input type="email" name="companies[0][email]" required maxlength="160" placeholder="e.g. contact@acme.com">
                    </div>
                </div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field">
                        <label class="mcf-label">Company Phone</label>
                        <input type="tel" name="companies[0][phone]" maxlength="40" placeholder="e.g. +1 555 123 4567">
                    </div>

                    <div class="mcf-field">
                        <label class="mcf-label">Rep Department</label>
                        <input type="text" name="companies[0][rep_department]" maxlength="160" placeholder="e.g. Engineering">
                    </div>
                </div>

                <div class="mcf-sub-divider"><span>Primary Representative (Required)</span></div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field">
                        <label class="mcf-label">Representative Name <span class="mcf-req">*</span></label>
                        <input type="text" name="companies[0][rep_name]" required maxlength="160" placeholder="e.g. Jane Smith">
                    </div>

                    <div class="mcf-field">
                        <label class="mcf-label">Representative Email <span class="mcf-req">*</span></label>
                        <input type="email" name="companies[0][rep_email]" required maxlength="160" placeholder="e.g. jane@acme.com">
                    </div>
                </div>

                <div class="mcf-sub-divider"><span>Custom Invitation Message (Optional)</span></div>

                <div class="mcf-field" style="grid-column: 1 / -1;">
                    <label class="mcf-label">Personalized message for this company's invitation</label>
                    <textarea name="companies[0][invitation_message]" maxlength="1000" rows="3" placeholder="Custom message to include in the invitation email..."></textarea>
                    <span class="mcf-hint">This message will be included in the company's invitation email</span>
                </div>

                <div class="mcf-sub-divider"><span>Optional: Company Logo</span></div>

                <div class="mcf-grid mcf-grid-2">
                    <div class="mcf-field" style="grid-column: 1 / -1;">
                        <label class="mcf-label">Company Logo</label>
                        <div class="mcf-upload-box" onclick="this.querySelector('input').click()">
                            <label class="mcf-upload-label">
                                <span class="mcf-upload-icon">🖼️</span>
                                <div>
                                    <strong>Upload Company Logo</strong>
                                    <p>PNG, JPG, or SVG — max 2MB</p>
                                </div>
                            </label>
                            <input type="file" name="companies[0][logo]" accept="image/png,image/jpeg,image/svg+xml" style="display:none">
                        </div>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <button type="button" class="mcf-btn-add-company" id="multiCompanyAdd" style="margin-top:16px">
        <span>+</span> Add Another Company
    </button>

    <script>
    let companyIndex = {{ count($wizardData['companies'] ?? []) }};

    document.getElementById('multiCompanyAdd').addEventListener('click', function() {
        const rows = document.getElementById('multiCompanyRows');
        const template = rows.querySelector('.mcf-company');
        if (!template) return;
        
        const clone = template.cloneNode(true);
        
        // Update all name attributes to use new index
        clone.querySelectorAll('input, select, textarea').forEach(function(input) {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace(/companies\[\d+\]/, 'companies[' + companyIndex + ']'));
            }
            input.value = '';
            if (input.type === 'file') {
                input.value = '';
            }
        });
        
        rows.appendChild(clone);
        companyIndex++;
    });

    // Handle remove buttons for companies
    document.getElementById('multiCompanyRows').addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.mcf-btn-remove');
        if (!removeBtn) return;
        
        const companyDiv = removeBtn.closest('.mcf-company');
        if (!companyDiv) return;
        
        const rows = document.getElementById('multiCompanyRows');
        const companies = rows.querySelectorAll('.mcf-company');
        
        if (companies.length > 1) {
            companyDiv.remove();
        } else {
            // Clear the single remaining company form
            companyDiv.querySelectorAll('input, textarea, select').forEach(function(input) {
                if (input.type !== 'file') input.value = '';
            });
        }
    });
    </script>
</div>

<div class="mcf-footer-actions">
    <div>
        <a href="{{ route('multi-company-meetings.create', ['step' => 1]) }}"
           class="mcf-btn mcf-btn-secondary"
           style="height:44px;padding:0 20px;font-size:13px">
            &larr; Back to Meeting Details
        </a>
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
            Review &amp; Confirm &rarr;
        </button>
    </div>
</div>