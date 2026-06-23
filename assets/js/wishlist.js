/* wishlist hearts - toggle via ajax, update the nav count */
document.addEventListener('DOMContentLoaded', function () {

    const meta = document.querySelector('meta[name="csrf-token"]');
    const token = meta ? meta.content : '';
    const base = (window.SZ_BASE || '/SportZone/');

    document.querySelectorAll('.js-wish').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const body = new URLSearchParams();
            body.append('product_id', btn.dataset.id);
            body.append('csrf_token', token);

            fetch(base + 'toggle_wishlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(r => r.json())
            .then(function (data) {
                if (data.login) { window.location.href = base + 'login.php'; return; }
                if (!data.ok) return;

                const heart = btn.querySelector('.wl-heart');   // line button (product page)
                const text  = btn.querySelector('.wl-text');
                const added = data.state === 'added';

                btn.classList.toggle('active', added);

                if (heart) {
                    heart.textContent = added ? '♥' : '♡';
                    if (text) text.textContent = added ? 'Saved to Wishlist' : 'Add to Wishlist';
                } else {
                    // small round heart on a product card
                    btn.textContent = added ? '♥' : '♡';
                    if (!added && document.body.dataset.page === 'wishlist') {
                        const card = btn.closest('.product-card');
                        if (card) card.remove();
                    }
                }

                const badge = document.getElementById('wishBadge');
                if (badge) {
                    badge.textContent = data.count;
                    badge.style.display = data.count > 0 ? '' : 'none';
                }
            })
            .catch(() => {});
        });
    });
});
