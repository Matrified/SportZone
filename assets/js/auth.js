/* register + login validation (Hadi) - server side still does the real checks */

document.addEventListener('DOMContentLoaded', function () {

    // ---------- Show / hide password ----------
    const toggle = document.getElementById('togglePassword');
    if (toggle) {
        toggle.addEventListener('click', function () {
            const pwd = document.getElementById('password');
            pwd.type = pwd.type === 'password' ? 'text' : 'password';
        });
    }

    // ---------- Helpers ----------
    function group(input) { return input.closest('.form-group'); }
    function fail(input, msg) {
        const g = group(input);
        g.classList.add('invalid');
        g.classList.remove('valid');
        const e = g.querySelector('.error-text');
        if (e) e.textContent = msg;
    }
    function pass(input) {
        const g = group(input);
        g.classList.remove('invalid');
        g.classList.add('valid');
    }

    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function validateName(i)  { if (i.value.trim().length < 3) { fail(i, 'Full name must be at least 3 characters.'); return false; } pass(i); return true; }
    function validateEmail(i) { if (!emailRe.test(i.value.trim())) { fail(i, 'Please enter a valid email address.'); return false; } pass(i); return true; }
    function validatePwd(i)   { if (i.value.length < 6) { fail(i, 'Password must be at least 6 characters.'); return false; } pass(i); return true; }

    // ---------- Register form ----------
    const reg = document.getElementById('registerForm');
    if (reg) {
        const name    = reg.querySelector('#full_name');
        const email   = reg.querySelector('#email');
        const pwd     = reg.querySelector('#password');
        const confirm = reg.querySelector('#confirm_password');
        const terms   = reg.querySelector('#terms');

        name.addEventListener('blur',   () => validateName(name));
        email.addEventListener('blur',  () => validateEmail(email));
        pwd.addEventListener('blur',    () => validatePwd(pwd));
        confirm.addEventListener('input', function () {
            if (confirm.value !== pwd.value) { fail(confirm, 'Passwords do not match.'); }
            else { pass(confirm); }
        });

        reg.addEventListener('submit', function (e) {
            let ok = true;
            if (!validateName(name)) ok = false;
            if (!validateEmail(email)) ok = false;
            if (!validatePwd(pwd)) ok = false;
            if (confirm.value !== pwd.value) { fail(confirm, 'Passwords do not match.'); ok = false; }
            if (!terms.checked) {
                group(terms).classList.add('invalid');
                ok = false;
            }
            if (!ok) e.preventDefault();
        });
    }

    // ---------- Login form ----------
    const login = document.getElementById('loginForm');
    if (login) {
        const email = login.querySelector('#email');
        const pwd   = login.querySelector('#password');
        login.addEventListener('submit', function (e) {
            let ok = true;
            if (!emailRe.test(email.value.trim())) { fail(email, 'Please enter a valid email address.'); ok = false; }
            if (pwd.value.length === 0) { fail(pwd, 'Password is required.'); ok = false; }
            if (!ok) e.preventDefault();
        });
    }
});
