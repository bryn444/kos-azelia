/**
 * ============================================================================
 * FILE: public/js/latar-kanvas.js
 * FUNGSI: Berjalan di dalam iframe pratinjau halaman publik.
 *         Merender ulang elemen .latar-foto dari draf induk dan melaporkan
 *         tinggi penuh halaman agar wadah kanvas mengikuti ukuran halaman.
 * ============================================================================
 */

(function () {
    "use strict";

    const ORIGIN_ASAL = window.location.origin;

    function kirimKeInduk(payload) {
        if (window.parent && window.parent !== window) {
            window.parent.postMessage(payload, ORIGIN_ASAL);
        }
    }

    function laporkanTinggiHalaman() {
        const tinggiBody = document.body ? document.body.scrollHeight : 0;
        const tinggiDoc = document.documentElement ? document.documentElement.scrollHeight : 0;
        const tinggi = Math.max(tinggiBody, tinggiDoc, 900);

        kirimKeInduk({
            tipe: "latar-tinggi",
            tinggi: tinggi,
        });
    }

    function kunciInteraksiHalaman() {
        document.addEventListener("click", function (event) {
            const tautan = event.target.closest("a, button");
            if (tautan) {
                event.preventDefault();
            }
        }, true);

        document.addEventListener("submit", function (event) {
            event.preventDefault();
        }, true);
    }

    function renderKanvasDraf(daftarFoto, sembunyikanId) {
        let wadah = document.getElementById("latar-kanvas");
        if (!wadah) {
            wadah = document.createElement("div");
            wadah.id = "latar-kanvas";
            wadah.className = "latar-kanvas";
            wadah.setAttribute("aria-hidden", "true");
            document.body.prepend(wadah);
        }

        wadah.innerHTML = "";

        const terurut = daftarFoto.slice().sort(function (a, b) {
            return Number(a.urutan) - Number(b.urutan);
        });

        terurut.forEach(function (item) {
            if (!item.aktif) return;
            if (sembunyikanId !== null && Number(item.id) === Number(sembunyikanId)) return;

            const lap = item.laptop || { jangkar: "atas", x: 50, y: 40, lebar: 30, rot: 0 };
            const hp = item.hp && item.hp.x !== null && item.hp.x !== undefined ? item.hp : lap;

            const img = document.createElement("img");
            img.src = item.url;
            img.alt = "";
            img.draggable = false;
            img.dataset.fotoId = String(item.id);
            img.className = [
                "latar-foto",
                (lap.jangkar === "bawah" ? "lap-bawah" : "lap-atas"),
                ((hp.jangkar || lap.jangkar) === "bawah" ? "hp-bawah" : "hp-atas"),
            ].join(" ");

            img.style.setProperty("--lap-x", String(lap.x));
            img.style.setProperty("--lap-y", String(lap.y));
            img.style.setProperty("--lap-lebar", String(lap.lebar));
            img.style.setProperty("--lap-rot", String(lap.rot));
            img.style.setProperty("--hp-x", String(hp.x));
            img.style.setProperty("--hp-y", String(hp.y));
            img.style.setProperty("--hp-lebar", String(hp.lebar));
            img.style.setProperty("--hp-rot", String(hp.rot));
            img.style.zIndex = String(Number(item.urutan) || 1);

            wadah.appendChild(img);
        });
    }

    window.addEventListener("message", function (event) {
        if (event.origin !== ORIGIN_ASAL || event.source !== window.parent) {
            return;
        }
        const data = event.data;
        if (!data || typeof data !== "object") {
            return;
        }

        if (data.tipe === "latar-draf" && Array.isArray(data.foto)) {
            renderKanvasDraf(data.foto, data.sembunyikanId ?? null);
            laporkanTinggiHalaman();
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        kunciInteraksiHalaman();
        laporkanTinggiHalaman();

        if (typeof ResizeObserver !== "undefined" && document.body) {
            const pengamat = new ResizeObserver(function () {
                laporkanTinggiHalaman();
            });
            pengamat.observe(document.body);
        }

        kirimKeInduk({ tipe: "latar-siap" });
    });

    window.addEventListener("load", function () {
        laporkanTinggiHalaman();
        kirimKeInduk({ tipe: "latar-siap" });
    });
})();