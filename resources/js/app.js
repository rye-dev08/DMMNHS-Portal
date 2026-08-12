// ============================================================
// Global notification + validation system
// ============================================================

// ---- Toast host (fixed, top-right) --------------------------
function getToastHost() {
    let host = document.getElementById('toast-host');
    if (host) {
        return host;
    }
    host = document.createElement('div');
    host.id = 'toast-host';
    host.className = 'toast-host';
    document.body.appendChild(host);
    return host;
}

const TOAST_ICONS = {
    success: `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
        </svg>`,
    error: `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>`,
    info: `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>`,
};

const TOAST_TITLES = {
    success: 'Success',
    error: 'Something went wrong',
    info: 'Notice',
};

function showToast(msg, type) {
    let kind = type || 'info';
    if (!TOAST_ICONS[kind]) {
        kind = 'info';
    }
    const host = getToastHost();
    const ttl = kind === 'error' ? 6500 : kind === 'success' ? 3500 : 4500;

    const box = document.createElement('div');
    box.className = `toast toast-${kind}`;
    box.setAttribute('role', kind === 'error' ? 'alert' : 'status');

    const icon = document.createElement('span');
    icon.className = 'toast-icon';
    icon.innerHTML = TOAST_ICONS[kind];

    const content = document.createElement('div');
    content.className = 'toast-content';

    const title = document.createElement('p');
    title.className = 'toast-title';
    title.textContent = TOAST_TITLES[kind];

    const text = document.createElement('p');
    text.className = 'toast-message';
    text.textContent = String(msg);

    content.appendChild(title);
    content.appendChild(text);

    const closeBtn = document.createElement('button');
    closeBtn.className = 'toast-close';
    closeBtn.type = 'button';
    closeBtn.setAttribute('aria-label', 'Dismiss');
    closeBtn.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>`;
    closeBtn.addEventListener('click', () => dismissToast(box));

    const bar = document.createElement('span');
    bar.className = 'toast-bar';
    bar.style.animationDuration = `${ttl}ms`;

    box.appendChild(icon);
    box.appendChild(content);
    box.appendChild(closeBtn);
    box.appendChild(bar);

    host.appendChild(box);

    // Enter animation
    requestAnimationFrame(() => requestAnimationFrame(() => box.classList.add('toast-enter')));

    const timer = setTimeout(() => dismissToast(box), ttl);
    box.addEventListener('mouseenter', () => clearTimeout(timer));
    box.addEventListener('mouseleave', () => {
        setTimeout(() => dismissToast(box), ttl);
    });
}

function dismissToast(box) {
    if (!box || box.dataset.leaving) {
        return;
    }
    box.dataset.leaving = '1';
    box.classList.remove('toast-enter');
    box.classList.add('toast-leave');
    setTimeout(() => box.remove(), 320);
}

// Backward-compatible helpers (existing pages still work).
function showNotice(msg, type) {
    showToast(msg, type || 'info');
}

function floatingAlert(msg, type) {
    showToast(msg, type || 'info');
}

// ---- Success / confirmation modal ---------------------------
function showModal(msg, type, title) {
    const kind = type || 'success';
    const iconKey = TOAST_ICONS[kind] || TOAST_ICONS.success;

    let overlay = document.getElementById('modal-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'modal-overlay';
        overlay.className = 'modal-overlay';
        document.body.appendChild(overlay);
    }
    overlay.innerHTML = `
        <div class="modal-card" role="dialog" aria-modal="true">
            <div class="modal-icon modal-icon-${kind}">${iconKey}</div>
            <h2 class="modal-title">${title || TOAST_TITLES[kind] || 'Notice'}</h2>
            <p class="modal-message">${String(msg)}</p>
            <button type="button" class="modal-close-btn">OK</button>
        </div>`;

    overlay.classList.remove('modal-hidden');
    requestAnimationFrame(() => overlay.classList.add('modal-visible'));

    const close = () => {
        overlay.classList.remove('modal-visible');
        setTimeout(() => overlay.classList.add('modal-hidden'), 220);
    };
    overlay.querySelector('.modal-close-btn').addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            close();
        }
    });
    overlay.querySelector('.modal-close-btn').focus();
}

// ---- Confirmation modal (replaces native confirm()) ----------
function showConfirm(message, opts) {
    const options = opts || {};
    const title = options.title || 'Are you sure?';
    const confirmText = options.confirmText || 'Continue';
    const cancelText = options.cancelText || 'Cancel';
    const danger = options.danger !== false;
    const onConfirm = options.onConfirm || (() => {});

    let overlay = document.getElementById('modal-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'modal-overlay';
        overlay.className = 'modal-overlay';
        document.body.appendChild(overlay);
    }
    overlay.innerHTML = `
        <div class="modal-card modal-card-sm" role="dialog" aria-modal="true">
            <div class="modal-icon ${danger ? 'modal-icon-danger' : 'modal-icon-info'}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <h2 class="modal-title">${title}</h2>
            <p class="modal-message">${String(message)}</p>
            <div class="modal-actions">
                <button type="button" class="modal-btn-ghost" data-confirm-cancel>${cancelText}</button>
                <button type="button" class="modal-close-btn modal-btn-confirm ${danger ? 'modal-btn-danger' : ''}">${confirmText}</button>
            </div>
        </div>`;

    overlay.classList.remove('modal-hidden');
    requestAnimationFrame(() => overlay.classList.add('modal-visible'));

    const close = () => {
        overlay.classList.remove('modal-visible');
        setTimeout(() => overlay.classList.add('modal-hidden'), 220);
    };
    overlay.querySelector('[data-confirm-cancel]').addEventListener('click', close);
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            close();
        }
    });
    overlay.querySelector('.modal-btn-confirm').addEventListener('click', () => {
        close();
        onConfirm();
    });
    overlay.querySelector('[data-confirm-cancel]').focus();
}

// Wire any [data-confirm] submit buttons (inside a form) to the styled modal.
function initConfirmDialogs() {
    document.querySelectorAll('button[type="submit"][data-confirm]').forEach((btn) => {
        if (btn.dataset.confirmBound) {
            return;
        }
        btn.dataset.confirmBound = '1';
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const form = btn.closest('form');
            const danger = btn.dataset.danger !== undefined ? btn.dataset.danger !== 'false' : true;
            showConfirm(btn.dataset.confirm, {
                title: btn.dataset.confirmTitle || 'Are you sure?',
                confirmText: btn.dataset.confirmText || 'Continue',
                danger,
                onConfirm: () => {
                    if (form) {
                        form.submit();
                    }
                },
            });
        });
    });
}

// ---- Loading states on submit buttons --------------------------
// Prevent duplicate submissions and give visual feedback while a
// form is being processed. Any form with a submit button gets this
// behaviour automatically; a button is restored if its page fails.
function setButtonLoading(btn, loading) {
    if (!btn) {
        return;
    }
    if (loading) {
        if (btn.dataset.loadingOriginal === undefined) {
            btn.dataset.loadingOriginal = btn.innerHTML;
        }
        btn.disabled = true;
        btn.classList.add('btn-loading');
        btn.dataset.loading = '1';
    } else {
        btn.disabled = false;
        btn.classList.remove('btn-loading');
        delete btn.dataset.loading;
        if (btn.dataset.loadingOriginal !== undefined) {
            btn.innerHTML = btn.dataset.loadingOriginal;
            delete btn.dataset.loadingOriginal;
        }
    }
}

function initFormLoading() {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.loadingBound) {
            return;
        }
        form.dataset.loadingBound = '1';

        form.addEventListener('submit', (e) => {
            if (e.defaultPrevented) {
                return;
            }
            // Keep the current confirm-modal flow intact: if this form has a
            // data-confirm submit button, the modal already handles feedback
            // and calls form.submit() programmatically (no submit event).
            const confirmBtn = form.querySelector('button[type="submit"][data-confirm]');
            if (confirmBtn) {
                return;
            }
            form.querySelectorAll('button[type="submit"]').forEach((btn) => setButtonLoading(btn, true));
        });
    });
}

// Keep window.alert mapped to the styled toast.
window.alert = function (msg) {
    showToast(String(msg), 'error');
};

// ---- Global error surfacing ------------------------------------
// Unexpected JS errors / failed AJAX calls should never be silent.
window.addEventListener('error', (e) => {
    if (e.target && e.target !== window) {
        return; // resource load errors (img/css) are not actionable
    }
    console.error(e.error || e.message);
    showToast('An unexpected error occurred. Please try again.', 'error');
});

window.addEventListener('unhandledrejection', (e) => {
    console.error(e.reason);
    showToast('Network error: the request could not be completed.', 'error');
});

// ---- Password validation helpers ------------------------------
function passesPasswordPolicy(password) {
    const hasMinLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasSymbol = /[^A-Za-z0-9]/.test(password);
    return hasMinLength && (hasUpper || hasSymbol);
}

function checkPassword(inputId) {
    const pass = document.getElementById(inputId).value;
    if (!passesPasswordPolicy(pass)) {
        showToast('Password must be at least 8 chars and include uppercase or symbol', 'error');
        return false;
    }
    return true;
}

// ---- Custom inline form validation (replaces native bubbles) --
function initFormValidation() {
    document.querySelectorAll('form[data-validate]').forEach((form) => {
        if (form.dataset.validationBound) {
            return;
        }
        form.dataset.validationBound = '1';
        form.setAttribute('novalidate', 'novalidate');

        const fieldWrapper = (input) =>
            input.closest('.field-wrapper') ||
            input.closest('.grid') ||
            input.parentElement;

        const showError = (input, message) => {
            const wrapper = fieldWrapper(input);
            let err = wrapper.querySelector('.field-error');
            if (!err) {
                err = document.createElement('span');
                err.className = 'field-error';
                wrapper.appendChild(err);
            }
            err.textContent = message;
            input.classList.add('field-invalid');
            wrapper.classList.add('field-shake');
            setTimeout(() => wrapper.classList.remove('field-shake'), 420);
        };

        const clearError = (input) => {
            const wrapper = fieldWrapper(input);
            const err = wrapper.querySelector('.field-error');
            if (err) {
                err.remove();
            }
            input.classList.remove('field-invalid');
        };

        const labelFor = (input) => {
            const id = input.id || input.name;
            const label = form.querySelector(`label[for="${id}"]`);
            return (label ? label.textContent.trim() : input.name).replace(/[:*]$/, '');
        };

        const validate = (checkAll) => {
            let firstBad = null;
            let anyError = false;
            form.querySelectorAll('input[required], select[required], textarea[required], input[data-min], input[data-pattern], input[data-match], input[data-password-policy]').forEach((input) => {
                if (input.type === 'hidden' || input.type === 'submit' || input.disabled) {
                    return;
                }
                clearError(input);
                const val = input.value.trim();
                const label = labelFor(input);

                if (input.hasAttribute('required') && val === '') {
                    showError(input, `${label} is required`);
                    anyError = true;
                    if (!firstBad) {
                        firstBad = input;
                    }
                    return;
                }
                const min = parseInt(input.dataset.min, 10);
                if (input.dataset.min && val !== '' && val.length < min) {
                    showError(input, `${label} must be at least ${min} characters`);
                    anyError = true;
                    if (!firstBad) {
                        firstBad = input;
                    }
                    return;
                }
                if (input.dataset.pattern && val !== '' && !new RegExp(input.dataset.pattern).test(val)) {
                    showError(input, `${label} is invalid`);
                    anyError = true;
                    if (!firstBad) {
                        firstBad = input;
                    }
                    return;
                }
                if (input.dataset.match && val !== '') {
                    const other = document.getElementById(input.dataset.match);
                    if (other && val !== other.value.trim()) {
                        showError(input, 'Passwords do not match');
                        anyError = true;
                        if (!firstBad) {
                            firstBad = input;
                        }
                        return;
                    }
                }
                if (input.dataset.passwordPolicy && val !== '' && !passesPasswordPolicy(val)) {
                    showError(input, 'Password must be at least 8 chars and include uppercase or symbol');
                    anyError = true;
                    if (!firstBad) {
                        firstBad = input;
                    }
                    return;
                }
            });
            return { ok: !anyError, firstBad };
        };

        form.addEventListener('submit', (e) => {
            const res = validate(false);
            if (!res.ok) {
                e.preventDefault();
                if (res.firstBad) {
                    res.firstBad.focus();
                }
            }
        });

        form.querySelectorAll('input, select').forEach((input) => {
            input.addEventListener('input', () => {
                clearError(input);
            });
            input.addEventListener('change', () => {
                clearError(input);
            });
        });
    });
}

// ---- Passwords show/hide toggles ------------------------------
function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        if (btn.dataset.bound) {
            return;
        }
        const input = document.getElementById(btn.getAttribute('aria-controls'));
        if (!input) {
            return;
        }
        const eyeOpen = btn.querySelector('[data-eye-open]');
        const eyeClosed = btn.querySelector('[data-eye-closed]');
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            if (eyeOpen) {
                eyeOpen.classList.toggle('hidden', !show);
            }
            if (eyeClosed) {
                eyeClosed.classList.toggle('hidden', show);
            }
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
        btn.dataset.bound = '1';
    });
}

// ---- Header sidebar drawer --------------------------------------
function initHeaderMenu() {
    const toggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('app-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (!toggle || !sidebar || toggle.dataset.bound) {
        return;
    }

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay?.classList.add('hidden');
    };

    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay?.classList.remove('hidden');
    };

    toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        if (sidebar.classList.contains('-translate-x-full')) {
            openSidebar();
        } else {
            closeSidebar();
        }
    });

    overlay?.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
    toggle.dataset.bound = '1';
}

// ---- Desktop sidebar collapse toggle -----------------------------
function initSidebarCollapse() {
    const toggle = document.getElementById('sidebar-collapse-toggle');
    if (!toggle || toggle.dataset.bound) {
        return;
    }

    const applyState = (collapsed) => {
        document.body.classList.toggle('sidebar-collapsed', collapsed);
        toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
    };

    applyState(localStorage.getItem('sidebar-collapsed') === '1');

    toggle.addEventListener('click', () => {
        const collapsed = !document.body.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebar-collapsed', collapsed ? '1' : '0');
        applyState(collapsed);
    });
    toggle.dataset.bound = '1';
}

// ---- Header notifications bell dropdown -----------------------
function initNotificationsBell() {
    const root = document.getElementById('notif-dropdown-root');
    if (!root || root.dataset.bound) {
        return;
    }
    const bell = document.getElementById('notif-bell');
    const panel = document.getElementById('notif-panel');
    if (!bell || !panel) {
        return;
    }

    const close = () => panel.classList.add('hidden');

    bell.addEventListener('click', (e) => {
        e.stopPropagation();
        panel.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) {
            close();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            close();
        }
    });

    root.dataset.bound = '1';
}

// ---- Top navbar offset (measured from actual header height) -----
function initNavbarOffset() {
    const header = document.getElementById('app-header');
    if (!header || header.dataset.bound) {
        return;
    }

    const sync = () => {
        document.documentElement.style.setProperty('--navbar-height', `${header.offsetHeight}px`);
    };

    sync();
    window.addEventListener('resize', sync);
    header.dataset.bound = '1';
}

// ============================================================
// Polling service (auto-refresh data without page reload)
// ============================================================

const POLL_INTERVALS = {
    notifications: 30000,
    announcements: 60000,
    messages: 30000,
    dashboard: 60000,
    gradeSubmissions: 60000,
    enrollmentRequests: 30000,
};

const POLL_ERROR_BACKOFF = {
    base: 5000,
    max: 120000,
    multiplier: 2,
};

const PollingService = {
    intervals: {},
    errorCounts: {},
    active: true,
    rafQueue: null,
    pendingUpdates: {},

    start(key, url, options) {
        if (this.intervals[key]) {
            return;
        }

        const interval = options?.interval || POLL_INTERVALS[key] || 30000;
        const immediate = options?.immediate !== false;
        const onUpdate = options?.onUpdate || (() => {});
        const onError = options?.onError || (() => {});

        if (immediate) {
            this.fetch(key, url, onUpdate, onError);
        }

        this.intervals[key] = setInterval(() => {
            this.fetch(key, url, onUpdate, onError);
        }, interval);
    },

    stop(key) {
        if (this.intervals[key]) {
            clearInterval(this.intervals[key]);
            delete this.intervals[key];
        }
    },

    stopAll() {
        Object.keys(this.intervals).forEach((key) => this.stop(key));
    },

    async fetch(key, url, onUpdate, onError) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            this.errorCounts[key] = 0;
            onUpdate(data, key);
        } catch (error) {
            this.errorCounts[key] = (this.errorCounts[key] || 0) + 1;
            onError(error, key);
        }
    },

    // Smooth DOM update with requestAnimationFrame and pulse animation
    smoothUpdate(element, updateFn) {
        if (!element) {
            return;
        }

        const oldValue = element.textContent;

        requestAnimationFrame(() => {
            updateFn(element);

            const newValue = element.textContent;
            if (oldValue !== newValue) {
                element.style.transition = 'opacity 0.2s ease, transform 0.2s ease';
                element.style.opacity = '0.4';
                element.style.transform = 'scale(0.95)';

                requestAnimationFrame(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'scale(1)';
                });
            }
        });
    },

    // Update the notification bell badge and count
    updateNotificationsBell(data) {
        const badge = document.getElementById('notif-badge');
        const countEl = document.getElementById('notif-count');

        this.smoothUpdate(badge, (el) => {
            if (el && data.unreadCount !== undefined) {
                el.textContent = data.unreadCount;
                el.style.display = data.unreadCount > 0 ? 'flex' : 'none';
            }
        });

        this.smoothUpdate(countEl, (el) => {
            if (el && data.unreadCount !== undefined) {
                el.textContent = data.unreadCount;
            }
        });
    },

    // Update the announcement unread count
    updateAnnouncementUnread(data) {
        const badge = document.getElementById('announcement-unread-badge');

        this.smoothUpdate(badge, (el) => {
            if (el && data.unreadCount !== undefined) {
                el.textContent = data.unreadCount;
                el.style.display = data.unreadCount > 0 ? 'inline-flex' : 'none';
            }
        });
    },

    // Update the message pending count in the sidebar
    updateMessageCount(data) {
        const badge = document.getElementById('message-pending-badge');

        this.smoothUpdate(badge, (el) => {
            if (el && data.pendingCount !== undefined) {
                el.textContent = data.pendingCount;
                el.style.display = data.pendingCount > 0 ? 'inline-flex' : 'none';
            }
        });
    },

    // Update dashboard stats
    updateDashboardStats(data) {
        Object.keys(data).forEach((key) => {
            const el = document.getElementById(`poll-stat-${key}`);
            if (el) {
                this.smoothUpdate(el, (element) => {
                    element.textContent = data[key];
                });
            }
        });
    },

    // Update grade submission progress
    updateGradeSubmissions(data) {
        if (data.summary) {
            const submittedEl = document.getElementById('poll-grade-submitted');
            const pendingEl = document.getElementById('poll-grade-pending');
            const lateEl = document.getElementById('poll-grade-late');

            this.smoothUpdate(submittedEl, (el) => {
                if (el) el.textContent = data.summary.submitted;
            });
            this.smoothUpdate(pendingEl, (el) => {
                if (el) el.textContent = data.summary.pending;
            });
            this.smoothUpdate(lateEl, (el) => {
                if (el) el.textContent = data.summary.late;
            });
        }
    },

    // Update enrollment request count
    updateEnrollmentRequests(data) {
        const badge = document.getElementById('poll-enrollment-count');

        this.smoothUpdate(badge, (el) => {
            if (el && data.pendingCount !== undefined) {
                el.textContent = data.pendingCount;
                el.style.display = data.pendingCount > 0 ? 'inline-flex' : 'none';
            }
        });
    },
};

// ---- Smooth page transitions (fade out on navigation) ---------
function initPageTransitions() {
    const main = document.getElementById('page-main');
    if (!main) {
        return;
    }

    document.addEventListener('click', (e) => {
        if (e.defaultPrevented || e.button !== 0) {
            return;
        }
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
            return;
        }
        const link = e.target.closest('a');
        if (!link) {
            return;
        }
        if (link.target === '_blank' || link.download) {
            return;
        }
        if (link.hasAttribute('onclick')) {
            return;
        }
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
            return;
        }
        if (link.origin !== window.location.origin) {
            return;
        }

        e.preventDefault();
        main.classList.add('page-fade-out');
        setTimeout(() => {
            window.location.assign(href);
        }, 140);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Start notification polling on all authenticated pages.
    PollingService.start('notifications', '/poll/notifications', {
        onUpdate: (data) => PollingService.updateNotificationsBell(data),
    });

    // Start announcement polling on all authenticated pages.
    PollingService.start('announcements', '/poll/announcements', {
        onUpdate: (data) => PollingService.updateAnnouncementUnread(data),
    });

    // Start message polling on all authenticated pages.
    PollingService.start('messages', '/poll/messages', {
        onUpdate: (data) => PollingService.updateMessageCount(data),
    });

    // Start dashboard polling on dashboard pages.
    const dashboardEl = document.getElementById('poll-dashboard');
    if (dashboardEl) {
        PollingService.start('dashboard', '/poll/dashboard', {
            onUpdate: (data) => PollingService.updateDashboardStats(data),
        });
    }

    // Start grade submission polling on grade submission pages.
    const gradeSubEl = document.getElementById('poll-grade-submissions');
    if (gradeSubEl) {
        PollingService.start('gradeSubmissions', '/poll/grade-submissions', {
            onUpdate: (data) => PollingService.updateGradeSubmissions(data),
        });
    }

    // Start enrollment request polling on teacher enrollment pages.
    const enrollmentEl = document.getElementById('poll-enrollment-requests');
    if (enrollmentEl) {
        PollingService.start('enrollmentRequests', '/poll/enrollment-requests', {
            onUpdate: (data) => PollingService.updateEnrollmentRequests(data),
        });
    }
});

document.addEventListener('DOMContentLoaded', initHeaderMenu);
document.addEventListener('DOMContentLoaded', initSidebarCollapse);
document.addEventListener('DOMContentLoaded', initPasswordToggles);
document.addEventListener('DOMContentLoaded', initFormValidation);
document.addEventListener('DOMContentLoaded', initConfirmDialogs);
document.addEventListener('DOMContentLoaded', initNotificationsBell);
document.addEventListener('DOMContentLoaded', initNavbarOffset);
document.addEventListener('DOMContentLoaded', initFormLoading);
document.addEventListener('DOMContentLoaded', initPageTransitions);

// Expose helpers for inline scripts / onclick handlers.
window.showToast = showToast;
window.showNotice = showNotice;
window.floatingAlert = floatingAlert;
window.showModal = showModal;
window.dismissToast = dismissToast;
window.setButtonLoading = setButtonLoading;
window.showConfirm = showConfirm;
window.PollingService = PollingService;

