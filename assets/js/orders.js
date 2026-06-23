/* =========================================================
   SPORTZONE - ORDER HISTORY
   Member 3 (Osman) - order details modal popup.
   ========================================================= */
document.addEventListener('DOMContentLoaded', function () {
    const modal     = document.getElementById('orderModal');
    const modalBody = document.getElementById('modalBody');
    const modalTitle = document.getElementById('modalTitle');
    const closeBtn  = document.getElementById('modalClose');

    if (!modal) return;

    document.querySelectorAll('.view-order-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const o = JSON.parse(btn.dataset.order);

            let itemsHtml = '<table class="data-table" style="margin-top:10px;"><thead><tr><th>Item</th><th>Qty</th><th>Price</th></tr></thead><tbody>';
            o.items.forEach(function (it) {
                const sz = it.size ? ' (Size: ' + escapeHtml(it.size) + ')' : '';
                itemsHtml += `<tr><td>${escapeHtml(it.product_name)}${sz}</td><td>${it.quantity}</td><td>RM ${parseFloat(it.price).toFixed(2)}</td></tr>`;
            });
            itemsHtml += '</tbody></table>';

            modalTitle.textContent = 'Order #' + o.id;
            modalBody.innerHTML = `
                <div class="review-line"><strong>Date:</strong> ${escapeHtml(o.date)}</div>
                <div class="review-line"><strong>Status:</strong> <span class="status-badge status-${o.status.toLowerCase()}">${escapeHtml(o.status)}</span></div>
                <div class="review-line"><strong>Recipient:</strong> ${escapeHtml(o.name)} (${escapeHtml(o.phone)})</div>
                <div class="review-line"><strong>Shipping to:</strong> ${escapeHtml(o.address)}</div>
                <div class="review-line"><strong>Payment:</strong> ${escapeHtml(o.payment)}</div>
                ${itemsHtml}
                <div class="summary-row" style="margin-top:10px;"><span>Subtotal</span><span>RM ${o.subtotal}</span></div>
                <div class="summary-row"><span>Shipping</span><span>RM ${o.shipping}</span></div>
                <div class="summary-row total"><span>Total</span><span>RM ${o.total}</span></div>
            `;
            modal.classList.add('open');
        });
    });

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : str;
        return div.innerHTML;
    }

    closeBtn.addEventListener('click', () => modal.classList.remove('open'));
    modal.addEventListener('click', function (e) {
        if (e.target === modal) modal.classList.remove('open');
    });
});
