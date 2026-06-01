// Confetti Effect (Custom implementation - keeping your version)
function startConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particles = [];
    const colors = ['#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3', '#03a9f4', '#00bcd4', '#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722'];

    class ConfettiParticle {
        constructor(x, y) {
            this.x = x;
            this.y = y;
            this.size = Math.random() * 10 + 5;
            this.color = colors[Math.floor(Math.random() * colors.length)];
            this.speedX = Math.random() * 6 - 3;
            this.speedY = Math.random() * -15 - 5; // Start moving upwards
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
        const numParticles = 100;
        // Start confetti from the top middle of the screen
        for (let i = 0; i < numParticles; i++) {
            // Randomize starting X slightly, start from very top
            particles.push(new ConfettiParticle(canvas.width / 2 + (Math.random() - 0.5) * 200, -10));
        }
    }

    function animateConfetti() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (let i = particles.length - 1; i >= 0; i--) {
            particles[i].update();
            particles[i].draw();

            // Remove if faded or off-screen (below the bottom edge)
            if (particles[i].opacity <= 0 || particles[i].y > canvas.height + 20) {
                particles.splice(i, 1);
            }
        }

        // Only continue animating if there are particles left
        if (particles.length > 0) {
            requestAnimationFrame(animateConfetti);
        } else {
            canvas.style.display = 'none'; // Hide canvas when confetti is done
        }
    }

    // Make sure canvas is visible before starting
    canvas.style.display = 'block';
    createConfetti();
    animateConfetti();

    // Handle window resize
    window.addEventListener('resize', () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    });
}

// Function to save check history via AJAX (keeping your version)
async function saveCheckHistory(nomorPeserta, studentName, checkResult) {
    const formData = new FormData();
    formData.append('nomorPeserta', nomorPeserta);
    formData.append('studentName', studentName);
    formData.append('checkResult', checkResult);

    try {
        const response = await fetch('save_check_history.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            console.log("Riwayat pengecekan berhasil disimpan.");
            // Karena save_check_history.php Anda tidak mengembalikan daftar history,
            // kita tidak bisa memperbarui daftar secara dinamis dari sini.
            // Anda mungkin perlu memuat ulang bagian riwayat secara terpisah
            // atau memodifikasi save_check_history.php untuk mengembalikan data terbaru.
            // Untuk saat ini, kita hanya log ke konsol.
        } else {
            console.error("Gagal menyimpan riwayat pengecekan:", result.message);
        }
    } catch (error) {
        console.error('Error saat mengirim riwayat pengecekan:', error);
    }
}

// Helper function to escape HTML for dynamic content (prevent XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

// Fungsi untuk memperbarui tampilan testimoni (keeping your version)
const updateTestimonialDisplay = (testimonials) => {
    const testimonialList = document.getElementById('testimonial-list-public-live');
    if (!testimonialList) return;

    testimonialList.innerHTML = ''; // Kosongkan daftar yang ada

    if (testimonials.length === 0) {
        testimonialList.innerHTML = '<p id="no-testimonials-msg" style="text-align: center; color: #777;">Belum ada pesan dan kesan dari siswa lain.</p>';
        return;
    }

    testimonials.forEach(t => {
        const li = document.createElement('li');
        li.classList.add('testimonial-list-public-item');
        // Format tanggal dan waktu ke format lokal Indonesia
        const formattedDate = new Date(t.date).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        li.innerHTML = `
            <span class="date">${formattedDate}</span>
            <p>"${escapeHtml(t.message)}"</p>
            <span class="author">- ${escapeHtml(t.name)}</span>
        `;
        testimonialList.appendChild(li);
    });
};


// **********************************************
// Kode untuk fitur hitung mundur (Ditambahkan/Disesuaikan)
// **********************************************
const setupCountdown = () => {
    // Pastikan variabel global targetDate dan isPageReady tersedia dari PHP
    // Variabel ini didefinisikan di <script> di dalam index.php sebelum script.js dimuat
    if (typeof targetDate === 'undefined' || typeof isPageReady === 'undefined') {
        console.error("Variabel targetDate atau isPageReady tidak ditemukan. Pastikan variabel PHP disematkan dengan benar.");
        return;
    }

    const countdownTarget = new Date(targetDate).getTime(); // Waktu target dalam milidetik
    const countdownSection = document.getElementById('countdown-section');
    const inputSection = document.getElementById('input-section');

    const updateCountdown = () => {
        const now = new Date().getTime(); // Waktu saat ini dalam milidetik
        const distance = countdownTarget - now; // Selisih waktu

        // Hitung hari, jam, menit, dan detik
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        if (distance < 0) {
            // Jika hitung mundur selesai
            clearInterval(countdownInterval); // Hentikan interval
            if (countdownSection) {
                countdownSection.style.display = 'none'; // Sembunyikan bagian hitung mundur
            }
            if (inputSection) {
                inputSection.style.display = 'block'; // Tampilkan bagian input form
            }
            // Muat ulang halaman setelah hitung mundur selesai untuk memastikan PHP mengambil kondisi terbaru
            // Ini penting agar server-side PHP mengenali bahwa waktu sudah tiba
            window.location.reload(true);
        } else {
            // Perbarui tampilan hitung mundur
            if (document.getElementById('days')) {
                document.getElementById('days').innerText = String(days).padStart(2, '0');
                document.getElementById('hours').innerText = String(hours).padStart(2, '0');
                document.getElementById('minutes').innerText = String(minutes).padStart(2, '0');
                document.getElementById('seconds').innerText = String(seconds).padStart(2, '0');
            }
        }
    };

    // Panggil updateCountdown segera setelah dimuat untuk mencegah flicker
    updateCountdown();

    // Perbarui hitung mundur setiap 1 detik
    const countdownInterval = setInterval(updateCountdown, 1000);

    // Logika tampilan awal berdasarkan status isPageReady dari PHP
    if (isPageReady) {
        if (countdownSection) countdownSection.style.display = 'none';
        if (inputSection) inputSection.style.display = 'block';
    } else {
        if (countdownSection) countdownSection.style.display = 'block';
        if (inputSection) inputSection.style.display = 'none';
    }
};

