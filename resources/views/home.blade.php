@extends('layouts.app')

@section('title', 'Dashboard')
@section('content')
    <h2 class="mb-4"><strong>Dashboard</strong></h2>

    @php
        $timezone = 'Asia/Jakarta';
        $today = \Carbon\Carbon::now($timezone)->startOfDay();
        $tanggalGajian = \Carbon\Carbon::create($today->year, $today->month, 1, 0, 0, 0, $timezone)->startOfDay();

        if ($today->gt($tanggalGajian)) {
            $tanggalGajian->addMonth();
        }

        $selisihHari = $today->diffInDays($tanggalGajian, false);
        $waktuGajian = $tanggalGajian->diffInSeconds($today, false);
        $countdown = gmdate("H:i:s", $waktuGajian);

        // Kirim waktu gajian dalam format ISO (UTC)
        $tanggalGajianIso = $tanggalGajian->toIso8601String();
    @endphp

    <div class="dashboard-container" style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: space-between;">
        <!-- Card Jumlah Karyawan -->
        <a href="{{ route('karyawan.index') }}" style="text-decoration: none; flex: 1 1 calc(50% - 1rem);">
            <div class="card card-purple" style="min-height: 260px; display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; background: linear-gradient(135deg, #6c5ce7, #4e44ad); color: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <h2 style="font-size: 2.5rem; font-weight: bold; margin: 0;">{{ $totalKaryawan }}</h2>
                    <i class="fas fa-users" style="font-size: 50px; color: white;"></i>
                </div>
                <p style="font-size: 1.3rem; font-weight: 600; margin: 0;">Jumlah Karyawan</p>
                <small class="text-white" style="font-size: 1rem; font-weight: 400; margin-top: 10px;">Jumlah total karyawan yang terdaftar dalam sistem perusahaan ini.</small>
                <div class="info" style="font-size: 1.2rem; font-weight: bold; color: white; background-color: #1c1c1c; padding: 6px 12px; border-radius: 5px; margin-top: 10px; text-align: center;">Info ➔</div>
            </div>
        </a>

        <!-- Card Pengingat Gaji -->
        <div class="card card-red" style="min-height: 200px; flex: 1 1 calc(50% - 1rem); display: flex; flex-direction: column; justify-content: space-between; padding: 1.5rem; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; border-radius: 8px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);">
            <p style="font-size: 1.3rem; font-weight: 600; margin: 0;"><strong>Pengingat Gaji</p></strong>
            <small class="text-white" style="font-size: 1.2rem; font-weight: bold; background-color: rgb(255, 255, 255); padding: 5px 10px; border-radius: 5px; margin-top: 10px; display: block; text-align: center;">
                @if($selisihHari === 0)
                    <span style="color:rgb(255, 25, 0);">Hari ini hari gajian!</span>
                @elseif($selisihHari === 1)
                    <span style="color:rgb(255, 136, 0);">Gaji bulanan dibayarkan besok ({{ $tanggalGajian->format('d M Y') }})</span>
                @else
                    <span style="color:rgb(0, 0, 0);">Gaji bulanan dibayarkan dalam {{ $selisihHari }} hari lagi ({{ $tanggalGajian->format('d M Y') }})</span>
                @endif
            </small>

            @if($selisihHari >= 0)
                <div id="countdown" style="font-size: 1.5rem; margin-top: 10px; color: #ecf0f1; text-align: center; font-weight: bold;">
                     {{ $selisihHari }} hari lagi.
                </div>
            @endif

            <div class="info" style="font-size: 1.2rem; font-weight: bold; color: white; background-color: #1c1c1c; padding: 6px 12px; border-radius: 5px; margin-top: 10px; text-align: center;">Info ➔</div>
        </div>
    </div>

    <script>
        const countdownElement = document.getElementById('countdown');
        
        // Gunakan waktu gajian yang dikirimkan dalam format ISO dari backend
        const targetTime = new Date("{{ $tanggalGajianIso }}").getTime();

        function updateCountdown() {
            const now = new Date().getTime();
            const remainingTime = targetTime - now;

            if (remainingTime <= 0) {
                countdownElement.innerHTML = "Gaji sudah dibayar!";
                return;
            }

            // Menghitung jumlah hari, jam, menit, dan detik
            const days = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
            const hours = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

            countdownElement.innerHTML = `${days} hari ${hours} jam ${minutes} menit ${seconds} detik`;
        }

        setInterval(updateCountdown, 1000);
    </script>
@endsection
