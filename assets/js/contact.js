/* contact form - quick client-side validation before sending */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('contactForm');
    if (!form) return;

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    form.addEventListener('submit', function (e) {
        let ok = true;
        const name = form.querySelector('#name');
        const email = form.querySelector('#email');
        const message = form.querySelector('#message');

        function mark(input, good) {
            input.closest('.form-group').classList.toggle('invalid', !good);
            if (!good) ok = false;
        }

        mark(name, name.value.trim().length >= 2);
        mark(email, emailRe.test(email.value.trim()));
        mark(message, message.value.trim().length >= 10);

        if (!ok) e.preventDefault();
    });
});
