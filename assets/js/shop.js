/* product listing - filter panel toggle on mobile (Ahmed) */
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('filterToggle');
    const panel  = document.getElementById('filterPanel');
    if (toggle && panel) {
        toggle.addEventListener('click', function () {
            panel.classList.toggle('open');
        });
    }
});
