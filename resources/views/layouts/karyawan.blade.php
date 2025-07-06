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
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 250px;
            background: #333;
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar h4 {
            text-align: center;
            font-size: 18px;
            margin-top: 10px;
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            margin: 5px 0;
            border-radius: 5px;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: #6c5ce7;
        }

        .profile-box {
            text-align: center;
            padding: 10px;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .profile-box.active,
        .profile-box:hover {
            background-color: #6c5ce7;
            cursor: pointer;
        }

        .profile-box img,
        .profile-box i {
            display: block;
            margin: 0 auto;
        }

        .profile-box h4 {
            margin-top: 10px;
            color: white;
        }

        .logout {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: #e74c3c;
            text-align: center;
            padding: 10px;
            border-radius: 5px;
        }

        .logout a {
            color: white;
            text-decoration: none;
            font-weight: bold;
        }

        .toggle-btn {
            display: none;
            position: absolute;
            left: 15px;
            top: 15px;
            background: #6c5ce7;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }

        .content {
            margin-left: 270px;
            padding: 20px;
            transition: margin-left 0.3s ease-in-out;
        }

        .form-label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        .btn-edit, .btn-delete {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
            color: white;
        }

        .btn-edit {
            background: #3498db;
        }

        .btn-edit:hover {
            background: #2980b9;
        }

        .btn-delete {
            background: #e74c3c;
        }

        .btn-delete:hover {
            background: #c0392b;
        }

        .btn-add {
            background: #2ecc71;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            border: none;
        }

        .btn-add:hover {
            background: #27ae60;
        }

        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .search-box input {
            flex: 1;
            padding: 8px;
        }

        .search-box button {
            background: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
        }

        .search-box button:hover {
            background: #2980b9;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .dashboard-container {
            display: flex;
            gap: 20px;
        }

        .card {
            flex: 1;
            padding: 20px;
            border-radius: 10px;
            color: white;
        }
        .form-create-gaji, 
        .form-tambah-karyawan,
        .form-edit-karyawan, .form-edit-gaji{
            color: black;
        } 
        

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                width: 250px;
                height: 100vh;
            }

            .content {
                margin-left: 0;
            }

            .toggle-btn {
                display: block;
            }

            .sidebar.show {
                transform: translateX(0);
            }
        }
    </style>
     <style>
     html, body {
        height: 100%;
        margin: 0;
        }

    .wrapper {
        display: flex;
        height: 100vh;
        }

    .main {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        min-height: 100vh;
        background: #f0f0f0;
        }

    .content {
        flex-grow: 1;
        padding: 20px;
        }

    .footer-custom {
        background: linear-gradient(135deg, var(--brand-start, #0d47a1), var(--brand-end, #1976d2));
        color: #fff;
        padding: 14px 20px;
        display: flex;
        justify-content: center;

        align-items: center;
        gap: 0.5rem;
        font-size: 14px;
        font-weight: 500;
        
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
        }

    .logo-footer {
        width: 40px;
        height: auto;
        object-fit: contain;
        }

    @media (max-width: 768px) {
        .wrapper {
            flex-direction: column;
        }

        .sidebar {
            width: 100%;
            height: auto;
        }

        .main {
            margin-left: 0;
        }

        .footer-custom {
            flex-wrap: wrap;
            text-align: center;
        }
    }

    </style>
</head>
<body>

<!-- Tombol Toggle Sidebar (Mobile) -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="wrapper">
    <div class="sidebar">
        <div class="text-center mb-4 position-relative">
        @if (Auth::check())
            <a href="{{ route('profile.show.karyawan') }}" class="profile-box {{ request()->is('profile*') ? 'active' : '' }}">
                @if (Auth::user()->profile_photo)
                    <img src="{{ asset('storage/profile_photos/' . Auth::user()->profile_photo) }}" alt="Foto Profil" class="rounded-circle" width="80" height="80" style="object-fit: cover; border: 2px solid #fff;">
                @else
                    <i class="bi bi-person-circle" style="font-size: 80px; color: #fff;"></i>
                @endif
                <h4>Hello, {{ Auth::user()->name }}!</h4>
            </a>
        @endif
        </div>

        <a href="{{ route('karyawan.dashboard') }}" class="{{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
            <i class="fa fa-home"></i> Dashboard
        </a>
        <a href="{{ route('slip-gaji.karyawan') }}" class="{{ request()->is('slip-gaji*') ? 'active' : '' }}">
            <i class="fa fa-money-bill-wave"></i> Riwayat Gaji
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

   <main class="main">
    <section class="content">
      @yield('content')
    </section>

    <footer class="footer-custom">
      <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-footer" />
      <span>&copy; {{ date('Y') }} CV Arindra Mandiri. All rights reserved.</span>
    </footer>
  </main>
</div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('show');
    }
    function confirmLogout() {
    if (confirm('Apakah kamu yakin ingin keluar?')) {
        document.getElementById('logout-form').submit();
    }
}
</script>

</body>
</html>