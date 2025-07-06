@extends('layouts.karyawan')

@section('title', 'Dashboard Karyawan')

@section('content')
@php
    $selisihHari      = $selisihHari      ?? 0;
    $tanggalGajianIso = $tanggalGajianIso ?? now()->toIso8601String();
    $tanggalGajian    = $tanggalGajian    ?? now();
    $slipTerakhir = $slipTerakhir ?? null;
@endphp


<h2 class="mb-4"><strong>Dashboard Karyawan</strong></h2>

<div class="dashboard-container"
     style="display:flex;gap:1rem;flex-wrap:wrap;justify-content:space-between;">

    <!-- ====== CARD: Gaji Diterima ====== -->
    <div class="card"
         style="min-height:260px;flex:1 1 calc(50% - 1rem);display:flex;flex-direction:column;
                justify-content:space-between;padding:1.5rem;
                background:linear-gradient(135deg,#27ae60,#1e8449);color:white;
                border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <h2 style="font-size:2.2rem;font-weight:bold;margin:0;">
                Rp{{ number_format($slipTerakhir->total_dibayar ?? 0, 0, ',', '.') }}
            </h2>
            <i class="fas fa-wallet" style="font-size:48px;color:white;"></i>
        </div>
        <p style="font-size:1.3rem;font-weight:600;margin:0;">Gaji&nbsp;Diterima</p>
        <small style="font-size:1rem;font-weight:400;margin-top:10px;">
            Total gaji yang sudah masuk ke akun Anda
            @if($slipTerakhir)
                pada {{ \Carbon\Carbon::parse($slipTerakhir->updated_at)
                    ->setTimezone('Asia/Jakarta')
                    ->translatedFormat('d M Y H:i:s') }}.
            @else
                pada ­– belum ada data.
            @endif
        </small>
    </div>

    <!-- ====== CARD: Gajian Berikutnya ====== -->
    <div class="card"
         style="min-height:200px;flex:1 1 calc(50% - 1rem);display:flex;flex-direction:column;
                justify-content:space-between;padding:1.5rem;
                background:linear-gradient(135deg,#e67e22,#d35400);color:white;
                border-radius:8px;box-shadow:0 4px 10px rgba(0,0,0,0.1);">
        <p style="font-size:1.3rem;font-weight:600;margin:0;"><strong>Gajian Berikutnya</strong></p>

        <small style="font-size:1.1rem;font-weight:bold;background:white;
                      color:#000;padding:6px 10px;border-radius:5px;margin-top:10px;display:block;text-align:center;">
            @if($selisihHari === 0)
                <span style="color:#c0392b;">Hari ini hari gajian!</span>
            @elseif($selisihHari === 1)
                Gaji besok ({{ $tanggalGajian->format('d M Y') }})
            @else
                {{ $selisihHari }} hari lagi ({{ $tanggalGajian->format('d M Y') }})
            @endif
        </small>

        <div id="countdown"
             style="font-size:1.4rem;margin-top:10px;color:#ecf0f1;text-align:center;font-weight:bold;">
            {{ $selisihHari }} hari lagi.
        </div>
    </div>
</div>


{{-- ========== SCRIPT COUNTDOWN ========== --}}
<script>
    const countdownElement = document.getElementById('countdown');
    const targetTime       = new Date("{{ $tanggalGajianIso }}").getTime();

    function updateCountdown() {
        const now           = Date.now();
        const remainingTime = targetTime - now;

        if (remainingTime <= 0) {
            countdownElement.textContent = "Gaji sudah dibayar!";
            return;
        }

        const days    = Math.floor(remainingTime / (1000 * 60 * 60 * 24));
        const hours   = Math.floor((remainingTime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((remainingTime % (1000 * 60)) / 1000);

        countdownElement.textContent =
            `${days} hari ${hours} jam ${minutes} menit ${seconds} detik`;
    }
    updateCountdown();              // inisialisasi
    setInterval(updateCountdown, 1000);
</script>
@endsection
