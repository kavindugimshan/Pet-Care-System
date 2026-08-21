// ============================================================
// Pet Care System - Booking JavaScript
// Member 3: Booking & Payment
// Vanilla JS only. Frontend validation & UX enhancements.
// Backend PHP validation is the authoritative source of truth.
// ============================================================

(function () {
    'use strict';

    // ── Set minimum appointment date to today ────────────────
    function setMinDate() {
        var dateInput = document.getElementById('appointment_date');
        if (!dateInput) return;
        var today = new Date();
        var yyyy  = today.getFullYear();
        var mm    = String(today.getMonth() + 1).padStart(2, '0');
        var dd    = String(today.getDate()).padStart(2, '0');
        dateInput.min = yyyy + '-' + mm + '-' + dd;
    }

    // ── Show inline error ────────────────────────────────────
    function showError(fieldId, message) {
        var errEl = document.getElementById('err_' + fieldId);
        var input = document.getElementById(fieldId);
        if (errEl) {
            errEl.textContent = message;
        }
        if (input) {
            input.closest('.form-group').classList.add('has-error');
            input.setAttribute('aria-invalid', 'true');
        }
    }

    // ── Clear inline error ───────────────────────────────────
    function clearError(fieldId) {
        var errEl = document.getElementById('err_' + fieldId);
        var input = document.getElementById(fieldId);
        if (errEl) { errEl.textContent = ''; }
        if (input) {
            input.closest('.form-group').classList.remove('has-error');
            input.removeAttribute('aria-invalid');
        }
    }

    // ── Validate a single field on blur ──────────────────────
    function attachBlurValidation() {
        var rules = {
            customer_name: function (v) {
                if (!v.trim()) return 'Full name is required.';
                if (v.trim().length > 100) return 'Name is too long (max 100 characters).';
                return '';
            },
            customer_email: function (v) {
                if (!v.trim()) return 'Email address is required.';
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim())) return 'Please enter a valid email address.';
                return '';
            },
            customer_phone: function (v) {
                if (!v.trim()) return 'Phone number is required.';
                if (!/^[0-9+\-\s()]{7,20}$/.test(v.trim())) return 'Please enter a valid phone number.';
                return '';
            },
            pet_name: function (v) {
                if (!v.trim()) return 'Pet name is required.';
                return '';
            },
            breed: function (v) {
                if (!v.trim()) return 'Breed is required.';
                return '';
            },
            age: function (v) {
                if (v === '') return 'Age is required.';
                var n = parseFloat(v);
                if (isNaN(n) || n < 0 || n > 30) return 'Age must be between 0 and 30.';
                return '';
            },
            appointment_date: function (v) {
                if (!v) return 'Appointment date is required.';
                var chosen = new Date(v + 'T00:00:00');
                var today  = new Date();
                today.setHours(0, 0, 0, 0);
                if (chosen < today) return 'Appointment date cannot be in the past.';
                return '';
            }
        };

        Object.keys(rules).forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('blur', function () {
                var msg = rules[id](el.value);
                if (msg) { showError(id, msg); } else { clearError(id); }
            });
            el.addEventListener('input', function () {
                clearError(id);
            });
        });
    }

    // ── Booking form submit validation ───────────────────────
    function attachBookingFormValidation() {
        var form = document.getElementById('bookingForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            var fields = [
                'customer_name', 'customer_email', 'customer_phone',
                'pet_name', 'breed', 'age', 'appointment_date'
            ];
            var hasError = false;

            fields.forEach(function (id) {
                var el = document.getElementById(id);
                if (!el) return;
                // Trigger blur validation
                el.dispatchEvent(new Event('blur'));
                if (document.getElementById('err_' + id) &&
                    document.getElementById('err_' + id).textContent) {
                    hasError = true;
                }
            });

            if (hasError) {
                e.preventDefault();
                // Scroll to first error
                var firstError = form.querySelector('.has-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }

            // Disable submit button to prevent double submission
            var btn = document.getElementById('submitBtn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Processing...';
            }
        });
    }

    // ── Payment form: card number formatting ─────────────────
    function attachCardFormatting() {
        var cardInput = document.getElementById('card_number');
        if (!cardInput) return;
        cardInput.addEventListener('input', function () {
            var raw     = this.value.replace(/\D/g, '').substring(0, 16);
            var groups  = raw.match(/.{1,4}/g) || [];
            this.value  = groups.join(' ');
        });

        var expiryInput = document.getElementById('card_expiry');
        if (!expiryInput) return;
        expiryInput.addEventListener('input', function () {
            var raw = this.value.replace(/\D/g, '').substring(0, 4);
            if (raw.length >= 3) {
                this.value = raw.substring(0, 2) + '/' + raw.substring(2);
            } else {
                this.value = raw;
            }
        });
    }

    // ── Payment form submit ───────────────────────────────────
    function attachPaymentFormValidation() {
        var form = document.getElementById('paymentForm');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            var cardName   = (document.getElementById('card_name')   || {}).value  || '';
            var cardNumber = ((document.getElementById('card_number') || {}).value || '').replace(/\s/g, '');
            var cardExpiry = (document.getElementById('card_expiry') || {}).value  || '';
            var cardCvv    = (document.getElementById('card_cvv')   || {}).value   || '';

            var errors = false;

            if (!cardName.trim()) {
                showError('card_name', 'Name on card is required.');
                errors = true;
            } else { clearError('card_name'); }

            if (cardNumber.length < 13 || cardNumber.length > 19 || !/^\d+$/.test(cardNumber)) {
                showError('card_number', 'Please enter a valid card number (13-19 digits).');
                errors = true;
            } else { clearError('card_number'); }

            if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                showError('card_expiry', 'Enter expiry as MM/YY.');
                errors = true;
            } else { clearError('card_expiry'); }

            if (cardCvv.length < 3 || cardCvv.length > 4 || !/^\d+$/.test(cardCvv)) {
                showError('card_cvv', 'CVV must be 3 or 4 digits.');
                errors = true;
            } else { clearError('card_cvv'); }

            if (errors) {
                e.preventDefault();
                return;
            }

            // Disable pay button
            var btn = document.getElementById('payBtn');
            if (btn) {
                btn.disabled = true;
                btn.textContent = 'Processing Payment...';
            }
        });
    }

    // ── Init ─────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        setMinDate();
        attachBlurValidation();
        attachBookingFormValidation();
        attachCardFormatting();
        attachPaymentFormValidation();
    });

}());