// Pastikan semua script dijalankan setelah DOM siap
document.addEventListener('DOMContentLoaded', () => {
    // Panggil setupCountdown untuk menginisialisasi hitung mundur
    setupCountdown();

    // --- Logika untuk Form Testimoni Siswa (Ajax) --- (keeping your version)
    const testimonialForm = document.getElementById('student-testimonial-form');
    const studentNameForTestimonial = document.getElementById('studentNameForTestimonial');
    const testimonialMessage = document.getElementById('testimonialMessage');
    const testimonialResponseDiv = document.getElementById('testimonial-response');
    const submitTestimonialBtn = testimonialForm ? testimonialForm.querySelector('.btn-submit-testimonial') : null;
    const testimonialListPublicLive = document.getElementById('testimonial-list-public-live');
    const noTestimonialsMsg = document.getElementById('no-testimonials-msg');

    if (testimonialForm && submitTestimonialBtn) {
        testimonialForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const name = studentNameForTestimonial.value.trim();
            const message = testimonialMessage.value.trim();

            if (!name) {
                testimonialResponseDiv.textContent = 'Nama siswa tidak ditemukan. Mohon refresh halaman.';
                testimonialResponseDiv.style.color = 'red';
                return;
            }
            if (!message) {
                testimonialResponseDiv.textContent = 'Pesan dan kesan tidak boleh kosong.';
                testimonialResponseDiv.style.color = 'red';
                return;
            }

            submitTestimonialBtn.disabled = true;
            submitTestimonialBtn.textContent = 'Mengirim...';
            testimonialResponseDiv.textContent = '';
            testimonialResponseDiv.style.color = '';

            const formData = new FormData(testimonialForm);

            try {
                const response = await fetch('save_testimonial.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    testimonialResponseDiv.style.color = 'var(--success-color)';
                    testimonialResponseDiv.textContent = result.message;
                    testimonialMessage.value = '';

                    const newTestimonialItem = document.createElement('li');
                    newTestimonialItem.classList.add('testimonial-list-public-item');

                    const now = new Date();
                    const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
                    const formattedDate = now.toLocaleString('id-ID', options);

                    newTestimonialItem.innerHTML = `
                        <span class="date">${formattedDate}</span>
                        <p>"${escapeHtml(message)}"</p>
                        <span class="author">- ${escapeHtml(name)}</span>
                    `;

                    if (noTestimonialsMsg) {
                        noTestimonialsMsg.style.display = 'none';
                    }

                    if (testimonialListPublicLive) {
                        testimonialListPublicLive.prepend(newTestimonialItem);

                        while (testimonialListPublicLive.children.length > 5) {
                            testimonialListPublicLive.removeChild(testimonialListPublicLive.lastChild);
                        }
                    }

                } else {
                    testimonialResponseDiv.style.color = 'var(--danger-color)';
                    testimonialResponseDiv.textContent = result.message || 'Terjadi kesalahan saat mengirim testimoni.';
                }
            } catch (error) {
                console.error('Error submitting testimonial:', error);
                testimonialResponseDiv.style.color = 'var(--danger-color)';
                testimonialResponseDiv.textContent = 'Terjadi kesalahan jaringan atau server.';
            } finally {
                submitTestimonialBtn.disabled = false;
                submitTestimonialBtn.textContent = 'Kirim Kesan & Pesan';
            }
        });
    }
});
// script.js additions

/**
 * Mengirim permintaan AJAX untuk menambahkan suka pada item.
 * @param {string} itemId ID unik dari pesan/testimoni.
 * @param {string} itemType Tipe item ('teacher_message' atau 'testimonial').
 * @param {HTMLElement} buttonElement Elemen tombol "Suka" yang diklik.
 */
