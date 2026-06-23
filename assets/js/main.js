/* shared js - runs on every page (mobile menu + small form helpers) */

document.addEventListener('DOMContentLoaded', function () {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const navLinks = document.getElementById('navLinks');

    if (hamburgerBtn && navLinks) {
        hamburgerBtn.addEventListener('click', function () {
            navLinks.classList.toggle('active');
        });
    }
});

/* Generic helper: show a field-level validation error */
function setFieldError(groupEl, message) {
    groupEl.classList.add('invalid');
    groupEl.classList.remove('valid');
    const errorEl = groupEl.querySelector('.error-text');
    if (errorEl) errorEl.textContent = message;
}

function setFieldValid(groupEl) {
    groupEl.classList.remove('invalid');
    groupEl.classList.add('valid');
}
