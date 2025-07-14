@extends('layouts.app')

@section('title', 'Edit Gaji Karyawan')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-white">
            <h5 class="mb-0">Edit Gaji Karyawan</h5>
        </div>
        <div class="form-edit-gaji p-4">
            <form action="{{ route('gaji.update', $gajiKaryawan->id) }}" method="POST" id="form-gaji">
                @csrf
                @method('PUT')

                {{-- Nama Karyawan --}}
                <div class="mb-3">
                    <label for="karyawan_id" class="form-label">Nama Karyawan:</label>
                    <select class="form-select" name="karyawan_id" id="karyawan_id" required>
                        <option value="" disabled>Pilih Karyawan</option>
                        @foreach($karyawan as $kar)
                            <option value="{{ $kar->id }}" data-jabatan="{{ $kar->jabatan }}" {{ $kar->id == $gajiKaryawan->karyawan_id ? 'selected' : '' }}>
                                {{ $kar->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jabatan --}}
                <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan:</label>
                    <input type="text" class="form-control" id="jabatan" name="jabatan" value="{{ $gajiKaryawan->karyawan->jabatan }}" readonly>
                </div>

                {{-- Kategori Gaji --}}
                <div class="mb-3">
                    <label for="kategori_gaji" class="form-label">Kategori Gaji:</label>
                    <select class="form-select" name="kategori_gaji" id="kategori_gaji" required>
                        <option value="" disabled>Pilih Kategori</option>
                        <option value="Mingguan" {{ $gajiKaryawan->kategori_gaji == 'Mingguan' ? 'selected' : '' }}>Mingguan</option>
                        <option value="Bulanan" {{ $gajiKaryawan->kategori_gaji == 'Bulanan' ? 'selected' : '' }}>Bulanan</option>
                    </select>
                </div>

                {{-- Komponen Gaji Umum --}}
                @php
                    $fields = [
                        'gaji_pokok' => 'Gaji Pokok',
                        'uang_makan' => 'Uang Makan',
                        'asuransi' => 'Asuransi BPJS',
                        'uang_transportasi' => 'Uang Transportasi',
                        'uang_lembur' => 'Uang Lembur',
                        'thr' => 'THR',
                    ];
                @endphp

                @foreach($fields as $field => $label)
                    <div class="mb-3 field-group {{ $field === 'uang_transportasi' ? 'non-marketing' : '' }}">
                        <label for="{{ $field }}" class="form-label">{{ $label }}:</label>
                        <input type="text" class="form-control" id="{{ $field }}_display" oninput="formatRupiah(this, '{{ $field }}')" value="{{ number_format($gajiKaryawan->$field, 0, ',', '.') }}">
                        <input type="hidden" name="{{ $field }}" id="{{ $field }}" value="{{ $gajiKaryawan->$field }}">
                    </div>
                @endforeach

                {{-- Komponen Khusus Marketing --}}
                <div id="marketing-fields" class="mt-4 {{ strtolower($gajiKaryawan->karyawan->jabatan) == 'marketing' ? '' : 'd-none' }}">
                    <h5>Komponen Tambahan untuk Marketing</h5>
                    <div class="mb-3 field-group marketing">
                    <label for="tunjangan_sewa_transport_display" class="form-label">Tunjangan Sewa Transportasi:</label>
                    <input type="text" class="form-control" id="tunjangan_sewa_transport_display" oninput="formatRupiah(this, 'tunjangan_sewa_transport')" value="{{ number_format($gajiKaryawan->tunjangan_sewa_transport, 0, ',', '.') }}">
                    <input type="hidden" name="tunjangan_sewa_transport" id="tunjangan_sewa_transport" value="{{ $gajiKaryawan->tunjangan_sewa_transport }}">
                </div>

                <div class="mb-3 field-group marketing">
                    <label for="tunjangan_pulsa_display" class="form-label">Tunjangan Pulsa:</label>
                    <input type="text" class="form-control" id="tunjangan_pulsa_display" oninput="formatRupiah(this, 'tunjangan_pulsa')" value="{{ number_format($gajiKaryawan->tunjangan_pulsa, 0, ',', '.') }}">
                    <input type="hidden" name="tunjangan_pulsa" id="tunjangan_pulsa" value="{{ $gajiKaryawan->tunjangan_pulsa }}">
                </div>

                <div class="mb-3 field-group marketing">
                    <label for="omset_display" class="form-label">Omset Marketing :</label>
                    <input type="text" class="form-control" id="omset_display" placeholder="Masukkan Omset" oninput="formatRupiah(this, 'omset'); hitungInsentif();" value="{{ number_format($gajiKaryawan->omset ?? 0, 0, ',', '.') }}">
                    <input type="hidden" name="omset" id="omset" value="{{ $gajiKaryawan->omset ?? 0 }}">
                </div>

                <div class="mb-3 field-group marketing">
                    <label for="insentif_display" class="form-label">Insentif (Otomatis):</label>
                    <input type="text" class="form-control" id="insentif_display" readonly value="{{ 'Rp ' . number_format($gajiKaryawan->insentif, 0, ',', '.') }}">
                    <input type="hidden" name="insentif" id="insentif" value="{{ $gajiKaryawan->insentif }}">
                </div>
                </div>

                <div class="d-flex justify-content-start mt-4 gap-2">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('gaji.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
    const jabatanInput = document.getElementById('jabatan');
    const marketingFields = document.getElementById('marketing-fields');

    document.getElementById('karyawan_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const jabatan = selected.getAttribute('data-jabatan') || '';
        jabatanInput.value = jabatan;

        toggleFields(jabatan.toLowerCase());
    });

    function formatRupiah(input, hiddenId) {
        let angka = input.value.replace(/[^\d]/g, '');
        let nilai = angka ? parseInt(angka) : 0;
        document.getElementById(hiddenId).value = nilai;
        input.value = nilai ? 'Rp ' + nilai.toLocaleString('id-ID') : '';
    }

    function toggleFields(jabatan) {
        if (jabatan === 'marketing') {
            marketingFields.classList.remove('d-none');
            document.querySelectorAll('.non-marketing').forEach(el => el.classList.add('d-none'));
        } else {
            marketingFields.classList.add('d-none');
            document.querySelectorAll('.non-marketing').forEach(el => el.classList.remove('d-none'));
        }
    }

    document.getElementById('form-gaji').addEventListener('submit', function (e) {
        let jabatan = jabatanInput.value.toLowerCase();
        let required = ['gaji_pokok', 'uang_makan', 'asuransi', 'thr'];

        if (jabatan === 'marketing') {
            required.push('tunjangan_sewa_transport', 'tunjangan_pulsa', 'insentif');
        } else {
            required.push('uang_transportasi', 'uang_lembur');
        }

        for (let field of required) {
            let valStr = document.getElementById(field).value;
            if (!valStr || isNaN(parseInt(valStr))) {
                e.preventDefault();
                alert("Field " + field.replaceAll('_', ' ') + " wajib diisi!");
                return false;
            }
        }
    });

    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[id$="_display"]').forEach(input => {
            const hidden = input.id.replace('_display', '');
            formatRupiah(input, hidden);
        });

        toggleFields(jabatanInput.value.toLowerCase());
    });
    function hitungInsentif() {
    const omset = parseInt(document.getElementById('omset').value) || 0;
    const insentifInput = document.getElementById('insentif');
    const insentifDisplay = document.getElementById('insentif_display');

    let insentif = omset >= 1000000 ? omset * 0.002 : omset * 0.001;
    let rounded = Math.round(insentif);

    insentifInput.value = rounded;
    insentifDisplay.value = "Rp " + rounded.toLocaleString("id-ID");
}

</script>
@endsection
