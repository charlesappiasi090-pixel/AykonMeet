// AykonMeet Global Interactive Feedback System
// Provides toast notifications, loading states, button feedback, and form validation

(function() {
    'use strict';

    // ============ TOAST NOTIFICATION SYSTEM ============
    const ToastManager = {
        container: null,
        toasts: new Map(),
        defaultDuration: 5000,

        init() {
            this.container = document.getElementById('ayk-toast-container');
            if (!this.container) {
                this.container = document.createElement('div');
                this.container.id = 'ayk-toast-container';
                this.container.className = 'ayk-toast-container';
                document.body.appendChild(this.container);
            }
        },

        show(type, message, options = {}) {
            this.init();

            const toast = document.createElement('div');
            const id = 'toast-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);

            const icons = {
                success: '✓',
                error: '✕',
                warning: '⚠',
                info: 'ℹ'
            };

            const iconClasses = {
                success: 'bg-green-100 text-green-700',
                error: 'bg-red-100 text-red-700',
                warning: 'bg-yellow-100 text-yellow-700',
                info: 'bg-blue-100 text-blue-700'
            };

            const titles = {
                success: 'Success',
                error: 'Error',
                warning: 'Warning',
                info: 'Information'
            };

            const icon = icons[type] || icons.info;
            const iconClass = iconClasses[type] || iconClasses.info;
            const title = options.title || titles[type] || 'Notification';

            toast.id = id;
            toast.className = `ayk-toast ayk-toast-${type} flex items-start gap-3 p-4 bg-white border border-line rounded-xl shadow-lg min-w-[320px] max-w-[420px] animate-in`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'polite');
            toast.setAttribute('aria-atomic', 'true');
            toast.dataset.autoClose = options.duration ?? 5000;
            toast.dataset.dismissible = options.dismissible !== false;

            let html = `
                <div class="ayk-toast-icon flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-lg ${iconClasses[type] || iconClasses.info}">${icons[type] || icons.info}</div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-navy text-sm mb-1">${title}</div>
                    <div class="text-muted text-sm leading-relaxed">${message}</div>
                </div>
            `;

            if (options.dismissible !== false) {
                html += `<button type="button" class="ayk-toast-close w-7 h-7 rounded-lg flex items-center justify-center text-muted hover:bg-paper hover:text-navy transition-colors" aria-label="Dismiss" onclick="ToastManager.dismiss('${id}')">✕</button>`;
            }

            if (options.action && options.actionText) {
                html += `<button type="button" class="mt-2 text-sm font-semibold text-navy hover:underline" onclick="${options.action}">${options.actionText}</button>`;
            }

            toast.innerHTML = html;
            this.container.appendChild(toast);
            this.toasts.set(id, toast);

            // Auto-close
            const duration = options.duration ?? this.defaultDuration;
            if (duration > 0) {
                setTimeout(() => this.dismiss(id), duration);
            }

            return id;
        },

        dismiss(id) {
            const toast = this.toasts.get(id);
            if (!toast) return;

            toast.classList.add('removing');
            setTimeout(() => {
                toast.remove();
                this.toasts.delete(id);
            }, 250);
        },

        success(message, options) { return this.show('success', message, options); },
        error(message, options) { return this.show('error', message, options); },
        warning(message, options) { return this.show('warning', message, options); },
        info(message, options) { return this.show('info', message, options); },

        clearAll() {
            this.toasts.forEach((toast, id) => this.dismiss(id));
        }
    };

    // ============ BUTTON LOADING STATE MANAGEMENT ============
    const ButtonLoader = {
        setLoading(button, loading, loadingText) {
            if (!button) return;

            if (loading) {
                button.dataset.originalText = button.innerHTML;
                button.dataset.originalDisabled = button.disabled;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.classList.add('loading');

                const text = loadingText || 'Loading...';
                button.innerHTML = `
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>${text}</span>
                `;
            } else {
                button.disabled = button.dataset.originalDisabled === 'true';
                button.removeAttribute('aria-busy');
                button.classList.remove('loading');
                button.innerHTML = button.dataset.originalText || button.innerHTML;
                delete button.dataset.originalText;
                delete button.dataset.originalDisabled;
            }
        },

        setSuccess(button, successText, duration = 2000) {
            if (!button) return;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg><span>${successText || 'Done'}</span>`;
            button.classList.add('success');

            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
                button.classList.remove('success');
            }, duration);
        },

        setError(button, errorText, duration = 3000) {
            if (!button) return;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg><span>${errorText || 'Failed'}</span>`;
            button.classList.add('error');

            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalText;
                button.classList.remove('error');
            }, duration);
        }
    };

    // ============ FORM VALIDATION FEEDBACK ============
    const FormValidator = {
        validators: {
            required: (value) => value !== '' && value !== null && value !== undefined,
            email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            minLength: (value, min) => value && value.length >= min,
            maxLength: (value, max) => value && value.length <= max,
            pattern: (value, regex) => value && regex.test(value),
        },

        validateField(input, rules) {
            const field = input.closest('.form-field') || input.parentElement;
            if (!field) return true;

            const value = input.value;
            let isValid = true;
            let errorMessage = '';

            for (const [rule, params] of Object.entries(rules)) {
                const validator = this.validators[rule];
                if (validator && !validator(input.value, ...params)) {
                    isValid = false;
                    errorMessage = this.getErrorMessage(rule, params, input);
                    break;
                }
            }

            this.setFieldState(input, isValid, errorMessage);
            return isValid;
        },

        getErrorMessage(rule, params, input) {
            const messages = {
                required: 'This field is required',
                email: 'Please enter a valid email address',
                minLength: `Must be at least ${params[0]} characters`,
                maxLength: `Must be no more than ${params[0]} characters`,
                pattern: 'Invalid format',
            };
            return messages[rule] || 'Invalid value';
        },

        setFieldState(input, isValid, errorMessage = '') {
            const field = input.closest('.form-field') || input.parentElement;
            if (!field) return;

            const errorEl = field.querySelector('.field-error');
            const iconEl = field.querySelector('.validation-icon');

            input.classList.remove('error', 'success');
            input.classList.add(isValid ? 'success' : 'error');
            input.setAttribute('aria-invalid', !isValid);

            if (iconEl) {
                iconEl.classList.remove('error', 'success');
                iconEl.classList.add(isValid ? 'success' : 'error');
                iconEl.innerHTML = isValid
                    ? '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                    : '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            }

            if (errorEl) {
                if (!isValid && errorMessage) {
                    errorEl.textContent = errorMessage;
                    errorEl.classList.add('visible');
                } else {
                    errorEl.classList.remove('visible');
                }
            }
        },

        validateForm(form, rules) {
            let isValid = true;
            for (const [name, fieldRules] of Object.entries(rules)) {
                const input = form.querySelector(`[name="${name}"]`);
                if (input && !this.validateField(input, fieldRules)) {
                    isValid = false;
                }
            }
            return isValid;
        }
    };

    // ============ PAGE LOADING INDICATOR ============
    const PageLoader = {
        bar: null,

        init() {
            this.bar = document.getElementById('ayk-page-loader');
            if (!this.bar) {
                this.bar = document.createElement('div');
                this.bar.id = 'ayk-page-loader';
                this.bar.className = 'ayk-page-loader';
                document.body.insertBefore(this.bar, document.body.firstChild);
            }
        },

        start() {
            this.init();
            this.bar.classList.add('active');
        },

        finish() {
            this.init();
            this.bar.classList.add('finishing');
            this.bar.classList.remove('active');
            setTimeout(() => {
                this.bar.classList.remove('finishing');
            }, 400);
        }
    };

    // ============ CONNECTION STATUS ============
    const ConnectionStatus = {
        element: null,
        state: 'disconnected',

        init(selector) {
            this.element = document.querySelector(selector);
        },

        setState(state, label) {
            if (!this.element) return;
            this.state = state;
            this.element.className = 'connection-status ' + state;
            this.element.textContent = label || this.formatLabel(state);
        },

        formatLabel(state) {
            const labels = {
                connecting: 'Connecting...',
                connected: 'Connected',
                failed: 'Connection Failed',
                disconnected: 'Disconnected'
            };
            return labels[state] || state;
        }
    };

    // ============ FORM SUBMISSION HANDLER ============
    const FormHandler = {
        async submit(form, options = {}) {
            const submitBtn = form.querySelector('[type="submit"]');
            const formData = new FormData(form);

            // Show loading
            if (submitBtn) ButtonLoader.setLoading(submitBtn, true, options.loadingText);

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    if (submitBtn) ButtonLoader.setSuccess(submitBtn, options.successText || 'Success!');
                    ToastManager.success(data.message || 'Operation completed successfully');

                    if (options.onSuccess) options.onSuccess(data);
                    if (options.redirect) window.location.href = options.redirect;
                    if (options.reload) window.location.reload();
                } else {
                    throw new Error(data.message || 'Operation failed');
                }
            } catch (error) {
                if (submitBtn) ButtonLoader.setError(submitBtn, error.message || 'Operation failed');
                ToastManager.error(error.message || 'An error occurred');
                if (options.onError) options.onError(error);
            }
        },

        attachToForm(form, options = {}) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submit(form, options);
            });
        }
    };

    // ============ INITIALIZATION ============
    function init() {
        // Initialize toast container
        ToastManager.init();

        // Initialize page loader
        PageLoader.init();

        // Auto-handle forms with data-ajax attribute
        document.querySelectorAll('form[data-ajax]').forEach(form => {
            FormHandler.attachToForm(form, {
                loadingText: form.dataset.loadingText,
                successText: form.dataset.successText,
                redirect: form.dataset.redirect,
                reload: form.dataset.reload === 'true'
            });
        });

        // Handle buttons with data-loading attribute
        document.querySelectorAll('button[data-loading], a[data-loading]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.dataset.loading) {
                    ButtonLoader.setLoading(this, true, this.dataset.loadingText);
                }
            });
        });

        // Handle form field validation on blur
        document.querySelectorAll('.form-field input, .form-field select, .form-field textarea').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.dataset.validate) {
                    const rules = JSON.parse(this.dataset.validate);
                    FormValidator.validateField(this, rules);
                }
            });
        });

        // Initialize connection status if element exists
        if (document.querySelector('.connection-status')) {
            ConnectionStatus.init('.connection-status');
        }

        // Handle HTMX-like page transitions
        document.addEventListener('DOMContentLoaded', () => {
            PageLoader.finish();
        });

        // Listen for HTMX-like events
        document.addEventListener('htmx:beforeRequest', () => PageLoader.start());
        document.addEventListener('htmx:afterRequest', () => PageLoader.finish());

        // Export to window
        window.AykFeedback = {
            ToastManager,
            ButtonLoader,
            FormValidator,
            PageLoader,
            ConnectionStatus,
            FormHandler
        };
    }

    // Auto-initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for manual use
    window.ToastManager = ToastManager;
    window.ButtonLoader = ButtonLoader;
    window.FormValidator = FormValidator;
    window.PageLoader = PageLoader;
    window.ConnectionStatus = ConnectionStatus;
    window.FormHandler = FormHandler;

})();