// DevHire - Main JavaScript
// ============================================

const DEVHIRE_BASE_URL = typeof window.DEVHIRE_BASE_URL === 'string'
    ? window.DEVHIRE_BASE_URL.replace(/\/+$/, '')
    : '';

const DEVHIRE_CSRF_TOKEN = typeof window.DEVHIRE_CSRF_TOKEN === 'string' ? window.DEVHIRE_CSRF_TOKEN : '';

function apiUrl(path) {
    const normalizedPath = String(path || '').replace(/^\/+/, '');
    const basePath = DEVHIRE_BASE_URL !== '' ? DEVHIRE_BASE_URL : '';

    if (normalizedPath === '') {
        return basePath || '/';
    }

    return `${basePath}/${normalizedPath}`.replace(/([^:]\/)\/+/g, '$1');
}

function validateEmail(email) {
    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return pattern.test(String(email || '').trim());
}

function showNotification(message, type = 'info') {
    const existingContainer = document.querySelector('.notification-stack');
    if (!existingContainer) {
        const container = document.createElement('div');
        container.className = 'notification-stack';
        document.body.appendChild(container);
    }

    const stack = document.querySelector('.notification-stack');
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;

    const iconClass = type === 'success'
        ? 'check-circle'
        : type === 'error'
            ? 'exclamation-circle'
            : type === 'warning'
                ? 'triangle-exclamation'
                : 'circle-info';

    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${iconClass}"></i>
            <span></span>
        </div>
        <button type="button" class="notification-close" aria-label="Dismiss notification">&times;</button>
    `;
    notification.querySelector('span').textContent = message;

    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.add('is-leaving');
        setTimeout(() => notification.remove(), 180);
    });

    stack.appendChild(notification);

    requestAnimationFrame(() => {
        notification.classList.add('is-visible');
    });

    window.setTimeout(() => {
        if (notification.isConnected) {
            notification.classList.add('is-leaving');
            window.setTimeout(() => notification.remove(), 180);
        }
    }, 4500);
}

function setButtonLoading(button, isLoading, loadingText = 'Loading...') {
    if (!button) {
        return;
    }

    if (isLoading) {
        if (!button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.innerHTML = `<span class="btn-spinner"></span><span>${loadingText}</span>`;
        return;
    }

    button.disabled = false;
    button.removeAttribute('aria-busy');

    if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
        delete button.dataset.originalHtml;
    }
}

function debounce(func, wait) {
    let timeoutId;
    return function executedFunction(...args) {
        window.clearTimeout(timeoutId);
        timeoutId = window.setTimeout(() => func.apply(this, args), wait);
    };
}

function handleFileUpload(input) {
    const file = input.files && input.files[0];
    if (!file) {
        return;
    }

    const maxSize = 5 * 1024 * 1024;
    const allowedExtensions = ['pdf', 'doc', 'docx'];
    const extension = file.name.split('.').pop().toLowerCase();

    if (file.size > maxSize) {
        showNotification('File size must be 5 MB or smaller.', 'error');
        input.value = '';
        return;
    }

    if (!allowedExtensions.includes(extension)) {
        showNotification('Only PDF, DOC, and DOCX files are allowed.', 'error');
        input.value = '';
        return;
    }

    const existingLabel = input.parentElement.querySelector('.file-name');
    if (existingLabel) {
        existingLabel.remove();
    }

    const label = document.createElement('div');
    label.className = 'file-name';
    label.textContent = file.name;
    input.parentElement.appendChild(label);
}

async function saveJob(jobId, button) {
    try {
        if (!jobId) {
            throw new Error('Invalid job selected.');
        }

        if (button) {
            setButtonLoading(button, true, 'Saving...');
        }

        const response = await fetch(apiUrl('api/handler.php?action=saveJob'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-Token': DEVHIRE_CSRF_TOKEN
            },
            body: JSON.stringify({ job_id: jobId })
        });

        const result = await response.json().catch(() => ({}));

        if (response.status === 401 || response.status === 403) {
            window.location.href = apiUrl('pages/login.php');
            return;
        }

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Unable to save job.');
        }

        if (button) {
            button.classList.add('is-saved');
            button.innerHTML = '<i class="fas fa-bookmark"></i>';
            setButtonLoading(button, false);
        }

        showNotification(result.message || 'Job saved successfully.', 'success');
    } catch (error) {
        if (button) {
            setButtonLoading(button, false);
        }
        showNotification(error.message || 'Unable to save job.', 'error');
    }
}

async function removeSavedJob(jobId) {
    const response = await fetch(apiUrl('api/handler.php?action=removeSavedJob'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-Token': DEVHIRE_CSRF_TOKEN
        },
        body: JSON.stringify({ job_id: jobId })
    });

    return response.json();
}

function observeElements() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const narrowViewport = window.matchMedia('(max-width: 900px)').matches;
    if (reducedMotion || narrowViewport) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        return;
    }

    const observer = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                instance.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -80px 0px' });

    document.querySelectorAll('.job-card, .feature-card, .testimonial-card, .stat-card, .timeline-item, .panel').forEach((element) => {
        observer.observe(element);
    });
}

function setupStickyNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) {
        return;
    }

    let ticking = false;
    const toggleNavbarState = () => {
        navbar.classList.toggle('is-scrolled', window.scrollY > 24);
        ticking = false;
    };

    const onScroll = () => {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(toggleNavbarState);
    };

    toggleNavbarState();
    window.addEventListener('scroll', onScroll, { passive: true });
}

function setupMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');

    if (!hamburger || !navMenu) {
        return;
    }

    hamburger.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        hamburger.classList.toggle('active');
    });

    navMenu.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
            hamburger.classList.remove('active');
        });
    });
}

function setupSearchDebounce() {
    const searchInputs = document.querySelectorAll('input[type="search"]');
    if (!searchInputs.length) {
        return;
    }

    searchInputs.forEach((input) => {
        input.addEventListener('input', debounce((event) => {
            const query = event.target.value.trim();
            if (query.length >= 2) {
                document.dispatchEvent(new CustomEvent('devhire:search', { detail: { query } }));
            }
        }, 250));
    });
}

function setupFormLoadingStates() {
    document.querySelectorAll('form').forEach((form) => {
        if (form.dataset.loadingBound === 'true') {
            return;
        }

        form.dataset.loadingBound = 'true';
        form.addEventListener('submit', (event) => {
            if (form.dataset.async === 'true') {
                event.preventDefault();
            }

            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                setButtonLoading(submitButton, true, 'Submitting...');
            }
        });
    });
}

function setupDashboardShortcuts() {
    document.querySelectorAll('[data-save-job]').forEach((button) => {
        button.addEventListener('click', () => {
            const jobId = Number(button.dataset.saveJob);
            saveJob(jobId, button);
        });
    });
}

function setupFormValidation() {
    const emailFields = document.querySelectorAll('input[type="email"]');
    emailFields.forEach((field) => {
        field.addEventListener('blur', () => {
            if (field.value && !validateEmail(field.value)) {
                field.setCustomValidity('Enter a valid email address.');
            } else {
                field.setCustomValidity('');
            }
        });
    });
}

function setupCounterAnimations() {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const narrowViewport = window.matchMedia('(max-width: 900px)').matches;
    if (reducedMotion || narrowViewport) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        return;
    }

    const counters = document.querySelectorAll('.stat-number');
    if (!counters.length) {
        return;
    }

    const animateCounter = (element, target) => {
        const duration = 1600;
        const start = 0;
        const increment = Math.max(1, Math.floor(target / (duration / 16)));
        let current = start;

        const timer = window.setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                window.clearInterval(timer);
                return;
            }
            element.textContent = current.toLocaleString();
        }, 16);
    };

    const observer = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting || entry.target.dataset.animated === 'true') {
                return;
            }

            const value = parseInt(entry.target.textContent.replace(/[^0-9]/g, ''), 10);
            if (!Number.isNaN(value)) {
                animateCounter(entry.target, value);
                entry.target.dataset.animated = 'true';
            }

            instance.unobserve(entry.target);
        });
    }, { threshold: 0.5 });

    counters.forEach((counter) => observer.observe(counter));
}

function lazyLoadImages() {
    if (!('IntersectionObserver' in window)) {
        return;
    }

    const images = document.querySelectorAll('img[data-src]');
    if (!images.length) {
        return;
    }

    const imageObserver = new IntersectionObserver((entries, instance) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            const image = entry.target;
            image.src = image.dataset.src;
            image.removeAttribute('data-src');
            instance.unobserve(image);
        });
    });

    images.forEach((image) => imageObserver.observe(image));
}

function init() {
    setupMobileMenu();
    setupStickyNavbar();
    setupSearchDebounce();
    setupFormLoadingStates();
    setupDashboardShortcuts();
    setupFormValidation();
    observeElements();
    setupCounterAnimations();
    lazyLoadImages();
}

document.addEventListener('DOMContentLoaded', init);

window.DevHire = {
    validateEmail,
    showNotification,
    handleFileUpload,
    saveJob,
    removeSavedJob,
    debounce,
    setButtonLoading
};
