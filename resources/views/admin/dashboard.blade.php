@extends('layouts.app')

@section('title', 'Admin Panel & Monitoring — Siap Siaga PMR')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 space-y-6">
    
    <!-- Top Header Title & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pusat Kendali & Operasional PMR</h1>
            <p class="text-xs text-slate-500 font-medium">Monitoring darurat, manajemen lokasi sekolah & penjadwalan piket PMR</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.export-report') }}" target="_blank" class="px-4 py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs flex items-center gap-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <span>Cetak Rekap Medis</span>
            </a>
        </div>
    </div>

    @if(session('status'))
    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center gap-2">
        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('status') }}</span>
    </div>
    @endif

    <!-- 1. Stat Cards Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Emergency</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-slate-900">{{ $stats['total_emergencies'] }}</span>
                <span class="text-xs font-bold text-slate-500">Laporan</span>
            </div>
        </div>

        <div class="bg-red-50 p-5 rounded-xl border-2 border-red-200">
            <span class="text-[11px] font-bold text-red-600 uppercase tracking-wider">Emergency Aktif</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-red-600">{{ $stats['active_emergencies'] }}</span>
                <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 text-[10px] font-black">LIVE</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kasus Resolved</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-emerald-600">{{ $stats['resolved_emergencies'] }}</span>
                <span class="text-xs font-bold text-emerald-700">Tertangani</span>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">PMR Piket Hari Ini</span>
            <div class="flex items-baseline justify-between mt-2">
                <span class="text-3xl font-black text-slate-900">{{ $stats['on_duty_pmr'] }} / {{ $stats['total_pmr'] }}</span>
                <span class="text-xs font-bold text-slate-500">On Duty</span>
            </div>
        </div>
    </div>

    <!-- 2. Interactive Admin Emergency & PMR Dispatch Map -->
    <div class="bg-white rounded-xl p-5 border border-slate-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="font-black text-base text-slate-900">🗺️ Peta Sebaran Tanggap Darurat & Posisi Petugas PMR</h2>
                <p class="text-xs text-slate-500">Visualisasi titik kumpul sekolah, insiden darurat aktif, dan sebaran posisi anggota PMR</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-bold">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-red-600 inline-block"></span> Insiden Aktif</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-600 inline-block"></span> PMR Available</span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block"></span> Lokasi Sekolah</span>
            </div>
        </div>

        <div id="admin_school_map" class="w-full h-80 rounded-lg border border-slate-200"></div>
    </div>

            <!-- Filter buttons -->
            <div class="flex items-center gap-1.5 overflow-x-auto text-xs font-bold">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-lg border {{ !request()->filled('status') ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    Semua
                </a>
                <a href="{{ route('admin.dashboard', ['status' => 'on_the_way']) }}" class="px-3 py-1.5 rounded-lg border {{ request('status') === 'on_the_way' ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    On The Way
                </a>
                <a href="{{ route('admin.dashboard', ['status' => 'handling']) }}" class="px-3 py-1.5 rounded-lg border {{ request('status') === 'handling' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    Handling
                </a>
                <a href="{{ route('admin.dashboard', ['status' => 'resolved']) }}" class="px-3 py-1.5 rounded-lg border {{ request('status') === 'resolved' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                    Resolved
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-400 font-black uppercase text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">Kode & Waktu</th>
                        <th class="px-6 py-4">Pelapor</th>
                        <th class="px-6 py-4">Jenis Kejadian</th>
                        <th class="px-6 py-4">Lokasi</th>
                        <th class="px-6 py-4">Petugas PMR</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($emergencies as $item)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4 font-mono">
                            <span class="font-bold text-slate-900">#{{ $item->emergency_code }}</span>
                            <div class="text-[11px] text-slate-400">{{ $item->reported_at->format('d/m/y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-slate-900">
                            {{ $item->reporter->name }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-md text-[11px] font-extrabold uppercase {{ $item->incident_type === 'kecelakaan' || $item->incident_type === 'cedera' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-800' }}">
                                {{ $item->incident_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-800">
                            {{ $item->location_display }}
                        </td>
                        <td class="px-6 py-4">
                            @if($item->activeAssignment && $item->activeAssignment->pmrUser)
                                <span class="font-bold text-slate-800">{{ $item->activeAssignment->pmrUser->name }}</span>
                                <span class="text-[10px] text-slate-400 block font-mono">Score: {{ $item->activeAssignment->dispatch_score }}%</span>
                            @else
                                <span class="text-slate-400 italic">Belum Ada Petugas</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold {{ $item->status === 'resolved' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700' }}">
                                {{ strtoupper(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('student.emergency.show', $item->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 italic">Tidak ada insiden darurat yang sesuai filter.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 3. Dua Kolom Manajemen: Master Lokasi Sekolah & Anggota / Jadwal Piket PMR -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Kolom Kiri: Manajemen Master Lokasi Sekolah -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-base text-slate-900">Master Lokasi Sekolah</h3>
                    <p class="text-xs text-slate-500">Titik preset area sekolah untuk laporan cepat siswa</p>
                </div>
            </div>

            <!-- Form Tambah Lokasi Baru -->
            <form action="{{ route('admin.locations.store') }}" method="POST" class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-3">
                @csrf
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 block">Tambah Titik Lokasi Baru</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <input type="text" name="name" required placeholder="Nama Lokasi (misal: UKS Gedung A)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                    <input type="text" name="building" placeholder="Gedung / Lantai (misal: Lantai 1)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="latitude" placeholder="Latitude (opsional)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:outline-none">
                    <input type="text" name="longitude" placeholder="Longitude (opsional)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:outline-none">
                </div>
                <button type="submit" class="w-full py-2.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs transition-colors">
                    + Simpan Titik Lokasi
                </button>
            </form>

            <!-- List Lokasi Sekolah -->
            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @foreach($locations as $loc)
                <div class="p-3 rounded-lg border border-slate-200 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div>
                        <h4 class="font-bold text-xs text-slate-900">📍 {{ $loc->name }}</h4>
                        <span class="text-[11px] text-slate-500">{{ $loc->building ?? 'Area Sekolah' }} • {{ $loc->emergencies_count }} Kali Dilaporkan</span>
                    </div>

                    <form action="{{ route('admin.locations.destroy', $loc->id) }}" method="POST" onsubmit="return confirm('Hapus lokasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-md transition-colors" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Kolom Kanan: Manajemen Anggota PMR & Jadwal Piket -->
        <div class="bg-white rounded-xl border border-slate-200 p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-black text-base text-slate-900">Anggota PMR & Jadwal Piket</h3>
                    <p class="text-xs text-slate-500">Atur ketersediaan dan status on-duty petugas</p>
                </div>
            </div>

            <!-- Form Tambah Anggota PMR Baru -->
            <form action="{{ route('admin.pmr-members.store') }}" method="POST" class="p-4 rounded-lg bg-slate-50 border border-slate-200 space-y-3">
                @csrf
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500 block">Tambah Anggota PMR Baru</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <input type="text" name="name" required placeholder="Nama Lengkap" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    <input type="text" name="nisn_or_member_id" required placeholder="No ID Anggota / NISN PMR" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="phone_number" required placeholder="No WhatsApp / HP" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:outline-none">
                    <input type="text" name="class_grade" required placeholder="Kelas (misal: XI MIPA 2)" class="px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:outline-none">
                </div>
                <input type="password" name="password" required placeholder="Password Login Petugas (Min 6 karakter)" class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs font-medium focus:outline-none">

                <button type="submit" class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition-colors">
                    + Daftarkan Petugas PMR
                </button>
            </form>

            <!-- List Anggota PMR & Toggle Piket -->
            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @foreach($pmrMembers as $pmr)
                <div class="p-3 rounded-lg border border-slate-200 flex items-center justify-between hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold flex items-center justify-center text-xs">
                            PMR
                        </div>
                        <div>
                            <h4 class="font-bold text-xs text-slate-900">{{ $pmr->name }}</h4>
                            <p class="text-[11px] text-slate-500">{{ $pmr->pmrProfile->nisn_or_member_id ?? 'PMR' }} • {{ $pmr->pmrProfile->class_grade ?? 'XI' }} • <strong class="text-slate-800 uppercase">{{ $pmr->pmrProfile->availability_status ?? 'available' }}</strong></p>
                        </div>
                    </div>

                    <form action="{{ route('admin.pmr-members.toggle-duty', $pmr->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-[11px] font-black transition-colors {{ $pmr->pmrProfile && $pmr->pmrProfile->is_on_duty ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">
                            {{ $pmr->pmrProfile && $pmr->pmrProfile->is_on_duty ? 'ON PIKET' : 'OFF PIKET' }}
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Inisialisasi Map Admin
    const adminMap = L.map('admin_school_map').setView([-6.20020000, 106.81680000], 18);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(adminMap);

    // 1. Render Titik Master Lokasi Sekolah (Marker Biru — kotak)
    const schoolLocations = @json($locations);
    schoolLocations.forEach(loc => {
        if (loc.latitude && loc.longitude) {
            const blueIcon = L.divIcon({
                className: 'custom-loc-icon',
                html: `<div style="background-color:#2563eb; width:12px; height:12px; border-radius:2px; border:2px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [12, 12],
                iconAnchor: [6, 6]
            });
            L.marker([loc.latitude, loc.longitude], { icon: blueIcon }).addTo(adminMap)
                .bindPopup(`<strong>🏫 ${loc.name}</strong><br>${loc.building ?? 'Area Sekolah'}`);
        }
    });

    // 2. Render Anggota PMR yang Aktif (Marker Hijau — kotak)
    const pmrList = @json($pmrMembers);
    pmrList.forEach(pmr => {
        if (pmr.pmr_profile && pmr.pmr_profile.current_latitude && pmr.pmr_profile.current_longitude) {
            const greenIcon = L.divIcon({
                className: 'custom-pmr-icon',
                html: `<div style="background-color:#16a34a; width:14px; height:14px; border-radius:2px; border:2px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });
            L.marker([pmr.pmr_profile.current_latitude, pmr.pmr_profile.current_longitude], { icon: greenIcon }).addTo(adminMap)
                .bindPopup(`<strong>🩹 Petugas: ${pmr.name}</strong><br>Status: ${pmr.pmr_profile.availability_status.toUpperCase()}<br>Piket: ${pmr.pmr_profile.is_on_duty ? 'ON' : 'OFF'}`);
        }
    });

    // 3. Render Insiden Darurat Aktif (Marker Merah — kotak, tanpa animasi CSS)
    const activeEmergencies = @json($emergencies->whereNotIn('status', ['resolved', 'cancelled'])->values());
    activeEmergencies.forEach(emg => {
        if (emg.latitude && emg.longitude) {
            const redIcon = L.divIcon({
                className: 'custom-emg-icon',
                html: `<div style="background-color:#dc2626; width:18px; height:18px; border-radius:2px; border:2px solid #ffffff; box-shadow:0 1px 6px rgba(220,38,38,0.6);"></div>`,
                iconSize: [18, 18],
                iconAnchor: [9, 9]
            });
            L.marker([emg.latitude, emg.longitude], { icon: redIcon }).addTo(adminMap)
                .bindPopup(`<strong>🚨 EMERGENCY #${emg.emergency_code}</strong><br>Tipe: ${emg.incident_type.toUpperCase()}<br>Status: ${emg.status.toUpperCase()}`)
                .openPopup();
        }
    });
</script>
@endpush
@endsection
