/**
 * ============================================================================
 * FILE: public/js/latar-editor.js
 * FUNGSI: Mengendalikan editor latar belakang ala Canva di panel pengelola:
 *         - Skala kanvas mengikuti lebar panggung & tinggi halaman publik
 *         - Pilih, geser, ubah ukuran, putar foto
 *         - Kontrol panel kanan (Atur & Lapisan) + Tabel Gambar Terupload
 *         - Hapus permanen ke server + Unggah + Apply + Terapkan
 * ============================================================================
 */

(function () {
    "use strict";

    const LEBAR_LOGIS_LAPTOP = 1280;
    const LEBAR_LOGIS_HP = 390;
    const LANGKAH_GESER_PANAH = 0.5;
    const LANGKAH_GESER_SHIFT = 2.5;
    const UKURAN_MIN_CQW = 3;
    const UKURAN_MAKS_CQW = 200;
    const UKURAN_AWAL_UNGGAH = 30;
    const BATAS_RIWAYAT = 50;
    const AMBANG_SNAP_TENGAH = 1.0;
    const AMBANG_SNAP_SUDUT = 3;

    const ORIGIN_ASAL = window.location.origin;

    const elDataAwal = document.getElementById("le-data-awal");
    if (!elDataAwal) return;
    const konfigurasi = JSON.parse(elDataAwal.textContent);

    function salinDalam(data) {
        return JSON.parse(JSON.stringify(data));
    }

    function bulatkan(angka) {
        return Math.round(angka * 100) / 100;
    }

    function normalisasiItem(raw) {
        const lap = raw.laptop || {
            jangkar: raw.jangkar || "atas",
            x: Number(raw.lap_x ?? raw.x ?? 50),
            y: Number(raw.lap_y ?? raw.y ?? 40),
            lebar: Number(raw.lap_lebar ?? raw.lebar ?? 30),
            rot: Number(raw.lap_rot ?? raw.rotasi ?? 0),
        };
        const hpRaw = raw.hp || {};
        const hpKosong =
            hpRaw.x === null || hpRaw.x === undefined ||
            hpRaw.y === null || hpRaw.y === undefined ||
            hpRaw.lebar === null || hpRaw.lebar === undefined ||
            hpRaw.rot === null || hpRaw.rot === undefined;

        return {
            id: Number(raw.id),
            url: raw.url,
            nama_asli: raw.nama_asli || raw.nama || ("Foto #" + raw.id),
            rasio: Number(raw.rasio || 1),
            urutan: Number(raw.urutan || 1),
            aktif: raw.aktif !== undefined ? Boolean(raw.aktif) : true,
            laptop: {
                jangkar: lap.jangkar === "bawah" ? "bawah" : "atas",
                x: Number(lap.x ?? 50),
                y: Number(lap.y ?? 40),
                lebar: Number(lap.lebar ?? 30),
                rot: Number(lap.rot ?? 0),
            },
            hp: hpKosong
                ? { jangkar: null, x: null, y: null, lebar: null, rot: null }
                : {
                    jangkar: hpRaw.jangkar === "bawah" ? "bawah" : "atas",
                    x: Number(hpRaw.x),
                    y: Number(hpRaw.y),
                    lebar: Number(hpRaw.lebar),
                    rot: Number(hpRaw.rot),
                },
        };
    }

    let daftarFotoTersimpan = (konfigurasi.foto || []).map(normalisasiItem);
    let daftarFoto = salinDalam(daftarFotoTersimpan);
    let perangkatAktif = "laptop";
    let idTerpilih = daftarFoto.length > 0 ? daftarFoto[daftarFoto.length - 1].id : null;
    let tinggiHalamanLogis = 1800;
    let faktorSkala = 1;
    let adaPerubahanBelumSimpan = false;

    let riwayatUndo = [];
    let riwayatRedo = [];
    let gesturAktif = null;

    const elPanggung = document.getElementById("le-panggung");
    const elWadahSkala = document.getElementById("le-wadah-skala");
    const elIframe = document.getElementById("le-iframe-pratinjau");
    const elOverlay = document.getElementById("le-overlay-kanvas");
    const elGarisTengah = document.getElementById("le-garis-tengah");
    const elKotakTerpilih = document.getElementById("le-kotak-terpilih");
    const elGambarTerpilih = document.getElementById("le-gambar-terpilih");
    const elLabelDerajat = document.getElementById("le-label-derajat");
    const elKosongPanggung = document.getElementById("le-kosong-panggung");
    const elBannerBelumSimpan = document.getElementById("le-banner-belum-simpan");
    const elBannerPesan = document.getElementById("le-banner-pesan");
    const elDaftarLapisan = document.getElementById("le-daftar-lapisan");
    const elTbodyGambar = document.getElementById("le-tbody-gambar");
    const elJumlahGambar = document.getElementById("le-jumlah-gambar");
    const elInputUnggah = document.getElementById("le-input-unggah");

    function getLebarLogis() {
        return perangkatAktif === "hp" ? LEBAR_LOGIS_HP : LEBAR_LOGIS_LAPTOP;
    }

    function bacaPropertiFoto(foto) {
        const hp = foto.hp || {};
        const hpAktif =
            perangkatAktif === "hp" &&
            hp.x !== null && hp.x !== undefined &&
            hp.y !== null && hp.y !== undefined &&
            hp.lebar !== null && hp.lebar !== undefined &&
            hp.rot !== null && hp.rot !== undefined;

        if (hpAktif) {
            return {
                jangkar: hp.jangkar || foto.laptop.jangkar,
                x: Number(hp.x),
                y: Number(hp.y),
                lebar: Number(hp.lebar),
                rot: Number(hp.rot),
                ikutLaptop: false,
            };
        }

        return {
            jangkar: foto.laptop.jangkar,
            x: Number(foto.laptop.x),
            y: Number(foto.laptop.y),
            lebar: Number(foto.laptop.lebar),
            rot: Number(foto.laptop.rot),
            ikutLaptop: true,
        };
    }

    function tulisPropertiFoto(foto, perubahan) {
        if (perangkatAktif === "hp") {
            const sudahAdaHp =
                foto.hp &&
                foto.hp.x !== null && foto.hp.x !== undefined &&
                foto.hp.y !== null && foto.hp.y !== undefined &&
                foto.hp.lebar !== null && foto.hp.lebar !== undefined &&
                foto.hp.rot !== null && foto.hp.rot !== undefined;

            if (!sudahAdaHp) {
                foto.hp = {
                    jangkar: foto.laptop.jangkar,
                    x: foto.laptop.x,
                    y: foto.laptop.y,
                    lebar: foto.laptop.lebar,
                    rot: foto.laptop.rot,
                };
            }

            if (perubahan.x !== undefined) foto.hp.x = bulatkan(perubahan.x);
            if (perubahan.y !== undefined) foto.hp.y = bulatkan(perubahan.y);
            if (perubahan.lebar !== undefined) foto.hp.lebar = bulatkan(perubahan.lebar);
            if (perubahan.rot !== undefined) foto.hp.rot = bulatkan(perubahan.rot);
            if (perubahan.jangkar !== undefined) foto.hp.jangkar = perubahan.jangkar;
        } else {
            if (perubahan.x !== undefined) foto.laptop.x = bulatkan(perubahan.x);
            if (perubahan.y !== undefined) foto.laptop.y = bulatkan(perubahan.y);
            if (perubahan.lebar !== undefined) foto.laptop.lebar = bulatkan(perubahan.lebar);
            if (perubahan.rot !== undefined) foto.laptop.rot = bulatkan(perubahan.rot);
            if (perubahan.jangkar !== undefined) foto.laptop.jangkar = perubahan.jangkar;
        }
    }

    function kePikselLogis(foto) {
        const W = getLebarLogis();
        const H = tinggiHalamanLogis;
        const prop = bacaPropertiFoto(foto);

        const cx = (prop.x / 100) * W;
        const cy = prop.jangkar === "atas"
            ? (prop.y / 100) * W
            : H - (prop.y / 100) * W;
        const lebarPx = (prop.lebar / 100) * W;
        const tinggiPx = lebarPx * (foto.rasio || 1);

        return {
            cx: cx,
            cy: cy,
            lebarPx: lebarPx,
            tinggiPx: tinggiPx,
            rotasi: prop.rot,
            jangkar: prop.jangkar,
        };
    }

    function dariPikselLogis(cx, cy, jangkar) {
        const W = getLebarLogis();
        const H = tinggiHalamanLogis;
        const x = (cx / W) * 100;
        const y = ((jangkar === "atas" ? cy : H - cy) / W) * 100;
        return { x: x, y: y };
    }

    function pointerKeLogis(event) {
        const rect = elOverlay.getBoundingClientRect();
        return {
            px: (event.clientX - rect.left) / faktorSkala,
            py: (event.clientY - rect.top) / faktorSkala,
        };
    }

    function titikDiDalamFoto(px, py, foto) {
        if (!foto.aktif) return false;
        const kotak = kePikselLogis(foto);
        const dx = px - kotak.cx;
        const dy = py - kotak.cy;
        const rad = (-kotak.rotasi * Math.PI) / 180;
        const lokalX = dx * Math.cos(rad) - dy * Math.sin(rad);
        const lokalY = dx * Math.sin(rad) + dy * Math.cos(rad);

        return (
            Math.abs(lokalX) <= kotak.lebarPx / 2 &&
            Math.abs(lokalY) <= kotak.tinggiPx / 2
        );
    }

    function perbaruiSkalaPanggung() {
        if (!elPanggung || !elWadahSkala || !elIframe || !elOverlay) return;
        const W = getLebarLogis();
        const H = Math.max(tinggiHalamanLogis, 900);
        const lebarTersedia = Math.max(320, elPanggung.clientWidth - 32);
        faktorSkala = lebarTersedia / W;

        const tinggiTerukur = Math.round(H * faktorSkala);
        elWadahSkala.style.width = Math.round(W * faktorSkala) + "px";
        elWadahSkala.style.height = tinggiTerukur + "px";
        elWadahSkala.style.minHeight = "0px";
        elPanggung.style.minHeight = (tinggiTerukur + 32) + "px";

        elIframe.style.width = W + "px";
        elIframe.style.height = H + "px";
        elIframe.style.transform = "scale(" + faktorSkala + ")";

        elOverlay.style.width = W + "px";
        elOverlay.style.height = H + "px";
        elOverlay.style.transform = "scale(" + faktorSkala + ")";
        elOverlay.style.setProperty("--le-skala", String(faktorSkala));

        renderKotakTerpilih();
    }

    function kirimDrafKeIframe(sembunyikanId) {
        if (!elIframe || !elIframe.contentWindow) return;
        elIframe.contentWindow.postMessage(
            {
                tipe: "latar-draf",
                foto: daftarFoto,
                sembunyikanId: sembunyikanId !== undefined ? sembunyikanId : idTerpilih,
            },
            ORIGIN_ASAL
        );
    }

    function renderKotakTerpilih() {
        if (elKosongPanggung) {
            elKosongPanggung.hidden = daftarFoto.length > 0;
        }

        const foto = daftarFoto.find(function (f) {
            return Number(f.id) === Number(idTerpilih) && f.aktif;
        });

        if (!foto) {
            elKotakTerpilih.classList.remove("aktif");
            perbaruiPanelKanan();
            return;
        }

        const kotak = kePikselLogis(foto);
        const kiri = kotak.cx - kotak.lebarPx / 2;
        const atas = kotak.cy - kotak.tinggiPx / 2;

        elKotakTerpilih.classList.add("aktif");
        elKotakTerpilih.style.width = kotak.lebarPx + "px";
        elKotakTerpilih.style.height = kotak.tinggiPx + "px";
        elKotakTerpilih.style.transform =
            "translate(" + kiri + "px, " + atas + "px) rotate(" + kotak.rotasi + "deg)";

        if (elGambarTerpilih.getAttribute("src") !== foto.url) {
            elGambarTerpilih.setAttribute("src", foto.url);
        }

        perbaruiPanelKanan();
    }

    function segarkanSemua() {
        kirimDrafKeIframe(idTerpilih);
        renderKotakTerpilih();
        renderDaftarLapisan();
        renderTabelGambarTerupload();
        perbaruiStatusTombol();
    }

    function catatRiwayat() {
        riwayatUndo.push({
            foto: salinDalam(daftarFoto),
            idTerpilih: idTerpilih,
        });
        if (riwayatUndo.length > BATAS_RIWAYAT) {
            riwayatUndo.shift();
        }
        riwayatRedo = [];
        tandaiBelumSimpan(true);
    }

    function tandaiBelumSimpan(status) {
        adaPerubahanBelumSimpan = status;
        if (elBannerBelumSimpan) {
            elBannerBelumSimpan.classList.toggle("tampil", status);
        }
    }

    function tampilkanPesan(teks, jenis) {
        if (!elBannerPesan) return;
        elBannerPesan.textContent = teks;
        elBannerPesan.className = "le-banner-status tampil " + jenis;
        clearTimeout(tampilkanPesan._timer);
        tampilkanPesan._timer = setTimeout(function () {
            elBannerPesan.classList.remove("tampil");
        }, 8000);
    }

    function rapikanNomorUrutan() {
        const terurut = daftarFoto.slice().sort(function (a, b) {
            return Number(a.urutan) - Number(b.urutan) || Number(a.id) - Number(b.id);
        });
        terurut.forEach(function (f, idx) {
            f.urutan = idx + 1;
        });
    }

    function normalkanSudut(derajat) {
        let hasil = ((derajat + 180) % 360 + 360) % 360 - 180;
        if (hasil === -180 && derajat > 0) hasil = 180;
        return hasil;
    }

    // ===== HAPUS PERMANEN KE SERVER (DB + STORAGE) =====

    async function hapusFotoServer(foto) {
        const urlHapus = (konfigurasi.urlHapusTemplate || "/pengelola/latar-beranda/__ID__/hapus")
            .replace("__ID__", String(foto.id));

        const respons = await fetch(urlHapus, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": konfigurasi.csrfToken,
                "Accept": "application/json",
                "Content-Type": "application/json",
            },
        });

        if (!respons.ok) {
            const hasil = await respons.json().catch(function () { return {}; });
            throw new Error(hasil.message || "Gagal menghapus foto dari server.");
        }

        daftarFoto = daftarFoto.filter(function (f) {
            return Number(f.id) !== Number(foto.id);
        });
        daftarFotoTersimpan = daftarFotoTersimpan.filter(function (f) {
            return Number(f.id) !== Number(foto.id);
        });

        if (Number(idTerpilih) === Number(foto.id)) {
            idTerpilih = daftarFoto.length > 0 ? daftarFoto[daftarFoto.length - 1].id : null;
        }

        rapikanNomorUrutan();
        segarkanSemua();
        kirimDrafKeIframe(idTerpilih);

        tampilkanPesan('Foto "' + foto.nama_asli + '" berhasil dihapus permanen.', "sukses");
    }

    // ===== EVENT POINTER =====

    elOverlay.addEventListener("pointerdown", function (event) {
        const titik = pointerKeLogis(event);
        const targetSudut = event.target.closest(".le-pegangan-sudut");
        const targetPutar = event.target.closest("#le-pegangan-putar");
        const fotoAktif = daftarFoto.find(function (f) {
            return Number(f.id) === Number(idTerpilih);
        });

        if (targetPutar && fotoAktif) {
            event.preventDefault();
            const kotak = kePikselLogis(fotoAktif);
            const sudutAwal = Math.atan2(titik.py - kotak.cy, titik.px - kotak.cx);
            gesturAktif = {
                jenis: "putar",
                pointerId: event.pointerId,
                snapshotSebelum: salinDalam(daftarFoto),
                sudutAwal: sudutAwal,
                rotasiAwal: kotak.rotasi,
                berubah: false,
            };
            elLabelDerajat.classList.add("aktif");
            if (elOverlay.setPointerCapture) {
                elOverlay.setPointerCapture(event.pointerId);
            }
            return;
        }

        if (targetSudut && fotoAktif) {
            event.preventDefault();
            const kotak = kePikselLogis(fotoAktif);
            const prop = bacaPropertiFoto(fotoAktif);
            const jarakAwal = Math.hypot(titik.px - kotak.cx, titik.py - kotak.cy);
            gesturAktif = {
                jenis: "ukuran",
                pointerId: event.pointerId,
                snapshotSebelum: salinDalam(daftarFoto),
                jarakAwal: Math.max(10, jarakAwal),
                lebarAwal: prop.lebar,
                berubah: false,
            };
            if (elOverlay.setPointerCapture) {
                elOverlay.setPointerCapture(event.pointerId);
            }
            return;
        }

        const urutDepanKeBelakang = daftarFoto.slice().sort(function (a, b) {
            return Number(b.urutan) - Number(a.urutan);
        });
        const fotoTertangkap = urutDepanKeBelakang.find(function (f) {
            return titikDiDalamFoto(titik.px, titik.py, f);
        });

        if (fotoTertangkap) {
            idTerpilih = fotoTertangkap.id;
            const kotak = kePikselLogis(fotoTertangkap);
            gesturAktif = {
                jenis: "geser",
                pointerId: event.pointerId,
                snapshotSebelum: salinDalam(daftarFoto),
                offsetX: titik.px - kotak.cx,
                offsetY: titik.py - kotak.cy,
                jangkar: kotak.jangkar,
                berubah: false,
            };
            if (elOverlay.setPointerCapture) {
                elOverlay.setPointerCapture(event.pointerId);
            }
            segarkanSemua();
        }
    });

    elOverlay.addEventListener("pointermove", function (event) {
        if (!gesturAktif || idTerpilih === null) return;
        const foto = daftarFoto.find(function (f) {
            return Number(f.id) === Number(idTerpilih);
        });
        if (!foto) return;

        const titik = pointerKeLogis(event);
        const W = getLebarLogis();
        const H = tinggiHalamanLogis;

        if (!gesturAktif.berubah) {
            riwayatUndo.push({
                foto: gesturAktif.snapshotSebelum,
                idTerpilih: idTerpilih,
            });
            if (riwayatUndo.length > BATAS_RIWAYAT) riwayatUndo.shift();
            riwayatRedo = [];
            gesturAktif.berubah = true;
            tandaiBelumSimpan(true);
        }

        if (gesturAktif.jenis === "geser") {
            let cxBaru = titik.px - gesturAktif.offsetX;
            let cyBaru = titik.py - gesturAktif.offsetY;

            cxBaru = Math.max(0, Math.min(W, cxBaru));
            cyBaru = Math.max(0, Math.min(H, cyBaru));

            const koordinat = dariPikselLogis(cxBaru, cyBaru, gesturAktif.jangkar);

            if (Math.abs(koordinat.x - 50) <= AMBANG_SNAP_TENGAH) {
                koordinat.x = 50;
                elGarisTengah.classList.add("aktif");
            } else {
                elGarisTengah.classList.remove("aktif");
            }

            tulisPropertiFoto(foto, { x: koordinat.x, y: koordinat.y });
        }

        if (gesturAktif.jenis === "ukuran") {
            const kotak = kePikselLogis(foto);
            const jarakSekarang = Math.hypot(titik.px - kotak.cx, titik.py - kotak.cy);
            let lebarBaru = gesturAktif.lebarAwal * (jarakSekarang / gesturAktif.jarakAwal);
            lebarBaru = Math.max(UKURAN_MIN_CQW, Math.min(UKURAN_MAKS_CQW, lebarBaru));

            tulisPropertiFoto(foto, { lebar: lebarBaru });
        }

        if (gesturAktif.jenis === "putar") {
            const kotak = kePikselLogis(foto);
            const sudutSekarang = Math.atan2(titik.py - kotak.cy, titik.px - kotak.cx);
            const selisihDerajat = ((sudutSekarang - gesturAktif.sudutAwal) * 180) / Math.PI;
            let rotBaru = normalkanSudut(gesturAktif.rotasiAwal + selisihDerajat);

            if (event.shiftKey) {
                rotBaru = Math.round(rotBaru / 15) * 15;
            } else {
                const kelipatan90 = Math.round(rotBaru / 90) * 90;
                if (Math.abs(rotBaru - kelipatan90) <= AMBANG_SNAP_SUDUT) {
                    rotBaru = kelipatan90;
                }
            }
            rotBaru = normalkanSudut(rotBaru);

            elLabelDerajat.textContent = Math.round(rotBaru) + "°";
            tulisPropertiFoto(foto, { rot: rotBaru });
        }

        renderKotakTerpilih();
        renderTabelGambarTerupload();
        perbaruiStatusTombol();
    });

    function selesaikanGestur(event) {
        if (!gesturAktif) return;
        if (
            event &&
            gesturAktif.pointerId !== undefined &&
            elOverlay.hasPointerCapture &&
            elOverlay.hasPointerCapture(gesturAktif.pointerId)
        ) {
            elOverlay.releasePointerCapture(gesturAktif.pointerId);
        }
        gesturAktif = null;
        elGarisTengah.classList.remove("aktif");
        elLabelDerajat.classList.remove("aktif");
        segarkanSemua();
    }

    elOverlay.addEventListener("pointerup", selesaikanGestur);
    elOverlay.addEventListener("pointercancel", selesaikanGestur);

    // ===== PANEL KANAN, LAPISAN & TABEL GAMBAR =====

    function perbaruiPanelKanan() {
        const foto = daftarFoto.find(function (f) {
            return Number(f.id) === Number(idTerpilih);
        });
        const tombolAksi = document.querySelectorAll("[data-aksi]");
        const kontrolHp = document.getElementById("le-kontrol-khusus-hp");
        const statusHp = document.getElementById("le-status-hp");
        const btnSembunyi = document.getElementById("le-btn-tampil-sembunyi");

        tombolAksi.forEach(function (btn) {
            btn.disabled = !foto;
        });

        if (kontrolHp) {
            kontrolHp.hidden = perangkatAktif !== "hp";
        }

        const elX = document.getElementById("le-val-x");
        const elY = document.getElementById("le-val-y");
        const elLebar = document.getElementById("le-val-lebar");
        const elRotasi = document.getElementById("le-val-rotasi");

        if (!foto) {
            if (elX) elX.textContent = "-";
            if (elY) elY.textContent = "-";
            if (elLebar) elLebar.textContent = "-";
            if (elRotasi) elRotasi.textContent = "-";
            if (statusHp) statusHp.textContent = "HP: ikut laptop";
            return;
        }

        const prop = bacaPropertiFoto(foto);
        if (elX) elX.textContent = bulatkan(prop.x) + "%";
        if (elY) elY.textContent = bulatkan(prop.y) + "%";
        if (elLebar) elLebar.textContent = bulatkan(prop.lebar) + " cqw";
        if (elRotasi) elRotasi.textContent = bulatkan(prop.rot) + "°";

        if (btnSembunyi) {
            btnSembunyi.textContent = foto.aktif ? "Sembunyikan" : "Tampilkan";
        }
        if (statusHp) {
            statusHp.textContent = prop.ikutLaptop ? "HP: ikut laptop" : "HP: susunan khusus";
        }
    }

    function renderDaftarLapisan() {
        if (!elDaftarLapisan) return;
        elDaftarLapisan.innerHTML = "";

        const terurut = daftarFoto.slice().sort(function (a, b) {
            return Number(b.urutan) - Number(a.urutan);
        });

        terurut.forEach(function (foto) {
            const baris = document.createElement("div");
            baris.className = "le-baris-lapisan" + (Number(foto.id) === Number(idTerpilih) ? " terpilih" : "");
            baris.addEventListener("click", function () {
                idTerpilih = foto.id;
                segarkanSemua();
            });

            const thumb = document.createElement("img");
            thumb.src = foto.url;
            thumb.alt = "";
            thumb.className = "le-thumb-lapisan";

            const nama = document.createElement("span");
            nama.className = "le-nama-lapisan";
            nama.textContent = foto.nama_asli;

            baris.appendChild(thumb);
            baris.appendChild(nama);
            elDaftarLapisan.appendChild(baris);
        });
    }

    function renderTabelGambarTerupload() {
        if (!elTbodyGambar) return;
        if (elJumlahGambar) {
            elJumlahGambar.textContent = String(daftarFoto.length);
        }

        if (daftarFoto.length === 0) {
            elTbodyGambar.innerHTML =
                '<tr><td colspan="3" style="text-align:center; color:#64748b; padding:14px;">Belum ada gambar yang diupload.</td></tr>';
            return;
        }

        elTbodyGambar.innerHTML = "";
        daftarFoto.forEach(function (foto) {
            const prop = bacaPropertiFoto(foto);
            const tr = document.createElement("tr");
            if (Number(foto.id) === Number(idTerpilih)) {
                tr.className = "baris-aktif";
            }

            tr.addEventListener("click", function () {
                idTerpilih = foto.id;
                segarkanSemua();
            });

            const tdThumb = document.createElement("td");
            const img = document.createElement("img");
            img.src = foto.url;
            img.alt = "";
            img.className = "le-thumb-tabel";
            tdThumb.appendChild(img);

            const tdInfo = document.createElement("td");
            tdInfo.innerHTML =
                '<div class="le-nama-file-tabel" title="' + foto.nama_asli + '">' + foto.nama_asli + "</div>" +
                '<div class="le-info-posisi-tabel">X:' + bulatkan(prop.x) + " Y:" + bulatkan(prop.y) +
                " L:" + bulatkan(prop.lebar) + (foto.aktif ? "" : " (Tersembunyi)") + "</div>";

            const tdAksi = document.createElement("td");
            const divAksi = document.createElement("div");
            divAksi.className = "le-aksi-tabel";

            const btnPilih = document.createElement("button");
            btnPilih.type = "button";
            btnPilih.className = "le-btn-tabel";
            btnPilih.textContent = "Pilih";
            btnPilih.addEventListener("click", function (ev) {
                ev.stopPropagation();
                idTerpilih = foto.id;
                segarkanSemua();
            });

            const btnHapus = document.createElement("button");
            btnHapus.type = "button";
            btnHapus.className = "le-btn-tabel hapus";
            btnHapus.textContent = "Hapus";
            btnHapus.addEventListener("click", async function (ev) {
                ev.stopPropagation();
                if (!confirm('Hapus permanen foto "' + foto.nama_asli + '"?')) return;
                btnHapus.disabled = true;
                try {
                    await hapusFotoServer(foto);
                } catch (err) {
                    btnHapus.disabled = false;
                    tampilkanPesan(err.message || "Gagal menghapus foto.", "error");
                }
            });

            divAksi.appendChild(btnPilih);
            divAksi.appendChild(btnHapus);
            tdAksi.appendChild(divAksi);

            tr.appendChild(tdThumb);
            tr.appendChild(tdInfo);
            tr.appendChild(tdAksi);
            elTbodyGambar.appendChild(tr);
        });
    }

    function perbaruiStatusTombol() {
        const btnUndo = document.getElementById("le-btn-urungkan");
        const btnRedo = document.getElementById("le-btn-ulangi");
        if (btnUndo) btnUndo.disabled = riwayatUndo.length === 0;
        if (btnRedo) btnRedo.disabled = riwayatRedo.length === 0;
    }

    function jalankanAksiPanel(aksi) {
        const foto = daftarFoto.find(function (f) {
            return Number(f.id) === Number(idTerpilih);
        });
        if (!foto) return;

        if (aksi === "hapus") {
            if (!confirm('Hapus permanen foto "' + foto.nama_asli + '"?')) return;
            hapusFotoServer(foto).catch(function (err) {
                tampilkanPesan(err.message || "Gagal menghapus foto.", "error");
            });
            return;
        }

        catatRiwayat();
        const prop = bacaPropertiFoto(foto);

        switch (aksi) {
            case "maju":
                foto.urutan = Number(foto.urutan) + 1;
                break;
            case "mundur":
                foto.urutan = Math.max(1, Number(foto.urutan) - 1);
                break;
            case "paling-depan": {
                const maks = daftarFoto.reduce(function (m, f) { return Math.max(m, Number(f.urutan)); }, 0);
                foto.urutan = maks + 1;
                break;
            }
            case "paling-belakang": {
                daftarFoto.forEach(function (f) { f.urutan = Number(f.urutan) + 1; });
                foto.urutan = 1;
                break;
            }
            case "perkecil":
                tulisPropertiFoto(foto, { lebar: Math.max(UKURAN_MIN_CQW, prop.lebar - 5) });
                break;
            case "perbesar":
                tulisPropertiFoto(foto, { lebar: Math.min(UKURAN_MAKS_CQW, prop.lebar + 5) });
                break;
            case "putar-kiri":
                tulisPropertiFoto(foto, { rot: normalkanSudut(prop.rot - 15) });
                break;
            case "putar-kanan":
                tulisPropertiFoto(foto, { rot: normalkanSudut(prop.rot + 15) });
                break;
            case "luruskan":
                tulisPropertiFoto(foto, { rot: 0 });
                break;
            case "pusatkan":
                tulisPropertiFoto(foto, { x: 50 });
                break;
            case "jangkar-atas":
                tulisPropertiFoto(foto, { jangkar: "atas" });
                break;
            case "jangkar-bawah":
                tulisPropertiFoto(foto, { jangkar: "bawah" });
                break;
            case "toggle-aktif":
                foto.aktif = !foto.aktif;
                break;
            case "salin-laptop":
                foto.hp = {
                    jangkar: foto.laptop.jangkar,
                    x: foto.laptop.x,
                    y: foto.laptop.y,
                    lebar: foto.laptop.lebar,
                    rot: foto.laptop.rot,
                };
                break;
            case "hp-ikut-laptop":
                foto.hp = { jangkar: null, x: null, y: null, lebar: null, rot: null };
                break;
        }

        rapikanNomorUrutan();
        segarkanSemua();
    }

    // ===== TOMBOL TOOLBAR, PANEL & SERVER =====

    document.querySelectorAll("[data-aksi]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            jalankanAksiPanel(btn.dataset.aksi);
        });
    });

    document.querySelectorAll(".le-tombol-perangkat").forEach(function (btn) {
        btn.addEventListener("click", function () {
            document.querySelectorAll(".le-tombol-perangkat").forEach(function (b) {
                b.classList.remove("aktif");
            });
            btn.classList.add("aktif");
            perangkatAktif = btn.dataset.perangkat || "laptop";
            perbaruiSkalaPanggung();
            segarkanSemua();
        });
    });

    document.querySelectorAll(".le-tab-btn").forEach(function (btn) {
        btn.addEventListener("click", function () {
            document.querySelectorAll(".le-tab-btn").forEach(function (b) { b.classList.remove("aktif"); });
            document.querySelectorAll(".le-isi-tab").forEach(function (p) { p.classList.remove("aktif"); });
            btn.classList.add("aktif");
            const target = document.getElementById("le-tab-" + btn.dataset.tab);
            if (target) target.classList.add("aktif");
        });
    });

    const btnBatalPilih = document.getElementById("le-btn-batal-pilih");
    if (btnBatalPilih) {
        btnBatalPilih.addEventListener("click", function () {
            idTerpilih = null;
            segarkanSemua();
        });
    }

    const btnUndo = document.getElementById("le-btn-urungkan");
    if (btnUndo) {
        btnUndo.addEventListener("click", function () {
            if (riwayatUndo.length === 0) return;
            riwayatRedo.push({ foto: salinDalam(daftarFoto), idTerpilih: idTerpilih });
            const prev = riwayatUndo.pop();
            daftarFoto = prev.foto;
            idTerpilih = prev.idTerpilih;
            tandaiBelumSimpan(true);
            segarkanSemua();
        });
    }

    const btnRedo = document.getElementById("le-btn-ulangi");
    if (btnRedo) {
        btnRedo.addEventListener("click", function () {
            if (riwayatRedo.length === 0) return;
            riwayatUndo.push({ foto: salinDalam(daftarFoto), idTerpilih: idTerpilih });
            const next = riwayatRedo.pop();
            daftarFoto = next.foto;
            idTerpilih = next.idTerpilih;
            tandaiBelumSimpan(true);
            segarkanSemua();
        });
    }

    const btnBatalPerubahan = document.getElementById("le-btn-batalkan-perubahan");
    if (btnBatalPerubahan) {
        btnBatalPerubahan.addEventListener("click", function () {
            daftarFoto = salinDalam(daftarFotoTersimpan);
            idTerpilih = daftarFoto.length > 0 ? daftarFoto[daftarFoto.length - 1].id : null;
            riwayatUndo = [];
            riwayatRedo = [];
            tandaiBelumSimpan(false);
            segarkanSemua();
            tampilkanPesan("Perubahan dibatalkan ke susunan tersimpan.", "peringatan");
        });
    }

    const btnApply = document.getElementById("le-btn-apply");
    if (btnApply) {
        btnApply.addEventListener("click", function () {
            kirimDrafKeIframe(idTerpilih);
            tampilkanPesan("Posisi dikunci ke pratinjau. Klik Terapkan untuk menyimpan ke publik.", "sukses");
        });
    }

    const btnTerapkan = document.getElementById("le-btn-terapkan");
    if (btnTerapkan) {
        btnTerapkan.addEventListener("click", async function () {
            btnTerapkan.disabled = true;
            try {
                const payload = {
                    foto: daftarFoto.map(function (f) {
                        const hpKosong =
                            f.hp.x === null || f.hp.x === undefined ||
                            f.hp.y === null || f.hp.y === undefined ||
                            f.hp.lebar === null || f.hp.lebar === undefined ||
                            f.hp.rot === null || f.hp.rot === undefined;

                        return {
                            id: f.id,
                            aktif: f.aktif,
                            urutan: f.urutan,
                            jangkar: f.laptop.jangkar,
                            lap_x: f.laptop.x,
                            lap_y: f.laptop.y,
                            lap_lebar: f.laptop.lebar,
                            lap_rot: f.laptop.rot,
                            hp_jangkar: hpKosong ? null : f.hp.jangkar,
                            hp_x: hpKosong ? null : f.hp.x,
                            hp_y: hpKosong ? null : f.hp.y,
                            hp_lebar: hpKosong ? null : f.hp.lebar,
                            hp_rot: hpKosong ? null : f.hp.rot,
                        };
                    }),
                };

                const res = await fetch(konfigurasi.urlSimpan, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": konfigurasi.csrfToken,
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(payload),
                });

                const hasil = await res.json().catch(function () { return {}; });
                if (!res.ok) {
                    throw new Error(hasil.message || "Gagal menyimpan susunan latar.");
                }

                daftarFotoTersimpan = salinDalam(daftarFoto);
                tandaiBelumSimpan(false);
                kirimDrafKeIframe(idTerpilih);
                tampilkanPesan(hasil.message || "Susunan latar berhasil diterapkan.", "sukses");
            } catch (err) {
                tampilkanPesan(err.message || "Terjadi kesalahan saat menyimpan.", "error");
            } finally {
                btnTerapkan.disabled = false;
            }
        });
    }

    const btnResetTotal = document.getElementById("le-btn-reset-total");
    if (btnResetTotal) {
        btnResetTotal.addEventListener("click", async function () {
            if (!confirm("Nonaktifkan semua foto latar agar halaman publik kembali polos?")) return;
            try {
                const res = await fetch(konfigurasi.urlReset, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": konfigurasi.csrfToken,
                        "Accept": "application/json",
                    },
                });
                const hasil = await res.json().catch(function () { return {}; });
                if (!res.ok) throw new Error(hasil.message || "Gagal mereset latar.");

                daftarFoto.forEach(function (f) { f.aktif = false; });
                daftarFotoTersimpan = salinDalam(daftarFoto);
                idTerpilih = null;
                tandaiBelumSimpan(false);
                segarkanSemua();
                tampilkanPesan(hasil.message || "Latar dinonaktifkan.", "sukses");
            } catch (err) {
                tampilkanPesan(err.message || "Gagal mereset latar.", "error");
            }
        });
    }

    const btnUnggah = document.getElementById("le-btn-unggah");
    if (btnUnggah && elInputUnggah) {
        btnUnggah.addEventListener("click", function () {
            elInputUnggah.click();
        });

        elInputUnggah.addEventListener("change", async function () {
            if (!elInputUnggah.files || elInputUnggah.files.length === 0) return;
            const formData = new FormData();
            Array.from(elInputUnggah.files).forEach(function (file) {
                formData.append("foto[]", file);
            });

            btnUnggah.disabled = true;
            try {
                const res = await fetch(konfigurasi.urlUnggah, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": konfigurasi.csrfToken,
                        "Accept": "application/json",
                    },
                    body: formData,
                });
                const hasil = await res.json().catch(function () { return {}; });
                if (!res.ok) {
                    throw new Error(hasil.message || "Gagal mengunggah gambar.");
                }

                const itemBaru = (hasil.data || []).map(normalisasiItem);
                itemBaru.forEach(function (item) {
                    item.laptop.lebar = UKURAN_AWAL_UNGGAH;
                    daftarFoto.push(item);
                    daftarFotoTersimpan.push(salinDalam(item));
                });

                if (itemBaru.length > 0) {
                    idTerpilih = itemBaru[itemBaru.length - 1].id;
                }
                rapikanNomorUrutan();
                segarkanSemua();
                tampilkanPesan(hasil.message || "Gambar berhasil diunggah.", "sukses");
            } catch (err) {
                tampilkanPesan(err.message || "Gagal mengunggah gambar.", "error");
            } finally {
                elInputUnggah.value = "";
                btnUnggah.disabled = false;
            }
        });
    }

    // Keyboard panah untuk geser halus
    document.addEventListener("keydown", function (event) {
        if (["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName)) return;
        const foto = daftarFoto.find(function (f) { return Number(f.id) === Number(idTerpilih); });
        if (!foto) return;

        const langkah = event.shiftKey ? LANGKAH_GESER_SHIFT : LANGKAH_GESER_PANAH;
        const prop = bacaPropertiFoto(foto);
        let ditangani = true;

        switch (event.key) {
            case "ArrowLeft":
                tulisPropertiFoto(foto, { x: prop.x - langkah });
                break;
            case "ArrowRight":
                tulisPropertiFoto(foto, { x: prop.x + langkah });
                break;
            case "ArrowUp":
                tulisPropertiFoto(foto, { y: prop.jangkar === "atas" ? prop.y - langkah : prop.y + langkah });
                break;
            case "ArrowDown":
                tulisPropertiFoto(foto, { y: prop.jangkar === "atas" ? prop.y + langkah : prop.y - langkah });
                break;
            default:
                ditangani = false;
        }

        if (ditangani) {
            event.preventDefault();
            catatRiwayat();
            segarkanSemua();
        }
    });

    // Terima pesan dari iframe pratinjau (siap & tinggi halaman)
    window.addEventListener("message", function (event) {
        if (event.origin !== ORIGIN_ASAL || event.source !== elIframe.contentWindow) return;
        const data = event.data;
        if (!data || typeof data !== "object") return;

        if (data.tipe === "latar-siap") {
            kirimDrafKeIframe(idTerpilih);
        } else if (data.tipe === "latar-tinggi" && typeof data.tinggi === "number") {
            tinggiHalamanLogis = Math.max(900, Math.ceil(data.tinggi));
            perbaruiSkalaPanggung();
            kirimDrafKeIframe(idTerpilih);
        }
    });

    window.addEventListener("resize", function () {
        perbaruiSkalaPanggung();
    });

    perbaruiSkalaPanggung();
    segarkanSemua();
})();