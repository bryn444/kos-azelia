<nav class="mobile-bottom-nav d-lg-none bg-white border-top fixed-bottom py-2 px-3 shadow">
    <ul class="nav justify-content-around align-items-center mb-0">
        @if(auth()->user()->role === 'pengelola')
            <li class="nav-item text-center"><a href="{{ route('pengelola.beranda') }}" class="nav-link fs-6 {{ request()->routeIs('pengelola.beranda') ? 'text-primary fw-bold' : 'text-dark' }}">Beranda</a></li>
            <li class="nav-item text-center"><a href="{{ route('pengelola.penghuni') }}" class="nav-link fs-6 {{ request()->routeIs('pengelola.penghuni*') ? 'text-primary fw-bold' : 'text-dark' }}">Penghuni</a></li>
            <li class="nav-item text-center"><a href="{{ route('pengelola.kamar') }}" class="nav-link fs-6 {{ request()->routeIs('pengelola.kamar*') ? 'text-primary fw-bold' : 'text-dark' }}">Kamar</a></li>
            <li class="nav-item text-center"><a href="{{ route('pengelola.pembayaran') }}" class="nav-link fs-6 {{ request()->routeIs('pengelola.pembayaran*') ? 'text-primary fw-bold' : 'text-dark' }}">Pembayaran</a></li>
            <li class="nav-item text-center"><a href="{{ route('pengelola.lainnya') }}" class="nav-link fs-6 {{ request()->routeIs('pengelola.lainnya*') ? 'text-primary fw-bold' : 'text-dark' }}">Lainnya</a></li>
        @elseif(auth()->user()->role === 'pemilik')
            <li class="nav-item text-center"><a href="{{ route('pemilik.rekap') }}" class="nav-link fs-6 {{ request()->routeIs('pemilik.rekap') ? 'text-primary fw-bold' : 'text-dark' }}">Rekap</a></li>
            <li class="nav-item text-center"><a href="{{ route('pemilik.belum_bayar') }}" class="nav-link fs-6 {{ request()->routeIs('pemilik.belum_bayar*') ? 'text-primary fw-bold' : 'text-dark' }}">Belum Bayar</a></li>
            <li class="nav-item text-center"><a href="{{ route('pemilik.laporan') }}" class="nav-link fs-6 {{ request()->routeIs('pemilik.laporan*') ? 'text-primary fw-bold' : 'text-dark' }}">Laporan</a></li>
            <li class="nav-item text-center"><a href="{{ route('pemilik.lainnya') }}" class="nav-link fs-6 {{ request()->routeIs('pemilik.lainnya*') ? 'text-primary fw-bold' : 'text-dark' }}">Lainnya</a></li>
        @endif
    </ul>
</nav>