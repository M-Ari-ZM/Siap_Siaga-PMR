@extends('layouts.app')

@section('title', 'Lapor Emergency — Siap Siaga PMR')

@section('content')
<div class="max-w-xl mx-auto px-4 py-5">
    <!-- Header Back -->
    <div class="flex items-center justify-between mb-5">
        <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke Dashboard
        </a>
        <span class="px-2 py-1 rounded bg-red-100 text-red-700 text-[11px] font-extrabold uppercase">
            Form Darurat
        </span>
    </div>

    <div class="bg-white rounded-xl p-5 sm:p-6 border border-slate-200 space-y-5">
        <!-- Safety Alert Note -->
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 flex items-start gap-3">
            <div class="w-7 h-7 rounded-lg bg-red-600 text-white flex items-center justify-center shrink-0 text-sm font-bold">
                ⚠️
            </div>
            <div>
                <h4 class="font-extrabold text-sm text-red-900">Konfirmasi Darurat</h4>
                <p class="text-xs text-red-700 mt-0.5 leading-relaxed">
                    Sistem akan langsung mendispatch anggota PMR terdekat ke lokasi yang Anda tentukan. Pastikan informasi akurat.
                </p>
            </div>
        </div>

        <form action="{{ route('student.emergency.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- 1. Jenis Kejadian -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    1. Jenis Kejadian Medis <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @php
                        $types = [
                            'cedera'     => ['icon' => '🩹', 'label' => 'Cedera / Luka'],
                            'pingsan'    => ['icon' => '😵', 'label' => 'Pingsan'],
                            'sakit'      => ['icon' => '🤒', 'label' => 'Sakit / Lemas'],
                            'kecelakaan' => ['icon' => '🚨', 'label' => 'Kecelakaan'],
                            'lainnya'    => ['icon' => '❓', 'label' => 'Lainnya'],
                        ];
                    @endphp

                    @foreach($types as $key => $item)
                    <label class="relative flex flex-col items-center justify-center p-3 rounded-lg border-2 border-slate-200 hover:border-red-500 cursor-pointer text-center transition-colors has-[:checked]:border-red-600 has-[:checked]:bg-red-50">
                        <input type="radio" name="incident_type" value="{{ $key }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                        <span class="text-2xl mb-1">{{ $item['icon'] }}</span>
                        <span class="text-xs font-bold text-slate-800">{{ $item['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- 2. Lokasi Kejadian -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    2. Lokasi Kejadian <span class="text-red-500">*</span>
                </label>

                <select name="location_id" id="location_id" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-sm font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    <option value="">-- Pilih Titik Lokasi Sekolah --</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }} ({{ $loc->building ?? 'Area Sekolah' }})</option>
                    @endforeach
                    <option value="custom">📍 Lokasi Lainnya / Manual</option>
                </select>

                <!-- Custom location name input -->
                <div id="custom_location_wrapper" class="mt-2 hidden">
                    <input type="text" name="custom_location_name" placeholder="Tuliskan nama lokasi spesifik (misal: Tangga Gedung D lt 3)" class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-red-500 focus:outline-none">
                </div>

                <!-- Interactive Map Section -->
                <div class="mt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5">
                            🗺️ Titik Lokasi pada Peta
                        </span>
                        <div class="flex items-center gap-2 text-[10px] font-bold">
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-red-600 inline-block"></span> Titik Kejadian</span>
                            <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-sm bg-blue-600 inline-block"></span> Lokasi Sekolah</span>
                        </div>
                    </div>
                    
                    <div id="create_emergency_map" class="w-full h-56 rounded-lg border border-slate-200 shadow-inner relative z-10"></div>
                    <p class="text-[10px] text-slate-500 italic">
                        💡 Tips: Anda juga bisa <strong>mengeklik / menggeser pin merah di peta</strong> untuk menentukan posisi kejadian secara akurat.
                    </p>
                </div>

                <!-- GPS Status -->
                <div class="mt-2 flex items-center justify-between p-3 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                    <div class="flex items-center gap-2">
                        <span id="gps_indicator" class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                        <span id="gps_status_text" class="font-bold text-slate-700">Mendeteksi titik GPS perangkat...</span>
                    </div>
                    <button type="button" onclick="detectGPS()" class="px-2.5 py-1 rounded-md bg-slate-200 hover:bg-slate-300 font-bold text-[11px] text-slate-700 transition-colors">
                        🔄 Refresh GPS
                    </button>
                    <input type="hidden" name="latitude" id="input_latitude" value="">
                    <input type="hidden" name="longitude" id="input_longitude" value="">
                </div>
            </div>

            <!-- 3. Deskripsi Singkat -->
            <div>
                <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 mb-2">
                    3. Keterangan Singkat / Kondisi Korban (Opsional)
                </label>
                <textarea name="description" rows="2" placeholder="Contoh: Korban tidak sadarkan diri, butuh tandu dan pertolongan segera..." class="w-full px-4 py-3 rounded-lg border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-4 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-base transition-colors flex items-center justify-center gap-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                <span>KIRIM LAPORAN SEKARANG</span>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Data Lokasi Preset Sekolah dari Database
    const presetLocations = @json($locations);
    
    // Default koordinat peta (fallback sekolah / Jakarta jika belum ada GPS)
    let defaultLat = -6.20000000;
    let defaultLng = 106.81666600;

    // Jika ada lokasi sekolah terdaftar yang memiliki koordinat, gunakan sebagai default center
    const firstValidLoc = presetLocations.find(l => l.latitude && l.longitude);
    if (firstValidLoc) {
        defaultLat = parseFloat(firstValidLoc.latitude);
        defaultLng = parseFloat(firstValidLoc.longitude);
    }

    // Inisialisasi Peta Leaflet
    const createMap = L.map('create_emergency_map').setView([defaultLat, defaultLng], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(createMap);

    // Custom Icon Definisi
    // 1. Icon Lokasi Sekolah (Biru kotak)
    const schoolLocIcon = L.divIcon({
        className: 'custom-loc-icon',
        html: `<div style="background-color:#2563eb; width:12px; height:12px; border-radius:2px; border:2px solid #ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>`,
        iconSize: [12, 12],
        iconAnchor: [6, 6]
    });

    // 2. Icon Titik Darurat / Marker Pelapor (Merah tebal)
    const emergencyDotIcon = L.divIcon({
        className: 'custom-emg-pin',
        html: `<div style="background-color:#dc2626; width:18px; height:18px; border-radius:3px; border:3px solid #ffffff; box-shadow:0 0 8px rgba(220,38,38,0.8);"></div>`,
        iconSize: [18, 18],
        iconAnchor: [9, 9]
    });

    // Render Semua Dot Lokasi Sekolah di Map
    const schoolMarkers = {};
    presetLocations.forEach(loc => {
        if (loc.latitude && loc.longitude) {
            const marker = L.marker([parseFloat(loc.latitude), parseFloat(loc.longitude)], { icon: schoolLocIcon }).addTo(createMap)
                .bindPopup(`<strong>🏫 ${loc.name}</strong><br>${loc.building ?? 'Area Sekolah'}<br><button type="button" class="mt-1 px-2 py-0.5 bg-blue-600 text-white rounded text-[10px] font-bold" onclick="selectLocationById(${loc.id})">Pilih Lokasi Ini</button>`);
            schoolMarkers[loc.id] = marker;
        }
    });

    // Marker Kejadian Darurat (Draggable)
    let emergencyMarker = L.marker([defaultLat, defaultLng], {
        icon: emergencyDotIcon,
        draggable: true
    }).addTo(createMap).bindPopup('<strong>📍 Titik Kejadian</strong><br>Geser pin jika perlu');

    // Event saat pin digeser
    emergencyMarker.on('dragend', function(e) {
        const position = emergencyMarker.getLatLng();
        updateCoordinates(position.lat, position.lng);
    });

    // Event klik di map untuk memindahkan pin darurat secara manual
    createMap.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        setEmergencyLocation(lat, lng);
    });

    function updateCoordinates(lat, lng) {
        document.getElementById('input_latitude').value = parseFloat(lat).toFixed(8);
        document.getElementById('input_longitude').value = parseFloat(lng).toFixed(8);
    }

    function setEmergencyLocation(lat, lng, zoom = null) {
        emergencyMarker.setLatLng([lat, lng]);
        updateCoordinates(lat, lng);
        if (zoom) {
            createMap.setView([lat, lng], zoom);
        } else {
            createMap.panTo([lat, lng]);
        }
    }

    // Fungsi trigger saat memilih dari popup marker sekolah
    window.selectLocationById = function(locId) {
        const selectElem = document.getElementById('location_id');
        selectElem.value = locId;
        selectElem.dispatchEvent(new Event('change'));
    };

    // Sinkronisasi Dropdown Lokasi dengan Map
    document.getElementById('location_id').addEventListener('change', function() {
        const customWrapper = document.getElementById('custom_location_wrapper');
        const selectedVal = this.value;

        if (selectedVal === 'custom') {
            customWrapper.classList.remove('hidden');
        } else {
            customWrapper.classList.add('hidden');
            if (selectedVal) {
                const found = presetLocations.find(l => l.id == selectedVal);
                if (found && found.latitude && found.longitude) {
                    setEmergencyLocation(parseFloat(found.latitude), parseFloat(found.longitude), 18);
                    emergencyMarker.openPopup();
                }
            }
        }
    });

    function detectGPS() {
        const indicator = document.getElementById('gps_indicator');
        const statusText = document.getElementById('gps_status_text');

        indicator.classList.remove('bg-emerald-500', 'bg-red-500');
        indicator.classList.add('bg-amber-500');
        statusText.textContent = 'Mendeteksi GPS...';

        if (!navigator.geolocation) {
            statusText.textContent = 'GPS tidak didukung. Tentukan titik di peta secara manual.';
            indicator.classList.replace('bg-amber-500', 'bg-red-500');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                
                setEmergencyLocation(lat, lng, 18);
                indicator.classList.replace('bg-amber-500', 'bg-emerald-500');
                statusText.textContent = `GPS aktif (±${Math.round(pos.coords.accuracy)}m akurasi)`;
            },
            () => {
                indicator.classList.replace('bg-amber-500', 'bg-red-500');
                statusText.textContent = 'GPS tidak dapat diakses. Silakan klik titik di peta atau pilih lokasi sekolah.';
            },
            { timeout: 8000, enableHighAccuracy: true }
        );
    }

    // Auto-detect GPS on load & fix Leaflet container size
    setTimeout(() => {
        createMap.invalidateSize();
        detectGPS();
    }, 300);
</script>
@endpush
@endsection
