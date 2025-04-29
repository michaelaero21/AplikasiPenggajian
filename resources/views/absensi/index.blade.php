@extends('layouts.absensi')
@section('title', 'Daftar Absensi Karyawan')
@section('content')
<div class="container mx-auto p-4">

    <!-- Menyisipkan Tahun dan Bulan ke dalam Alpine.js -->
    <div class="flex justify-center items-center space-x-4 mb-4" x-data="{ 
        month: '{{ now()->format('F') }}', 
        year: '{{ $year ?? now()->year }}' 
    }">
        <button @click="changeMonth(-1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">←</button>
        <div class="text-lg font-semibold" x-text="month + ' ' + year"></div>
        <button @click="changeMonth(1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">→</button>
    </div>

    <!-- Upload File -->
    <div class="flex justify-center mb-4">
        <form action="{{ route('absensi.upload') }}" method="POST" enctype="multipart/form-data" class="flex space-x-2">
            @csrf
            <input type="file" name="file" accept=".xlsx, .xls, .csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Upload</button>
        </form>
    </div>

    <!-- Tabel Absensi -->
    <div class="overflow-x-auto">
        <table class="table-auto w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 text-center" rowspan="2">No</th>
                    <th class="border px-2 py-1 text-center" rowspan="2">Nama Karyawan</th>
                    <th class="border px-2 py-1 text-center" colspan="31">Hari/Tanggal</th>
                </tr>
                <!-- Baris kedua adalah untuk header hari/tanggal -->
                <tr>
                    @for ($i = 1; $i <= 31; $i++)
                        <th class="border px-2 py-1 text-center" style="min-width: 40px;">{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach ($karyawans as $index => $karyawan)
                    <tr>
                        <td class="border px-2 py-1 text-center">{{ $index + 1 }}</td>
                        <td class="border px-2 py-1">{{ $karyawan->nama }}</td>
                        @for ($i = 1; $i <= 31; $i++)
                            @php
                                $status = $karyawan->absensi[$i] ?? 'empty'; // Status hadir/absen kosong
                                // Background color based on status
                                $color = $status == 'hadir' ? '#90ee90' : ($status == 'absen' ? '#ff4d4d' : 'white');
                            @endphp
                            <td class="border px-2 py-1 text-center" style="background-color: {{ $color }}"></td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Alpine.js for month change -->
<script src="//unpkg.com/alpinejs" defer>
    function changeMonth(direction) {
        if (direction === -1 && month === 'January') { 
            month = 'December'; year--; 
        } else if (direction === 1 && month === 'December') { 
            month = 'January'; year++; 
        } else {
            month = new Date(year, new Date(Date.parse(month + ' 1, ' + year)).getMonth() + direction).toLocaleString('default', { month: 'long' });
        }
    }
</script>
@endsection
