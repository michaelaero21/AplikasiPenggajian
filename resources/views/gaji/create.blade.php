@extends('layouts.app')

@section('title', 'Tambah Gaji Karyawan')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Tambah Data Gaji Karyawan</h5>
        </div>
        <div class="form-create-gaji p-4">
            <!-- Menampilkan Error Global -->
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('gaji.store') }}" method="POST" id="form-gaji">
                @csrf

                <div class="mb-3">
                    <label for="karyawan_id" class="form-label">Nama Karyawan:</label>
                    <select class="form-select @error('karyawan_id') is-invalid @enderror" name="karyawan_id" id="karyawan_id" required>
                        <option value="" disabled selected>Pilih Karyawan</option>
                        @foreach($karyawan as $kar)
                            <option value="{{ $kar->id }}" data-jabatan="{{ $kar->jabatan }}">{{ $kar->nama }}</option>
                        @endforeach
                    </select>
                    @error('karyawan_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="jabatan" class="form-label">Jabatan:</label>
                    <input type="text" class="form-control" id="jabatan" name="jabatan" readonly>
                </div>

                <div class="mb-3">
                    <label for="kategori_gaji" class="form-label">Kategori Gaji:</label>
                    <select class="form-select @error('kategori_gaji') is-invalid @enderror" name="kategori_gaji" id="kategori_gaji" required>
                        <option value="" disabled selected>Pilih Kategori</option>
                        <option value="Mingguan">Mingguan</option>
                        <option value="Bulanan">Bulanan</option>
                    </select>
                    @error('kategori_gaji')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    <div class="mb-3 {{ in_array($field, ['uang_transportasi', 'uang_lembur']) ? 'non-marketing-fields' : '' }}">
                        <label for="{{ $field }}" class="form-label">{{ $label }}:</label>
                        <input type="text" class="form-control @error($field) is-invalid @enderror" id="{{ $field }}_display" placeholder="Masukkan {{ $label }}" oninput="formatRupiah(this, '{{ $field }}')" {{ in_array($field, ['uang_transportasi', 'uang_lembur']) ? '' : 'required' }}>
                        <input type="hidden" name="{{ $field }}" id="{{ $field }}">
                        @error($field)
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach

                {{-- Field Tambahan Marketing --}}
                <div id="marketing-fields" class="d-none mt-4">
                    <h5>Komponen Tambahan untuk Marketing</h5>
                    <div class="mb-3">
                        <label for="tunjangan_sewa_transport" class="form-label">Tunjangan Sewa Transportasi:</label>
                        <input type="text" class="form-control" id="tunjangan_sewa_transport_display" placeholder="Masukkan Tunjangan Sewa Transportasi" oninput="formatRupiah(this, 'tunjangan_sewa_transport')">
                        <input type="hidden" name="tunjangan_sewa_transport" id="tunjangan_sewa_transport">
                    </div>

                    <div class="mb-3">
                        <label for="tunjangan_pulsa" class="form-label">Tunjangan Pulsa:</label>
                        <input type="text" class="form-control" id="tunjangan_pulsa_display" placeholder="Masukkan Tunjangan Pulsa" oninput="formatRupiah(this, 'tunjangan_pulsa')">
                        <input type="hidden" name="tunjangan_pulsa" id="tunjangan_pulsa">
                    </div>

                    <div class="mb-3">
                        <label for="omset_display" class="form-label">Omset Marketing :</label>
                        <input type="text" class="form-control" id="omset_display" placeholder="Masukkan Omset" oninput="formatRupiah(this, 'omset'); hitungInsentif();">
                        <input type="hidden" name="omset" id="omset">   
                    </div>

                    <div class="mb-3">
                        <label for="insentif" class="form-label">Insentif (Otomatis):</label>
                        <input type="text" class="form-control" id="insentif_display" readonly>
                        <input type="hidden" name="insentif" id="insentif">
                    </div>
                </div>

                <!-- Tombol Simpan dan Batal -->
                <div class="d-flex justify-content-start mt-3 gap-2">
                    <button type="submit" class="btn btn-primary">Simpan Gaji</button>
                    <a href="{{ route('gaji.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('karyawan_id').addEventListener('change', function () {
    var selectedOption = this.options[this.selectedIndex];
    var jabatan = selectedOption.getAttribute('data-jabatan');
    document.getElementById('jabatan').value = jabatan;

    if (jabatan && jabatan.toLowerCase() === 'marketing') {
        // Tampilkan field marketing
        document.getElementById('marketing-fields').classList.remove('d-none');

        // Sembunyikan hanya uang_transportasi
        document.querySelector('[for="uang_transportasi"]').closest('.mb-3').classList.add('d-none');
        
        // Tampilkan semua field lain
        document.querySelectorAll('.non-marketing-fields').forEach(el => {
            if (!el.querySelector('#uang_transportasi')) {
                el.classList.remove('d-none');
            }
        });
    } else {
        // Non-marketing: sembunyikan marketing field
        document.getElementById('marketing-fields').classList.add('d-none');

        // Tampilkan semua field umum (termasuk uang_transportasi)
        document.querySelectorAll('.non-marketing-fields').forEach(el => el.classList.remove('d-none'));
    }
});

    function formatRupiah(input, hiddenFieldId) {
        let value = input.value.replace(/\D/g, '');
        value = value ? parseInt(value) : 0;
        document.getElementById(hiddenFieldId).value = value;
        input.value = value ? "Rp " + value.toLocaleString("id-ID") : "";
    }

    document.getElementById('form-gaji').addEventListener('submit', function (e) {
        let requiredFields = ['gaji_pokok', 'uang_makan', 'asuransi', 'thr'];
        let jabatan = document.getElementById('jabatan').value.toLowerCase();

        if (jabatan !== 'marketing') {
            requiredFields.push('uang_transportasi', 'uang_lembur');
        } else {
            requiredFields.push('tunjangan_sewa_transport', 'tunjangan_pulsa');
        }

        for (let id of requiredFields) {
            let val = document.getElementById(id).value;
            if (!val || parseInt(val) === 0) {
                e.preventDefault();
                alert(`Field ${id.replaceAll('_', ' ')} wajib diisi!`);
                return false;
            }
        }
    });
    function hitungInsentif() {
    const omsetInput = document.getElementById('omset');
    const insentifInput = document.getElementById('insentif');
    const insentifDisplay = document.getElementById('insentif_display');

    let omset = parseFloat(omsetInput.value) || 0;
    let insentif = 0;

    if (omset >= 1000000) {
        insentif = omset * 0.002; // 0.2%
    } else {
        insentif = omset * 0.001; // 0.1%
    }

    insentifInput.value = Math.round(insentif);
    insentifDisplay.value = "Rp " + Math.round(insentif).toLocaleString("id-ID");
}

</script>
@endsection
