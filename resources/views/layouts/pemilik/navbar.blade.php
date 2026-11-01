<nav class="navbar-kaca d-flex justify-content-between align-items-center">
    <div>
        <h3 class="fw-bold text-white mb-0 fs-3">Panel Pemilik Kos Azelia</h3>
        <p class="text-white-50 fs-6 mb-0">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</p>
    </div>
    
    <div class="dropdown">
        <button class="btn btn-light dropdown-toggle fs-5 px-4 py-2 rounded-pill fw-semibold" type="button" data-bs-toggle="dropdown">
            {{ auth()->user()->nama_lengkap }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-2 mt-2">
            <li><a class="dropdown-item py-2 fs-5 rounded-3" href="{{ route('password.change') }}">Ganti Kata Sandi</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item py-2 fs-5 text-danger rounded-3">Keluar</button>
                </form>
            </li>
        </ul>
    </div>
</nav>