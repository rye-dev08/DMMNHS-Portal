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

// Keep window.alert mapped to the styled toast.
window.alert = function (msg) {
    showToast(String(msg), 'error');
};

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
            form.querySelectorAll('input[required], select[required], textarea[required], input[data-min], input[data-pattern], input[data-match]').forEach((input) => {
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

document.addEventListener('DOMContentLoaded', initHeaderMenu);
document.addEventListener('DOMContentLoaded', initPasswordToggles);
document.addEventListener('DOMContentLoaded', initFormValidation);
