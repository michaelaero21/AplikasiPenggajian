@extends('layouts.absensi')

@section('title', 'Daftar Absensi Karyawan')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-center items-center space-x-4 mb-4" x-data="{
        month: '{{ $month }}',
        year: '{{ $year }}',
        karyawans: @json($karyawans),
        changeMonth(direction) {
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            let currentIndex = months.indexOf(this.month);
            currentIndex += direction;

            if (currentIndex < 0) {
                this.month = months[11];
                this.year--;
            } else if (currentIndex > 11) {
                this.month = months[0];
                this.year++;
            } else {
                this.month = months[currentIndex];
            }

            window.location.href = `?month=${this.month}&year=${this.year}`;
        },
        getDaysInMonth() {
            const date = new Date(this.year, new Date(Date.parse(this.month + ' 1, ' + this.year)).getMonth() + 1, 0);
            return Array.from({ length: date.getDate() }, (_, i) => i + 1);
        },
        getStatus(karyawan, day) {
            const absensi = karyawan.absensi.find(absen => new Date(absen.tanggal).getDate() === day);
            return absensi ? absensi.status : 'empty';
        },
        getColor(karyawan, day) {
            let status = this.getStatus(karyawan, day);
            if (status === 'empty') {
                return 'white';
            }
            // Jika status mengandung jam (format "07:00 - 16:00"), anggap hadir
            if (status.includes('-')) {
                return '#90ee90'; // hijau
            }
            return '#ff4d4d'; // merah untuk status selain itu
        }
    }">
        <button @click="changeMonth(-1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">←</button>
        <div class="text-lg font-semibold" x-text="month + ' ' + year"></div>
        <button @click="changeMonth(1)" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">→</button>
    </div>

    <div class="overflow-x-auto">
        <table class="table-auto w-full border border-gray-300 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1 text-center" rowspan="2">No</th>
                    <th class="border px-2 py-1 text-center" rowspan="2">Nama Karyawan</th>
                    <th class="border px-2 py-1 text-center" colspan="31">Hari / Tanggal</th>
                </tr>
                <tr>
                    <template x-for="day in getDaysInMonth()" :key="day">
                        <th class="border px-2 py-1 text-center" style="min-width: 40px;" x-text="day"></th>
                    </template>
                </tr>
            </thead>
            <tbody>
                <template x-for="(karyawan, index) in karyawans" :key="karyawan.id">
                    <tr>
                        <td class="border px-2 py-1 text-center" x-text="index + 1"></td>
                        <td class="border px-2 py-1" x-text="karyawan.nama"></td>
                        <template x-for="day in getDaysInMonth()" :key="day">
                            <td class="border px-2 py-1 text-center"
                                :style="{ backgroundColor: getColor(karyawan, day) }"
                                x-text="getStatus(karyawan, day) !== 'empty' ? getStatus(karyawan, day) : ''">
                            </td>
                        </template>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('absensi', () => ({
            getStatus(karyawan, day) {
                let absensi = karyawan.absensi.find(absen => new Date(absen.tanggal).getDate() === day);
                return absensi ? absensi.status : 'empty';
            },
            getColor(karyawan, day) {
                let status = this.getStatus(karyawan, day);
                return status === 'hadir' ? '#90ee90' : (status === 'absen' ? '#ff4d4d' : 'white');
            }
        }));
    });
</script>
@endsection
