/**
 * DevHire Admin Panel — shared UI behavior
 */
(function () {
    'use strict';

    function getConfig() {
        var el = document.getElementById('admin-panel-config');
        if (!el || !el.textContent) {
            return {};
        }
        try {
            return JSON.parse(el.textContent);
        } catch (e) {
            return {};
        }
    }

    var config = getConfig();

    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            if (!document.querySelector('.modal.show')) {
                document.body.style.overflow = '';
            }
        }
    }

    function initMobileMenu() {
        var btn = document.getElementById('mobileMenuBtn');
        var sidebar = document.getElementById('sidebar');
        if (!btn || !sidebar) {
            return;
        }

        var overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        function closeMenu() {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        }

        btn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show');
        });

        overlay.addEventListener('click', closeMenu);
    }

    function initDropdowns() {
        var notifications = document.getElementById('notifications');
        var notificationsDropdown = document.getElementById('notificationsDropdown');
        var adminMenu = document.getElementById('adminMenu');
        var adminMenuDropdown = document.getElementById('adminMenuDropdown');

        if (notifications && notificationsDropdown) {
            notifications.addEventListener('click', function (e) {
                e.stopPropagation();
                notificationsDropdown.classList.toggle('show');
                if (adminMenuDropdown) {
                    adminMenuDropdown.classList.remove('show');
                }
            });
        }

        if (adminMenu && adminMenuDropdown) {
            adminMenu.addEventListener('click', function (e) {
                e.stopPropagation();
                adminMenuDropdown.classList.toggle('show');
                if (notificationsDropdown) {
                    notificationsDropdown.classList.remove('show');
                }
            });
        }

        document.addEventListener('click', function () {
            if (notificationsDropdown) {
                notificationsDropdown.classList.remove('show');
            }
            if (adminMenuDropdown) {
                adminMenuDropdown.classList.remove('show');
            }
        });
    }

    function updateBadge(count) {
        var badge = document.querySelector('.notifications-badge');
        if (!badge) {
            return;
        }
        badge.textContent = String(Math.max(0, count));
        if (count <= 0) {
            badge.classList.add('is-hidden');
        } else {
            badge.classList.remove('is-hidden');
        }
    }

    function initNotifications() {
        var api = config.notificationApi;
        if (!api) {
            return;
        }

        document.querySelectorAll('.notification-item').forEach(function (item) {
            item.addEventListener('click', function () {
                var notificationId = this.dataset.id;
                var actionUrl = this.dataset.actionUrl || '';

                fetch(api, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'mark_read',
                        notification_id: notificationId,
                        csrf_token: config.csrfToken
                    })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            item.classList.remove('unread');
                            var badge = document.querySelector('.notifications-badge');
                            var current = parseInt(badge.textContent, 10) || 0;
                            updateBadge(current - 1);
                        }
                    });

                if (actionUrl) {
                    window.location.href = actionUrl;
                }
            });
        });
    }

    function initModals() {
        document.querySelectorAll('.modal').forEach(function (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeModal(modal.id);
                }
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-modal-close');
                if (id) {
                    closeModal(id);
                }
            });
        });
    }

    function markAllAsRead() {
        var api = config.notificationApi;
        if (!api) {
            return;
        }

        fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'mark_all_read',
                csrf_token: config.csrfToken
            })
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    document.querySelectorAll('.notification-item').forEach(function (item) {
                        item.classList.remove('unread');
                    });
                    updateBadge(0);
                }
            });
    }

    function setButtonLoading(btn, loading) {
        if (!btn) {
            return;
        }
        if (loading) {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.classList.add('is-loading');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Please wait…';
        } else {
            btn.classList.remove('is-loading');
            btn.disabled = false;
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
            }
        }
    }

    window.openModal = openModal;
    window.closeModal = closeModal;
    window.markAllAsRead = markAllAsRead;

    function initActionDropdowns() {
        document.querySelectorAll('.action-dropdown').forEach(function (wrap) {
            var toggle = wrap.querySelector('.action-dropdown-toggle');
            var menu = wrap.querySelector('.action-dropdown-menu');
            if (!toggle || !menu) {
                return;
            }

            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('.action-dropdown-menu.show').forEach(function (openMenu) {
                    if (openMenu !== menu) {
                        openMenu.classList.remove('show');
                    }
                });
                menu.classList.toggle('show');
                toggle.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
            });

            menu.querySelectorAll('[data-action]').forEach(function (item) {
                item.addEventListener('click', function () {
                    menu.classList.remove('show');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            });
        });

        document.addEventListener('click', function () {
            document.querySelectorAll('.action-dropdown-menu.show').forEach(function (menu) {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.action-dropdown-toggle[aria-expanded="true"]').forEach(function (btn) {
                btn.setAttribute('aria-expanded', 'false');
            });
        });
    }

    function applicationApiRequest(payload) {
        var api = config.applicationApi || '';
        if (!api) {
            return Promise.reject(new Error('Application API not configured'));
        }
        return fetch(api, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(Object.assign({}, payload, { csrf_token: config.csrfToken }))
        }).then(function (r) { return r.json(); });
    }

    window.AdminPanel = {
        config: config,
        openModal: openModal,
        closeModal: closeModal,
        markAllAsRead: markAllAsRead,
        setButtonLoading: setButtonLoading,
        applicationApiRequest: applicationApiRequest,
        apiFetch: function (url, options) {
            options = options || {};
            options.headers = options.headers || {};
            if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
                options.body.csrf_token = options.body.csrf_token || config.csrfToken;
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(options.body);
            }
            return fetch(url, options).then(function (r) { return r.json(); });
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        initMobileMenu();
        initDropdowns();
        initNotifications();
        initModals();
        initActionDropdowns();

        var badge = document.querySelector('.notifications-badge');
        if (badge && parseInt(badge.textContent, 10) <= 0) {
            badge.classList.add('is-hidden');
        }
        // Update any server-rendered "time-ago" elements and keep them fresh.
        function formatTimeAgo(ts) {
            if (!ts) return 'just now';
            var t = Date.parse(ts);
            if (isNaN(t)) return 'just now';
            var seconds = Math.floor((Date.now() - t) / 1000);
            if (seconds < 45) return 'just now';
            var units = [
                { s: 31536000, label: 'year' },
                { s: 2592000, label: 'month' },
                { s: 604800, label: 'week' },
                { s: 86400, label: 'day' },
                { s: 3600, label: 'hour' },
                { s: 60, label: 'minute' }
            ];
            for (var i = 0; i < units.length; i++) {
                var count = Math.floor(seconds / units[i].s);
                if (count >= 1) {
                    return count + ' ' + units[i].label + (count === 1 ? '' : 's') + ' ago';
                }
            }
            return 'just now';
        }

        function updateTimeAgoElements() {
            document.querySelectorAll('.time-ago').forEach(function (el) {
                var ts = el.dataset.ts;
                if (ts) {
                    el.textContent = formatTimeAgo(ts);
                }
            });
        }

        updateTimeAgoElements();
        setInterval(updateTimeAgoElements, 60 * 1000);
    });
})();
