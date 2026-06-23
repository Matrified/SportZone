/* product page - tabs + star rating picker (Ahmed) */
document.addEventListener('DOMContentLoaded', function () {

    // ---------- Tabs ----------
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });

    // ---------- Interactive star rating ----------
    const starInput = document.getElementById('starInput');
    const ratingValue = document.getElementById('ratingValue');
    if (starInput && ratingValue) {
        const stars = starInput.querySelectorAll('span');

        function paint(val) {
            stars.forEach(function (s) {
                s.textContent = parseInt(s.dataset.value) <= val ? '★' : '☆';
            });
        }

        stars.forEach(function (star) {
            star.addEventListener('mouseenter', () => paint(parseInt(star.dataset.value)));
            star.addEventListener('click', function () {
                ratingValue.value = star.dataset.value;
                paint(parseInt(star.dataset.value));
            });
        });

        starInput.addEventListener('mouseleave', () => paint(parseInt(ratingValue.value)));
    }

    // ---------- Review form validation ----------
    const reviewForm = document.getElementById('reviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            if (parseInt(ratingValue.value) < 1) {
                e.preventDefault();
                alert('Please select a star rating before submitting.');
            }
        });
    }
});
