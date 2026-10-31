<nav class="navbar navbar-expand navbar-light bg-white border-bottom px-4 py-3 shadow-sm">
    <div class="container-fluid px-0">
        <span class="navbar-brand fw-bold text-primary fs-4">Sistem Informasi Kos Azelia</span>
        <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle fs-5 text-dark fw-medium" href="#" role="button" data-bs-toggle="dropdown">
                    {{ auth()->user()->nama_lengkap ?? 'Pengguna' }} ({{ ucfirst(auth()->user()->role) }})
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-4 p-2">
                    <li><a class="dropdown-item py-2 fs-5 rounded-3" href="{{ route('password.change') }}">Ganti Kata Sandi</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item py-2 fs-5 text-danger rounded-3">Keluar</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>