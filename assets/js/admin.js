/**
 * Pet Care System - Admin Management JavaScript
 * University Web Application Development Project
 * Member 4: Admin Service & Appointment Management
 *
 * Vanilla JavaScript only — no jQuery or framework.
 */

/* ── Mobile Sidebar Toggle ────────────────────────────────── */
(function () {
    'use strict';

    var sidebar  = document.getElementById('adminSidebar');
    var hamburger = document.getElementById('hamburgerBtn');
    var overlay  = document.getElementById('sidebarOverlay');

    function openSidebar() {
        if (sidebar)  sidebar.classList.add('open');
        if (overlay)  overlay.classList.add('visible');
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        if (sidebar)  sidebar.classList.remove('open');
        if (overlay)  overlay.classList.remove('visible');
        document.body.style.overflow = '';
    }

    if (hamburger) {
        hamburger.addEventListener('click', function () {
            if (sidebar && sidebar.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Close sidebar on ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { closeSidebar(); }
    });
}());

/* ── Delete Confirmation Helper ───────────────────────────── */
/**
 * Show a quick inline confirmation before submitting a delete form.
 * The form already shows a full confirmation page — this is a JS
 * backup guard only on any element with data-confirm attribute.
 */
(function () {
    'use strict';

    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-confirm]');
        if (!el) return;

        var msg = el.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
        if (!window.confirm(msg)) {
            e.preventDefault();
            e.stopPropagation();
        }
    });
}());

/* ── Flash Message Auto-Dismiss ───────────────────────────── */
(function () {
    'use strict';

    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity    = '0';
            setTimeout(function () { alert.style.display = 'none'; }, 500);
        }, 5000);
    });
}());

/* ── Appointment Status Filter ────────────────────────────── */
(function () {
    'use strict';

    var filterBtns = document.querySelectorAll('[data-filter]');
    var apptCards  = document.querySelectorAll('[data-status]');

    if (!filterBtns.length || !apptCards.length) return;

    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var filter = btn.getAttribute('data-filter');

            // Update active state
            filterBtns.forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');

            // Show/hide cards
            apptCards.forEach(function (card) {
                if (filter === 'all') {
                    card.style.display = '';
                } else {
                    var status = (card.getAttribute('data-status') || '').toLowerCase();
                    card.style.display = (status === filter) ? '' : 'none';
                }
            });

            // Show empty state if no visible cards
            var visibleCount = 0;
            apptCards.forEach(function (c) {
                if (c.style.display !== 'none') visibleCount++;
            });

            var noResults = document.getElementById('noFilterResults');
            if (noResults) {
                noResults.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        });
    });
}());

/* ── Form Validation Feedback ─────────────────────────────── */
(function () {
    'use strict';

    var adminForm = document.getElementById('adminServiceForm');
    if (!adminForm) return;

    adminForm.addEventListener('submit', function (e) {
        var valid = true;

        // Clear previous errors
        adminForm.querySelectorAll('.form-error').forEach(function (el) {
            el.classList.remove('visible');
            el.textContent = '';
        });
        adminForm.querySelectorAll('.form-control').forEach(function (el) {
            el.classList.remove('is-invalid');
        });

        // Service name
        var nameField = document.getElementById('service_name');
        if (nameField && nameField.value.trim() === '') {
            showFieldError(nameField, 'Service name is required.');
            valid = false;
        }

        // Category
        var catField = document.getElementById('category');
        if (catField && catField.value.trim() === '') {
            showFieldError(catField, 'Category is required.');
            valid = false;
        }

        // Pet type
        var petField = document.getElementById('target_pet_type');
        if (petField && petField.value.trim() === '') {
            showFieldError(petField, 'Target pet type is required.');
            valid = false;
        }

        // Description
        var descField = document.getElementById('description');
        if (descField && descField.value.trim() === '') {
            showFieldError(descField, 'Description is required.');
            valid = false;
        }

        // Price
        var priceField = document.getElementById('price');
        if (priceField) {
            var priceVal = parseFloat(priceField.value);
            if (priceField.value.trim() === '' || isNaN(priceVal)) {
                showFieldError(priceField, 'Price must be a valid number.');
                valid = false;
            } else if (priceVal < 0) {
                showFieldError(priceField, 'Price cannot be negative.');
                valid = false;
            }
        }

        if (!valid) { e.preventDefault(); }
    });

    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        var errorEl = document.getElementById(field.id + '_error');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('visible');
        }
    }
}());

/* ── Stat Number Animation ────────────────────────────────── */
function animateCount(elementId, target) {
    var el = document.getElementById(elementId);
    if (!el || target === 0) return;
    var start = 0;
    var duration = 800;
    var step = Math.ceil(target / (duration / 16));
    var timer = setInterval(function () {
        start += step;
        if (start >= target) {
            el.textContent = target;
            clearInterval(timer);
        } else {
            el.textContent = start;
        }
    }, 16);
}
