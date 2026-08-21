/**
 * admin.js — Pet Care System
 * Client-side enhancements for admin pages.
 */

(function () {
    'use strict';

    /* ── Auto-dismiss flash messages after 4 seconds ──────────── */
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (el) {
        setTimeout(function () {
            el.style.transition  = 'opacity 0.5s ease';
            el.style.opacity     = '0';
            setTimeout(function () { el.style.display = 'none'; }, 500);
        }, 4000);
    });

    /* ── Confirm destructive actions ──────────────────────────── */
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    /* ── Service price input: format on blur ──────────────────── */
    var priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('blur', function () {
            var val = parseFloat(this.value);
            if (!isNaN(val) && val >= 0) {
                this.value = val.toFixed(2);
            }
        });
    }

})();
