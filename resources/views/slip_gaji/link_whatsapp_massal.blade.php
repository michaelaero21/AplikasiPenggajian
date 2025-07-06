{{-- resources/views/slip_gaji/link_whatsapp_massal.blade.php --}}

@extends('layouts.app')

@section('title', 'Link WhatsApp Slip Gaji')

@section('content')
<div class="container my-4">
    <h3 class="mb-3">Link WhatsApp Slip Gaji</h3>

    {{-- pesan error dari controller (jika ada) --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if(count($links))
        <div class="d-flex justify-content-between mb-2">
            <button id="copy-all" class="btn btn-sm btn-outline-secondary">
                Salin Semua Link
            </button>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">
                ← Kembali
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="links-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th>Nama Karyawan</th>
                        <th>Link WhatsApp</th>
                        <th style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($links as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $row['nama'] }}</td>
                            <td class="text-break">
                                {{-- input readonly agar mudah disalin --}}
                                <input type="text" class="form-control form-control-sm link-input"
                                       readonly value="{{ $row['link'] }}">
                            </td>
                            <td class="text-center">
                                <a href="{{ $row['link'] }}" target="_blank"
                                   class="btn btn-sm btn-success mb-1">
                                    Buka&nbsp;WA
                                </a>
                                <button class="btn btn-sm btn-info copy-one">
                                    Salin
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p>Tidak ada link WhatsApp yang tersedia.</p>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // helper: salin ke clipboard
    const copyToClipboard = text =>
        navigator.clipboard.writeText(text)
            .then(() => window.toastr
                ? toastr.success('Link disalin!')
                : alert('Link disalin!'));

    // tombol "Salin" per baris
    document.querySelectorAll('.copy-one').forEach(btn => {
        btn.addEventListener('click', e => {
            const input = e.target.closest('tr').querySelector('.link-input');
            copyToClipboard(input.value);
        });
    });

    // tombol "Salin Semua Link"
    const btnAll = document.getElementById('copy-all');
    if (btnAll) {
        btnAll.addEventListener('click', () => {
            const allLinks = Array.from(document.querySelectorAll('.link-input'))
                                  .map(i => i.value)
                                  .join('\n');
            copyToClipboard(allLinks);
        });
    }
});
</script>
@endpush
