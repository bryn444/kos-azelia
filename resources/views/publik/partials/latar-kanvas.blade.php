{{--
  NAMA FILE: resources/views/publik/partials/latar-kanvas.blade.php
  FUNGSI: Kanvas latar belakang seluruh halaman publik (anak langsung <body>, ikut bergulir
          bersama halaman) berisi foto kolase aktif dengan variabel posisi Laptop & HP.
  HALAMAN: Halaman publik calon penghuni (disisipkan di resources/views/layouts/publik.blade.php).
  CARA MENGUBAH:
  - Jangan menambahkan style inline berwarna; satu-satunya style inline adalah variabel angka
    (--lap-x, --lap-y, --lap-lebar, --lap-rot, --hp-x, --hp-y, --hp-lebar, --hp-rot).
  - Urutan tampil lapisan mengikuti urutan elemen <img> di DOM (urutan kecil di belakang, besar di depan).
--}}
@php
    $daftarFotoLatar = $latarUtama ?? [];
@endphp

{{-- ===== BAGIAN: KANVAS LATAR BELAKANG HALAMAN ===== --}}
<div class="latar-kanvas" aria-hidden="true">
    @foreach ($daftarFotoLatar as $item)
        @php
            $lap = $item['laptop'] ?? ['jangkar' => 'atas', 'x' => 50, 'y' => 40, 'lebar' => 30, 'rot' => 0];
            $hp = $item['hp'] ?? $lap;

            $kelasJangkarLap = ($lap['jangkar'] ?? 'atas') === 'bawah' ? 'lap-bawah' : 'lap-atas';
            $kelasJangkarHp = ($hp['jangkar'] ?? 'atas') === 'bawah' ? 'hp-bawah' : 'hp-atas';

            $lapX = (float) ($lap['x'] ?? 50);
            $lapY = (float) ($lap['y'] ?? 40);
            $lapLebar = (float) ($lap['lebar'] ?? 30);
            $lapRot = (float) ($lap['rot'] ?? 0);

            $hpX = (float) ($hp['x'] ?? $lapX);
            $hpY = (float) ($hp['y'] ?? $lapY);
            $hpLebar = (float) ($hp['lebar'] ?? $lapLebar);
            $hpRot = (float) ($hp['rot'] ?? $lapRot);
        @endphp
        <img
            src="{{ $item['url'] }}"
            alt=""
            class="latar-foto {{ $kelasJangkarLap }} {{ $kelasJangkarHp }}"
            decoding="async"
            loading="lazy"
            style="--lap-x: {{ $lapX }}; --lap-y: {{ $lapY }}; --lap-lebar: {{ $lapLebar }}; --lap-rot: {{ $lapRot }}; --hp-x: {{ $hpX }}; --hp-y: {{ $hpY }}; --hp-lebar: {{ $hpLebar }}; --hp-rot: {{ $hpRot }};"
        >
    @endforeach
</div>