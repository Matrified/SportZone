/* =========================================================
   SPORTZONE - ADMIN PANEL JS
   Member 4 (Mohamed Tarek)
   - Sidebar toggle (mobile)
   - Product image upload live preview
   - Custom canvas sales chart (vanilla JS, no external libs)
   ========================================================= */
document.addEventListener('DOMContentLoaded', function () {

    // ---------- Sidebar toggle ----------
    const menuBtn = document.getElementById('adminMenuBtn');
    const sidebar = document.getElementById('adminSidebar');
    if (menuBtn && sidebar) {
        menuBtn.addEventListener('click', () => sidebar.classList.toggle('open'));
    }

    // ---------- Image preview ----------
    const imageInput = document.getElementById('image');
    const preview = document.getElementById('imagePreview');
    if (imageInput && preview) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                preview.src = URL.createObjectURL(file);
            }
        });
    }

    // ---------- Sales chart (custom canvas line chart) ----------
    const canvas = document.getElementById('salesChart');
    if (canvas && typeof CHART_VALUES !== 'undefined') {
        drawLineChart(canvas, CHART_LABELS, CHART_VALUES);
    }
});

function drawLineChart(canvas, labels, values) {
    const ctx = canvas.getContext('2d');
    // Handle high-DPI displays
    const ratio = window.devicePixelRatio || 1;
    const cssWidth = canvas.clientWidth;
    const cssHeight = canvas.height;
    canvas.width = cssWidth * ratio;
    canvas.height = cssHeight * ratio;
    ctx.scale(ratio, ratio);

    const W = cssWidth, H = cssHeight;
    const padL = 44, padB = 26, padT = 14, padR = 14;
    const plotW = W - padL - padR;
    const plotH = H - padT - padB;

    const maxVal = Math.max(...values, 10);
    const niceMax = Math.ceil(maxVal / 50) * 50 || 50;

    ctx.clearRect(0, 0, W, H);

    // Grid + Y labels
    ctx.strokeStyle = '#eee';
    ctx.fillStyle = '#999';
    ctx.font = '11px Open Sans, sans-serif';
    ctx.textAlign = 'right';
    const steps = 4;
    for (let i = 0; i <= steps; i++) {
        const y = padT + (plotH / steps) * i;
        const val = niceMax - (niceMax / steps) * i;
        ctx.beginPath();
        ctx.moveTo(padL, y);
        ctx.lineTo(W - padR, y);
        ctx.stroke();
        ctx.fillText('RM' + Math.round(val), padL - 6, y + 4);
    }

    // Points
    const stepX = plotW / Math.max(1, labels.length - 1);
    const points = values.map((v, i) => ({
        x: padL + stepX * i,
        y: padT + plotH - (v / niceMax) * plotH
    }));

    // Area fill
    const grad = ctx.createLinearGradient(0, padT, 0, padT + plotH);
    grad.addColorStop(0, 'rgba(230,57,70,0.25)');
    grad.addColorStop(1, 'rgba(230,57,70,0)');
    ctx.beginPath();
    ctx.moveTo(points[0].x, padT + plotH);
    points.forEach(p => ctx.lineTo(p.x, p.y));
    ctx.lineTo(points[points.length - 1].x, padT + plotH);
    ctx.closePath();
    ctx.fillStyle = grad;
    ctx.fill();

    // Line
    ctx.beginPath();
    points.forEach((p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y));
    ctx.strokeStyle = '#E63946';
    ctx.lineWidth = 2.5;
    ctx.stroke();

    // Dots + X labels
    ctx.fillStyle = '#E63946';
    ctx.textAlign = 'center';
    points.forEach((p, i) => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
        ctx.fill();
        ctx.fillStyle = '#999';
        ctx.fillText(labels[i], p.x, H - 8);
        ctx.fillStyle = '#E63946';
    });
}
