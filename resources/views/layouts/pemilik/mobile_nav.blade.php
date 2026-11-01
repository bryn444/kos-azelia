<nav class="mobile-nav-kaca d-lg-none">
    <ul class="nav justify-content-around align-items-center mb-0">
        <li class="nav-item text-center"><a href="{{ route('pemilik.rekap') }}" class="nav-link {{ request()->routeIs('pemilik.rekap') ? 'active' : '' }}">Rekap</a></li>
        <li class="nav-item text-center"><a href="{{ route('pemilik.belum_bayar') }}" class="nav-link {{ request()->routeIs('pemilik.belum_bayar*') ? 'active' : '' }}">Belum Bayar</a></li>
        <li class="nav-item text-center"><a href="{{ route('pemilik.laporan') }}" class="nav-link {{ request()->routeIs('pemilik.laporan*') ? 'active' : '' }}">Laporan</a></li>
        <li class="nav-item text-center"><a href="{{ route('pemilik.lainnya') }}" class="nav-link {{ request()->routeIs('pemilik.lainnya*') ? 'active' : '' }}">Lainnya</a></li>
    </ul>
</nav>