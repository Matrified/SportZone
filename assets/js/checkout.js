/* checkout - step navigation, validation and review summary (Osman) */
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('checkoutForm');
    if (!form) return;

    const steps      = document.querySelectorAll('.checkout-step');
    const indicators = document.querySelectorAll('.step-indicator .step');

    function showStep(n) {
        steps.forEach(s => s.classList.remove('active'));
        document.getElementById('step-' + n).classList.add('active');
        indicators.forEach(function (ind) {
            ind.classList.toggle('active', parseInt(ind.dataset.step) <= n);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function validateStep1() {
        const fields = ['full_name', 'phone', 'address', 'city', 'postal_code'];
        let ok = true;
        fields.forEach(function (id) {
            const input = document.getElementById(id);
            const group = input.closest('.form-group');
            let valid = input.value.trim() !== '';
            if (id === 'phone' && valid) {
                valid = /^[0-9+\-\s]{7,20}$/.test(input.value.trim());
            }
            group.classList.toggle('invalid', !valid);
            if (!valid) ok = false;
        });
        return ok;
    }

    // ---------- Next / Prev ----------
    document.querySelectorAll('.next-step').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const next = parseInt(btn.dataset.next);
            if (next === 2 && !validateStep1()) return;
            if (next === 3) buildReview();
            showStep(next);
        });
    });

    document.querySelectorAll('.prev-step').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showStep(parseInt(btn.dataset.prev));
        });
    });

    // ---------- Payment toggle ----------
    const cardFields = document.getElementById('cardFields');
    document.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            cardFields.style.display = this.value === 'card' ? 'block' : 'none';
        });
    });

    // ---------- Build review summary ----------
    function buildReview() {
        const v = id => (document.getElementById(id) || {}).value || '';
        const payment = document.querySelector('input[name="payment_method"]:checked').value;
        const html = `
            <div class="review-line"><strong>Name:</strong> ${escapeHtml(v('full_name'))}</div>
            <div class="review-line"><strong>Phone:</strong> ${escapeHtml(v('phone'))}</div>
            <div class="review-line"><strong>Address:</strong> ${escapeHtml(v('address'))}, ${escapeHtml(v('city'))} ${escapeHtml(v('postal_code'))}</div>
            <div class="review-line"><strong>Payment:</strong> ${payment === 'cod' ? 'Cash on Delivery' : 'Credit / Debit Card'}</div>
        `;
        document.getElementById('reviewSummary').innerHTML = html;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ---------- Promo code ----------
    const applyBtn = document.getElementById('applyPromo');
    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            const code = document.getElementById('promoInput').value.trim();
            const msg = document.getElementById('promoMsg');
            const token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            if (code === '') { msg.textContent = 'Please enter a code.'; msg.className = 'promo-msg error'; return; }

            const body = new URLSearchParams();
            body.append('code', code);
            body.append('csrf_token', token);

            fetch((window.SZ_BASE || '/SportZone/') + 'apply_promo.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(r => r.json())
            .then(function (data) {
                if (!data.ok) {
                    msg.textContent = data.msg;
                    msg.className = 'promo-msg error';
                    document.getElementById('promoCode').value = '';
                    document.getElementById('discountRow').style.display = 'none';
                    return;
                }
                msg.textContent = data.msg;
                msg.className = 'promo-msg success';
                document.getElementById('promoCode').value = data.code;
                document.getElementById('discountVal').textContent = '- RM ' + data.discount;
                document.getElementById('discountRow').style.display = 'flex';
                document.getElementById('summaryTotal').textContent = 'RM ' + data.total;
            })
            .catch(() => { msg.textContent = 'Could not apply code.'; msg.className = 'promo-msg error'; });
        });
    }

    // ---------- Final submit guard + loading state ----------
    form.addEventListener('submit', function (e) {
        if (!validateStep1()) {
            e.preventDefault();
            showStep(1);
            return;
        }
        const btn = document.getElementById('placeOrderBtn');
        btn.textContent = 'Placing Order...';
        btn.disabled = true;
    });
});
