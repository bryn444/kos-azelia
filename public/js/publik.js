/**
 * NAMA FILE: public/js/publik.js
 * FUNGSI: Mengatur interaksi slider hero, scroll horizontal kamar, dan AJAX Tanya Asisten.
 * HALAMAN: Beranda publik & Tanya Asisten.
 * CARA MENGUBAH: Ubah variabel durasi autoplay di bawah ini.
 */

// ==========================================
// UBAH DI SINI: PENGATURAN SLIDER & ASISTEN
// ==========================================
const DURASI_AUTOPLAY_MS = 6000; // Durasi perpindahan slide hero (6 detik)

document.addEventListener('DOMContentLoaded', function () {
    
    // ===== BAGIAN: SLIDER HERO UNGGULAN =====
    const slides = document.querySelectorAll('.hero-slide');
    const indikators = document.querySelectorAll('.hero-indikator');
    const btnPrev = document.getElementById('heroPrev');
    const btnNext = document.getElementById('heroNext');
    let currentIndex = 0;
    let autoplayInterval;

    function tampilkanSlide(index) {
        if (slides.length === 0) return;
        slides.forEach((slide, i) => {
            slide.classList.toggle('d-none', i !== index);
            if (indikators[i]) {
                indikators[i].classList.toggle('active', i === index);
                indikators[i].setAttribute('aria-selected', i === index);
            }
        });
        currentIndex = index;
    }

    function slideBerikutnya() {
        let next = (currentIndex + 1) % slides.length;
        tampilkanSlide(next);
    }

    function slideSebelumnya() {
        let prev = (currentIndex - 1 + slides.length) % slides.length;
        tampilkanSlide(prev);
    }

    if (slides.length > 1) {
        // Autoplay
        function mulaiAutoplay() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            autoplayInterval = setInterval(slideBerikutnya, DURASI_AUTOPLAY_MS);
        }

        function hentikanAutoplay() {
            clearInterval(autoplayInterval);
        }

        const heroContainer = document.getElementById('heroSliderContainer');
        if (heroContainer) {
            heroContainer.addEventListener('mouseenter', hentikanAutoplay);
            heroContainer.addEventListener('mouseleave', mulaiAutoplay);
            heroContainer.addEventListener('focusin', hentikanAutoplay);
            heroContainer.addEventListener('focusout', mulaiAutoplay);
        }

        if (btnNext) btnNext.addEventListener('click', () => { slideBerikutnya(); });
        if (btnPrev) btnPrev.addEventListener('click', () => { slideSebelumnya(); });

        indikators.forEach((ind, i) => {
            ind.addEventListener('click', () => { tampilkanSlide(i); });
        });

        mulaiAutoplay();
    }

    // ===== BAGIAN: SCROLL HORIZONTAL KAMAR LAINNYA =====
    const containerKamar = document.getElementById('containerKamarLainnya');
    const scrollKiri = document.getElementById('scrollKiri');
    const scrollKanan = document.getElementById('scrollKanan');

    if (containerKamar && scrollKiri && scrollKanan) {
        scrollKiri.addEventListener('click', () => {
            containerKamar.scrollBy({ left: -340, behavior: 'smooth' });
        });
        scrollKanan.addEventListener('click', () => {
            containerKamar.scrollBy({ left: 340, behavior: 'smooth' });
        });
    }

    // ===== BAGIAN: ASISTEN VIRTUAL (AJAX & TOMBOL CEPAT) =====
    const formTanya = document.getElementById('formTanyaAsisten');
    const inputPertanyaan = document.getElementById('inputPertanyaan');
    const kotakJawaban = document.getElementById('kotakJawabanAsisten');
    const teksJawaban = document.getElementById('teksJawabanAsisten');
    const btnCepat = document.querySelectorAll('.btn-pertanyaan-cepat');

    if (btnCepat && inputPertanyaan) {
        btnCepat.forEach(btn => {
            btn.addEventListener('click', function () {
                inputPertanyaan.value = this.getAttribute('data-query');
                inputPertanyaan.focus();
            });
        });
    }

    if (formTanya) {
        formTanya.addEventListener('submit', function (e) {
            e.preventDefault();
            const query = inputPertanyaan.value.trim();

            if (!query || query.length > 200) {
                alert('Pertanyaan tidak boleh kosong atau lebih dari 200 karakter.');
                return;
            }

            const formData = new FormData();
            formData.append('pertanyaan', query);
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            fetch(formTanya.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => {
                if (res.status === 429) {
                    throw new Error('Terlalu banyak pertanyaan, coba lagi sebentar.');
                }
                if (!res.ok) {
                    throw new Error('Terjadi kesalahan pada server.');
                }
                return res.json();
            })
            .then(data => {
                teksJawaban.textContent = data.jawaban;
                kotakJawaban.classList.remove('d-none');
                kotakJawaban.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(err => {
                alert(err.message || 'Terlalu banyak pertanyaan, coba lagi sebentar.');
            });
        });
    }
});