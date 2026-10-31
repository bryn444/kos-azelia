<aside class="sidebar-desktop d-none d-lg-block bg-white border-end p-4">
    <div class="sidebar-brand mb-4 text-center">
        <h3 class="fw-bold text-primary">KOS AZELIA</h3>
        <p class="text-muted fs-6">Panel {{ ucfirst(auth()->user()->role) }}</p>
    </div>
    <ul class="nav flex-column gap-2">
        @if(auth()->user()->role === 'pengelola')
            <li class="nav-item"><a href="{{ route('pengelola.beranda') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pengelola.beranda') ? 'active' : '' }}">Beranda</a></li>
            <li class="nav-item"><a href="{{ route('pengelola.penghuni') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pengelola.penghuni*') ? 'active' : '' }}">Penghuni</a></li>
            <li class="nav-item"><a href="{{ route('pengelola.kamar') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pengelola.kamar*') ? 'active' : '' }}">Kamar</a></li>
            <li class="nav-item"><a href="{{ route('pengelola.pembayaran') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pengelola.pembayaran*') ? 'active' : '' }}">Pembayaran</a></li>
            <li class="nav-item"><a href="{{ route('pengelola.lainnya') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pengelola.lainnya*') ? 'active' : '' }}">Lainnya</a></li>
        @elseif(auth()->user()->role === 'pemilik')
            <li class="nav-item"><a href="{{ route('pemilik.rekap') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pemilik.rekap') ? 'active' : '' }}">Rekap</a></li>
            <li class="nav-item"><a href="{{ route('pemilik.belum_bayar') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pemilik.belum_bayar*') ? 'active' : '' }}">Belum Bayar</a></li>
            <li class="nav-item"><a href="{{ route('pemilik.laporan') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pemilik.laporan*') ? 'active' : '' }}">Laporan</a></li>
            <li class="nav-item"><a href="{{ route('pemilik.lainnya') }}" class="nav-link fs-5 py-3 px-4 rounded-3 {{ request()->routeIs('pemilik.lainnya*') ? 'active' : '' }}">Lainnya</a></li>
        @endif
    </ul>
</aside>