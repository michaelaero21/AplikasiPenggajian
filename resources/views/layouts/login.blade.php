<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Aplikasi Penggajian')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom minimal style -->
    <style>
        body {
            background: #f6f7fb;
            font-family: "Inter", sans-serif;
        }
        .auth-wrapper {
            min-height: 100vh;
        }
        .auth-card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,.06);
        }
        .brand-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }
        .brand-name {
            font-weight: 600;
            letter-spacing: .3px;
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center align-items-center auth-wrapper">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="card auth-card p-4 p-sm-5">

                <!-- Brand -->
                <div class="text-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="brand-logo mb-2">
                    <h5 class="brand-name mb-0">CV Arindra Mandiri</h5>
                </div>

                <!-- Judul form -->
                <h3 class="text-center mb-4">@yield('form_title')</h3>

                <!-- Konten form -->
                @yield('content')

            </div>
        </div>
    </div>

    <!-- Bootstrap JS bundle (opsional, jika Anda butuh komponen JS‑nya) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