async function likeItem(itemId, itemType, buttonElement) {
    // Cek apakah user sudah pernah menyukai item ini (bisa disimpan di localStorage/sessionStorage)
    const likedItems = JSON.parse(localStorage.getItem('likedItems') || '{}');
    if (likedItems[itemId]) {
        alert('Anda sudah menyukai ini!');
        return;
    }

    try {
        const response = await fetch('like_item.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `itemId=${encodeURIComponent(itemId)}&itemType=${encodeURIComponent(itemType)}`
        });
        const data = await response.json();

        if (data.success) {
            const likeCountSpan = buttonElement.querySelector('.like-count');
            if (likeCountSpan) {
                likeCountSpan.textContent = data.newLikesCount;
            }
            buttonElement.classList.add('liked'); // Tambahkan kelas untuk menandai sudah disukai
            buttonElement.disabled = true; // Nonaktifkan tombol setelah disukai

            // Simpan status "sudah disukai" di localStorage
            likedItems[itemId] = true;
            localStorage.setItem('likedItems', JSON.stringify(likedItems));
        } else {
            alert('Gagal menyukai: ' + data.message);
        }
    } catch (error) {
        console.error('Error liking item:', error);
        alert('Terjadi kesalahan saat menyukai.');
    }
}

/**
 * Mengirim permintaan AJAX untuk menambahkan komentar pada item.
 * @param {Event} event Objek event dari submit form.
 * @param {string} itemId ID unik dari pesan/testimoni.
 * @param {string} itemType Tipe item ('teacher_message' atau 'testimonial').
 */
async function addComment(event, itemId, itemType) {
    event.preventDefault(); // Mencegah form submit default

    const form = event.target;
    const authorInput = form.querySelector('.comment-author-input');
    const messageTextarea = form.querySelector('textarea');
    const commentAuthor = authorInput.value.trim();
    const commentMessage = messageTextarea.value.trim();

    if (!commentMessage) {
        alert('Komentar tidak boleh kosong.');
        return;
    }

    try {
        const response = await fetch('add_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                itemId: itemId,
                itemType: itemType,
                commentAuthor: commentAuthor,
                commentMessage: commentMessage,
            }),
        });
        const data = await response.json();

        if (data.success) {
            const commentsList = document.querySelector(`#comments-section-${itemId} .comments-list`);
            const noCommentsMsg = document.querySelector(`#comments-section-${itemId} .no-comments-msg`);

            // Hapus pesan "Belum ada komentar" jika ada
            if (noCommentsMsg) {
                noCommentsMsg.remove();
            }

            // Tambahkan komentar baru ke daftar
            const newCommentHtml = `
                <li class="comment-item">
                    <span class="comment-author">${data.newComment.author}</span>
                    <span class="comment-date">${formatDate(new Date(data.newComment.date))}</span>
                    <p class="comment-text">${data.newComment.comment}</p>
                </li>
            `;
            commentsList.insertAdjacentHTML('beforeend', newCommentHtml); // Tambahkan di akhir

            messageTextarea.value = ''; // Kosongkan textarea
            authorInput.value = ''; // Kosongkan input nama
        } else {
            alert('Gagal menambahkan komentar: ' + data.message);
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        alert('Terjadi kesalahan saat menambahkan komentar.');
    }
}

/**
 * Mengaktifkan/menonaktifkan tampilan bagian komentar.
 * @param {string} itemId ID unik dari pesan/testimoni.
 */
function toggleComments(itemId) {
    const commentsSection = document.getElementById(`comments-section-${itemId}`);
    if (commentsSection) {
        if (commentsSection.style.display === 'none' || commentsSection.style.display === '') {
            commentsSection.style.display = 'block';
        } else {
            commentsSection.style.display = 'none';
        }
    }
}

/**
 * Fungsi pembantu untuk memformat tanggal (opsional, jika Anda ingin format yang berbeda)
 * @param {Date} dateObj Objek tanggal.
 * @returns {string} Tanggal yang diformat.
 */
function formatDate(dateObj) {
    const options = { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' };
    return dateObj.toLocaleDateString('id-ID', options);
}

// Cek status suka saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    const likedItems = JSON.parse(localStorage.getItem('likedItems') || '{}');
    document.querySelectorAll('.like-button').forEach(button => {
        const itemId = button.dataset.id;
        if (itemId && likedItems[itemId]) {
            button.classList.add('liked');
            button.disabled = true;
        }
    });

    document.body.addEventListener('click', function (event) {
        // LIKE BUTTON
        const likeBtn = event.target.closest('.like-button');
        if (likeBtn && !likeBtn.disabled) {
            const itemId = likeBtn.dataset.id;
            const itemType = likeBtn.dataset.type;
            if (itemId && itemType) {
                likeItem(itemId, itemType, likeBtn);
            }
        }
        // COMMENT BUTTON
        const commentBtn = event.target.closest('.comment-toggle-button');
        if (commentBtn) {
            const itemId = commentBtn.dataset.id;
            if (itemId) {
                toggleComments(itemId);
            }
        }
    });
});