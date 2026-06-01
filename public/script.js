// ==========================================
// CLIENT-SIDE SCRIPTS - MTsN 11 MAJALENGKA
// ==========================================

// 1. Confetti Effect (Animasi Perayaan Kelulusan)
function startConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particles = [];
    const colors = [
        '#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', 
        '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4CAF50', 
        '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722'
    ];

    class ConfettiParticle {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.size = Math.random() * 10 + 5;
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.speedX = Math.random() * 6 - 3;
            this.speedY = Math.random() * -15 - 5; // Bergerak ke atas dulu
            this.gravity = 0.5;
            this.rotation = Math.random() * 360;
            this.rotationSpeed = Math.random() * 10 - 5;
            this.opacity = 1;
            this.fadeSpeed = Math.random() * 0.02 + 0.01;
        }

        update() {
            this.speedY += this.gravity;
            this.x += this.speedX;
            this.y += this.speedY;
            this.rotation += this.rotationSpeed;
            this.opacity -= this.fadeSpeed;
        }

        draw() {
            ctx.save();
            ctx.translate(this.x, this.y);
            ctx.rotate(this.rotation * Math.PI / 180);
            ctx.globalAlpha = Math.max(0, this.opacity);
            ctx.fillStyle = this.color;
            ctx.fillRect(-this.size / 2, -this.size / 2, this.size, this.size);
            ctx.restore();
        }
    }

    function createConfetti() {
        const numParticles = 120;
        for (let i = 0; i < numParticles; i++) {
            particles.push(new ConfettiParticle(canvas.width / 2 + (Math.random() - 0.5) * 200, -10));
        }
    }

    function animateConfetti() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (let i = particles.length - 1; i >= 0; i--) {
            particles[i].update();
            particles[i].draw();

            if (particles[i].opacity <= 0 || particles[i].y > canvas.height + 20) {
                particles.splice(i, 1);
            }
        }

        if (particles.length > 0) {
            requestAnimationFrame(animateConfetti);
        } else {
            canvas.style.display = 'none';
        }
    }

    canvas.style.display = 'block';
    createConfetti();
    animateConfetti();

    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

// 2. Helper untuk menghalangi XSS (Escape HTML)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// 3. Konfigurasi Fitur Hitung Mundur Rilis Kelulusan
const setupCountdown = () => {
    if (typeof targetDate === 'undefined' || typeof isPageReady === 'undefined') {
        console.error("Variabel targetDate atau isPageReady tidak didefinisikan.");
        return;
    }

    const countdownTarget = new Date(targetDate).getTime();
    const countdownSection = document.getElementById('countdown-section');
    const inputSection = document.getElementById('input-section');

    const updateCountdown = () => {
        const now = new Date().getTime();
        const distance = countdownTarget - now;

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (distance < 0) {
            clearInterval(countdownInterval);
            if (countdownSection) countdownSection.style.display = 'none';
            if (inputSection) inputSection.style.display = 'block';
            window.location.reload(true);
        } else {
            if (document.getElementById('days')) {
                document.getElementById('days').innerText = String(days).padStart(2, '0');
                document.getElementById('hours').innerText = String(hours).padStart(2, '0');
                document.getElementById('minutes').innerText = String(minutes).padStart(2, '0');
                document.getElementById('seconds').innerText = String(seconds).padStart(2, '0');
            }
        }
    };

    updateCountdown();
    const countdownInterval = setInterval(updateCountdown, 1000);

    if (isPageReady) {
        if (countdownSection) countdownSection.style.display = 'none';
        if (inputSection) inputSection.style.display = 'block';
    } else {
        if (countdownSection) countdownSection.style.display = 'block';
        if (inputSection) inputSection.style.display = 'none';
    }
};

// Inisialisasi hitung mundur ketika halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    setupCountdown();
});