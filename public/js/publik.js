/**
 * NAMA FILE: public/js/publik.js
 * FUNGSI: Mengatur slider foto kamar unggulan di hero, scroll horizontal kamar, dan AJAX Tanya Asisten.
 * HALAMAN: Beranda publik & Tanya Asisten.
 * CARA MENGUBAH: Ubah nilai pengaturan slider pada blok "UBAH DI SINI" di bawah ini.
 */

// ==========================================
// UBAH DI SINI: PENGATURAN SLIDER HERO FOTO
// ==========================================
const INTERVAL_OTOMATIS_MS = 2000;      // Kecepatan ganti foto otomatis (2 detik = 2000 ms)
const JEDA_SETELAH_MANUAL_MS = 6000;    // Jeda otomatis setelah klik panah/titik atau swipe (6 detik = 6000 ms)
const DURASI_EFEK_PUDAR_MS = 400;       // Durasi efek crossfade foto (0,4 detik = 400 ms)
const MAKS_FOTO_HERO = 6;               // Jumlah foto maksimal yang diproses di slider hero

document.addEventListener('DOMContentLoaded', function () {

    // ===== BAGIAN: SLIDER FOTO KAMAR UNGGULAN (TAHAP 7) =====
    // DITAMBAHKAN TAHAP 7: Mengganti slider antar kamar menjadi slider foto milik satu kamar unggulan
    const wadahSliderHero = document.getElementById('heroFotoSlider');
    if (wadahSliderHero) {
        const semuaFoto = Array.from(wadahSliderHero.querySelectorAll('.hero-foto-slide')).slice(0, MAKS_FOTO_HERO);
        const btnPrev = document.getElementById('heroFotoPrev');
        const btnNext = document.getElementById('heroFotoNext');
        const penandaTeks = document.getElementById('heroFotoPenanda');
        const tombolTitik = Array.from(wadahSliderHero.querySelectorAll('.hero-foto-titik-btn')).slice(0, MAKS_FOTO_HERO);

        let indeksAktif = 0;
        let timerOtomatis = null;
        let timerJedaManual = null;
        let kursorDiAtas = false;
        let fokusDiDalam = false;
        let tabTerlihat = !document.hidden;
        let sentuhAktif = false;
        let posisiSentuhMulaiX = 0;
        let posisiSentuhMulaiY = 0;

        wadahSliderHero.style.setProperty('--hero-durasi-pudars', (DURASI_EFEK_PUDAR_MS / 1000) + 's');

        /**
         * Memformat angka menjadi dua digit (misal 1 -> "01").
         * Alasan: Penanda posisi foto ditampilkan dalam format "01 / 04".
         */
        function formatDuaDigit(angka) {
            return String(angka).padStart(2, '0');
        }

        /**
         * Memperbarui teks penanda posisi ("01 / 04") dan status tombol titik aktif.
         * Alasan: Pengguna selalu melihat nomor foto yang sedang tampil dan total foto kamar.
         */
        function perbaruiPenandaDanTitik() {
            if (penandaTeks) {
                penandaTeks.textContent = formatDuaDigit(indeksAktif + 1) + ' / ' + formatDuaDigit(semuaFoto.length);
            }
            tombolTitik.forEach(function (titik, idx) {
                const aktif = idx === indeksAktif;
                titik.classList.toggle('aktif', aktif);
                titik.setAttribute('aria-pressed', aktif ? 'true' : 'false');
            });
        }

        /**
         * Memastikan foto target sudah mulai dimuat sebelum ditampilkan.
         * Alasan: Foto kedua dan seterusnya memakai loading="lazy" agar hemat muatan awal.
         */
        function siapkanFoto(fotoEl) {
            if (fotoEl && fotoEl.getAttribute('loading') === 'lazy') {
                fotoEl.setAttribute('loading', 'eager');
            }
        }

        /**
         * Menampilkan foto pada indeks tertentu dengan efek crossfade.
         * Alasan: Memindahkan tampilan foto dengan mulus tanpa mengubah tinggi area foto.
         */
        function tampilkanFoto(targetIndex) {
            if (semuaFoto.length === 0) return;
            const total = semuaFoto.length;
            const indeksBaru = ((targetIndex % total) + total) % total;

            siapkanFoto(semuaFoto[indeksBaru]);
            siapkanFoto(semuaFoto[(indeksBaru + 1) % total]);

            semuaFoto.forEach(function (fotoEl, idx) {
                fotoEl.classList.toggle('aktif', idx === indeksBaru);
            });

            indeksAktif = indeksBaru;
            perbaruiPenandaDanTitik();
        }

        /**
         * Menghentikan interval gerak otomatis yang sedang berjalan.
         * Alasan: Mencegah perpindahan foto saat kursor/fokus aktif, tab tersembunyi, atau saat jeda manual.
         */
        function hentikanOtomatis() {
            if (timerOtomatis !== null) {
                clearInterval(timerOtomatis);
                timerOtomatis = null;
            }
        }

        /**
         * Mengecek apakah gerak otomatis diizinkan berjalan saat ini.
         * Alasan: Mematuhi prefers-reduced-motion, kondisi hover/fokus, visibilitas tab, dan jeda manual.
         */
        function bolehJalanOtomatis() {
            if (semuaFoto.length <= 1) return false;
            if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return false;
            if (kursorDiAtas || fokusDiDalam || !tabTerlihat) return false;
            if (timerJedaManual !== null) return false;
            return true;
        }

        /**
         * Menjalankan interval pergantian foto otomatis jika syarat terpenuhi.
         * Alasan: Menggeser foto kamar secara berkala sesuai INTERVAL_OTOMATIS_MS.
         */
        function mulaiOtomatis() {
            hentikanOtomatis();
            if (!bolehJalanOtomatis()) return;

            timerOtomatis = setInterval(function () {
                tampilkanFoto(indeksAktif + 1);
            }, INTERVAL_OTOMATIS_MS);
        }

        /**
         * Menjeda gerak otomatis selama JEDA_SETELAH_MANUAL_MS setelah interaksi manual.
         * Alasan: Memberi waktu bagi pengguna melihat foto yang dipilih lewat panah, titik, atau swipe.
         */
        function jedaSetelahInteraksiManual() {
            hentikanOtomatis();
            if (timerJedaManual !== null) {
                clearTimeout(timerJedaManual);
            }
            timerJedaManual = setTimeout(function () {
                timerJedaManual = null;
                mulaiOtomatis();
            }, JEDA_SETELAH_MANUAL_MS);
        }

        /**
         * Menangani aksi navigasi manual ke foto sebelumnya atau berikutnya.
         * Alasan: Menyatukan logika tombol panah, titik, keyboard, dan swipe.
         */
        function pindahManual(keIndex, jedaOtomatis) {
            tampilkanFoto(keIndex);
            if (jedaOtomatis) {
                jedaSetelahInteraksiManual();
            }
        }

        // Jika hanya ada 1 foto: tanpa gerak otomatis, panah, dan penanda
        if (semuaFoto.length > 1) {
            perbaruiPenandaDanTitik();

            if (btnPrev) {
                btnPrev.addEventListener('click', function () {
                    pindahManual(indeksAktif - 1, true);
                });
            }

            if (btnNext) {
                btnNext.addEventListener('click', function () {
                    pindahManual(indeksAktif + 1, true);
                });
            }

            tombolTitik.forEach(function (titik, idx) {
                titik.addEventListener('click', function () {
                    pindahManual(idx, true);
                });
            });

            // Navigasi keyboard saat slider atau elemen di dalamnya difokuskan
            wadahSliderHero.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    pindahManual(indeksAktif - 1, true);
                } else if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    pindahManual(indeksAktif + 1, true);
                }
            });

            // Berhenti otomatis saat kursor di atas foto atau fokus ada di slider
            wadahSliderHero.addEventListener('mouseenter', function () {
                kursorDiAtas = true;
                hentikanOtomatis();
            });

            wadahSliderHero.addEventListener('mouseleave', function () {
                kursorDiAtas = false;
                mulaiOtomatis();
            });

            wadahSliderHero.addEventListener('focusin', function () {
                fokusDiDalam = true;
                hentikanOtomatis();
            });

            wadahSliderHero.addEventListener('focusout', function (event) {
                if (!wadahSliderHero.contains(event.relatedTarget)) {
                    fokusDiDalam = false;
                    mulaiOtomatis();
                }
            });

            // Berhenti otomatis saat tab browser tidak terlihat
            document.addEventListener('visibilitychange', function () {
                tabTerlihat = !document.hidden;
                if (!tabTerlihat) {
                    hentikanOtomatis();
                } else {
                    mulaiOtomatis();
                }
            });

            // Dukungan swipe kiri & kanan di layar sentuh memakai Pointer Events
            wadahSliderHero.addEventListener('pointerdown', function (event) {
                if (event.pointerType === 'mouse' && event.button !== 0) return;
                sentuhAktif = true;
                posisiSentuhMulaiX = event.clientX;
                posisiSentuhMulaiY = event.clientY;
            });

            wadahSliderHero.addEventListener('pointerup', function (event) {
                if (!sentuhAktif) return;
                sentuhAktif = false;

                const selisihX = event.clientX - posisiSentuhMulaiX;
                const selisihY = event.clientY - posisiSentuhMulaiY;
                const ambangSwipe = 40;

                if (Math.abs(selisihX) > ambangSwipe && Math.abs(selisihX) > Math.abs(selisihY)) {
                    if (selisihX < 0) {
                        pindahManual(indeksAktif + 1, true);
                    } else {
                        pindahManual(indeksAktif - 1, true);
                    }
                }
            });

            wadahSliderHero.addEventListener('pointercancel', function () {
                sentuhAktif = false;
            });

            mulaiOtomatis();
        }
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