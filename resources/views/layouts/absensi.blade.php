<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f0f0; margin: 0; padding: 0; }
        .sidebar {
            width: 250px; background: #333; color: white; height: 100vh;
            position: fixed; padding: 20px; transition: transform 0.3s; z-index: 100;
        }
        .sidebar h4 { text-align: center; font-size: 18px; margin-top: 10px; }
        .sidebar a { display: block; color: white; padding: 10px; text-decoration: none; margin: 5px 0; border-radius: 5px; }
        .sidebar a.active, .sidebar a:hover { background: #6c5ce7; }
        .profile-box { text-align: center; padding: 10px; border-radius: 10px; }
        .profile-box.active, .profile-box:hover { background-color: #6c5ce7; cursor: pointer; }
        .profile-box img, .profile-box i { display: block; margin: 0 auto; }
        .profile-box h4 { margin-top: 10px; color: white; }
        .logout {
            position: absolute; bottom: 20px; left: 20px; right: 20px;
            background: #e74c3c; text-align: center; padding: 10px; border-radius: 5px;
        }
        .logout a, .logout button { color: white; text-decoration: none; font-weight: bold; }
        .toggle-btn {
            display: none; position: absolute; left: 15px; top: 15px;
            background: #6c5ce7; color: white; border: none;
            padding: 8px 12px; border-radius: 5px; cursor: pointer; z-index: 1000;
        }
        .content { margin-left: 270px; padding: 20px; transition: margin-left 0.3s; }
        .table-container {
            background: white; padding: 20px; border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); overflow-x: auto;
        }
        .table th, .table td { text-align: center; padding: 10px; }
        .btn-edit, .btn-delete { padding: 5px 10px; border-radius: 5px; text-decoration: none; border: none; color: white; }
        .btn-edit { background: #3498db; }
        .btn-edit:hover { background: #2980b9; }
        .btn-delete { background: #e74c3c; }
        .btn-delete:hover { background: #c0392b; }
        .btn-add {
            background: #2ecc71; color: white; padding: 10px 15px;
            border-radius: 5px; text-decoration: none; border: none;
        }
        .btn-add:hover { background: #27ae60; }
        .search-box {
            display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;
        }
        .search-box input { flex: 1; padding: 8px; }
        .search-box button {
            background: #3498db; color: white; border: none;
            padding: 8px 15px; border-radius: 5px;
        }
        .search-box button:hover { background: #2980b9; }
        .dashboard-container { display: flex; gap: 20px; }
        .card { flex: 1; padding: 20px; border-radius: 10px; color: white; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); position: fixed; width: 250px; z-index: 999; }
            .content { margin-left: 0; }
            .toggle-btn { display: block; }
            .sidebar.show { transform: translateX(0); }
            .sidebar a { text-align: center; }
        }


        /* footer selalu diam di bawah layar dan tak terpengaruh scroll horizontal */
        .footer-custom {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(135deg,#0d47a1,#1976d2);
            color:#fff;
            padding:14px 20px;
            display:flex;
            justify-content:center;
            align-items:center;
            gap:.5rem;
            font-size:14px;
            font-weight:500;
            box-shadow:0 -4px 20px rgba(0,0,0,.1);
            z-index:10;
        }

        .logo-footer{
            width:40px;
            height:auto;
            object-fit:contain;
        }

    </style>
</head>
<body>
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="wrapper">
    <div class="sidebar">
        <div class="text-center mb-4 position-relative">
        @if (Auth::check())
            <a href="{{ route('profile.show') }}" class="profile-box {{ request()->is('profile*') ? 'active' : '' }}">
                @if (Auth::user()->profile_photo)
                    <img src="{{ asset('storage/profile_photos/' . Auth::user()->profile_photo) }}" alt="Foto Profil" class="rounded-circle" width="80" height="80" style="object-fit: cover; border: 2px solid #fff;">
                @else
                    <i class="bi bi-person-circle" style="font-size: 80px; color: #fff;"></i>
                @endif
                <h4>Hello, {{ Auth::user()->name }}!</h4>
            </a>
        @endif
        </div>

        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="fa fa-home"></i> Dashboard</a>
        <a href="{{ route('karyawan.index') }}" class="{{ request()->is('karyawan*') ? 'active' : '' }}"><i class="fa fa-users"></i> Karyawan</a>
        <a href="{{ route('absensi.index') }}" class="{{ request()->is('absensi*') ? 'active' : '' }}"><i class="fa fa-calendar-check"></i> Absensi</a>
        <a href="{{ route('gaji.index') }}" class="{{ request()->is('gaji*') || request()->is('slip-gaji*') ? 'active' : '' }}">
            <i class="fa fa-money-bill"></i> Gaji
        </a>

        <a href="{{ route('laporan.slip-gaji') }}" class="{{ request()->is('laporan*') ? 'active' : '' }}">
            <i class="fa fa-file-alt"></i> Laporan
        </a>

        <div class="logout">
            <form id="logout-form" action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="button" onclick="confirmLogout()" style="background: none; border: none; color: inherit;">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </button>
            </form>
        </div>
    </div>
    <div class="content">
        @yield('content')
       
    </div>
    
</div>
<footer class="footer-custom">
        <img src="{{ asset('images/logo.png') }}" class="logo-footer" />
        <span>&copy; {{ date('Y') }} CV Arindra Mandiri. All rights reserved.</span>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
    function confirmLogout() {
    if (confirm('Apakah kamu yakin ingin keluar?')) {
        document.getElementById('logout-form').submit();
    }
    }
</script>
@stack('scripts')
</body>
</html>
