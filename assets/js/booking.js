/**
 * booking.js — Pet Care System
 * Client-side enhancements for booking and payment forms.
 * All critical validation is also enforced server-side (PHP).
 */

(function () {
    'use strict';

    /* ── Booking Form Enhancements ────────────────────────────── */
    var bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        // Set minimum date to tomorrow
        var dateInput = document.getElementById('appointment_date');
        if (dateInput && !dateInput.value) {
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            dateInput.min = tomorrow.toISOString().split('T')[0];
        }

        // Disable submit button on valid submit to prevent double-click
        bookingForm.addEventListener('submit', function (e) {
            var btn = document.getElementById('submitBookingBtn');
            if (bookingForm.checkValidity()) {
                if (btn) {
                    btn.disabled    = true;
                    btn.textContent = 'Processing…';
                }
            }
        });
    }

    /* ── Payment Form Enhancements ────────────────────────────── */
    var paymentForm = document.getElementById('paymentForm');
    if (paymentForm) {
        var methodSel  = document.getElementById('payment_method');
        var cardFields = document.getElementById('cardFields');

        function toggleCardFields() {
            if (!methodSel || !cardFields) { return; }
            var val = methodSel.value;
            cardFields.style.display = (val === 'Credit Card' || val === 'Debit Card') ? 'block' : 'none';
        }

        if (methodSel) {
            toggleCardFields();
            methodSel.addEventListener('change', toggleCardFields);
        }

        // Auto-format demo card input with spaces
        var demoCard = document.getElementById('demo_card');
        if (demoCard) {
            demoCard.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').substring(0, 16);
                this.value = v.replace(/(.{4})/g, '$1 ').trim();
            });
        }

        paymentForm.addEventListener('submit', function (e) {
            var method = methodSel ? methodSel.value : '';
            if (!method) {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }
            var btn = document.getElementById('payBtn');
            if (btn) {
                btn.disabled    = true;
                btn.textContent = 'Processing…';
            }
        });
    }

})();
