<aside class="sidebar-kaca d-none d-lg-flex">
    <div class="sidebar-brand-box">
        @php
            $nama = auth()->user()->nama_lengkap ?? 'Pemilik';
            $inisial = collect(explode(' ', $nama))->map(fn($w) => mb_substr($w, 0, 1))->take(2)->join('');
        @endphp
        <div class="avatar-bulat">{{ strtoupper($inisial) }}</div>
        <h4 class="fw-bold text-white mb-1" style="font-size: 20px;">{{ $nama }}</h4>
        <p class="text-white-50 fs-6 mb-0">Pemilik Kos Azelia</p>
    </div>

    <ul class="nav flex-column gap-2 mb-auto">
        <li class="nav-item"><a href="{{ route('pemilik.rekap') }}" class="nav-link {{ request()->routeIs('pemilik.rekap') ? 'active' : '' }}">Rekap</a></li>
        <li class="nav-item"><a href="{{ route('pemilik.belum_bayar') }}" class="nav-link {{ request()->routeIs('pemilik.belum_bayar*') ? 'active' : '' }}">Belum Bayar</a></li>
        <li class="nav-item"><a href="{{ route('pemilik.laporan') }}" class="nav-link {{ request()->routeIs('pemilik.laporan*') ? 'active' : '' }}">Laporan</a></li>
        <li class="nav-item"><a href="{{ route('pemilik.lainnya') }}" class="nav-link {{ request()->routeIs('pemilik.lainnya*') ? 'active' : '' }}">Lainnya</a></li>
    </ul>

    <div class="mt-4 pt-3 border-top border-white-50">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-pil-putih">Keluar</button>
        </form>
    </div>
</aside>