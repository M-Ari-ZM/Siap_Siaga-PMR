@extends('layouts.app')

@section('title', 'Dashboard Petugas PMR — Siap Siaga')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-5 space-y-5">

    <!-- PMR Profile & Availability Toggle Card -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-lg bg-emerald-600 text-white font-black flex items-center justify-center text-sm">
                PMR
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="font-bold text-base text-slate-900">{{ $pmrUser->name }}</h1>
                    @if($pmrUser->pmrProfile && $pmrUser->pmrProfile->is_on_duty)
                    <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[11px] font-bold">PIKET HARI INI</span>
                    @else
                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-500 text-[11px] font-bold">OFF PIKET</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500">{{ $pmrUser->pmrProfile->nisn_or_member_id ?? 'PMR-2024' }} • {{ $pmrUser->pmrProfile->class_grade ?? 'XI MIPA' }}</p>
            </div>
        </div>

        <!-- Availability Status Switch -->
        <form action="{{ route('pmr.availability.toggle') }}" method="POST" class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg border border-slate-200 text-xs font-bold w-full sm:w-auto justify-between">
            @csrf
            @php $currentAvail = $pmrUser->pmrProfile->availability_status ?? 'available'; @endphp

            <button type="submit" name="status" value="available" class="px-3 py-1.5 rounded-md transition-colors {{ $currentAvail === 'available' ? 'bg-emerald-600 text-white' : 'text-slate-500 hover:text-slate-800' }}">
                AVAILABLE
            </button>
            <button type="submit" name="status" value="busy" class="px-3 py-1.5 rounded-md transition-colors {{ $currentAvail === 'busy' ? 'bg-amber-500 text-white' : 'text-slate-500 hover:text-slate-800' }}">
                BUSY
            </button>
            <button type="submit" name="status" value="offline" class="px-3 py-1.5 rounded-md transition-colors {{ $currentAvail === 'offline' ? 'bg-slate-700 text-white' : 'text-slate-500 hover:text-slate-800' }}">
                OFFLINE
            </button>
        </form>
    </div>

    <!-- Active Incoming Emergency Alert -->
    @if($activeEmergencies->count() > 0)
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-red-600 flex items-center gap-2">
                <span class="w-2 h-2 rounded-sm bg-red-600 inline-block"></span>
                Emergency Masuk / Perlu Penanganan Segera
            </h2>
            <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs font-bold">{{ $activeEmergencies->count() }} Kasus</span>
        </div>

        @foreach($activeEmergencies as $emg)
        <div class="bg-white rounded-xl p-5 border-2 border-red-400 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-1 rounded bg-red-600 text-white font-extrabold text-xs uppercase tracking-wider">
                        🚨 {{ $emg->incident_type }}
                    </span>
                    <span class="font-mono text-xs text-slate-400 font-bold">#{{ $emg->emergency_code }}</span>
                </div>
                <span class="text-xs font-bold text-red-600">
                    Dilaporkan {{ $emg->reported_at->diffForHumans() }} ({{ $emg->reported_at->format('H:i') }} WIB)
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 font-medium">Lokasi Kejadian:</span>
                    <p class="font-bold text-slate-900 text-sm mt-0.5">📍 {{ $emg->location_display }}</p>
                    @php
                        $myAssignment = $emg->activeAssignment;
                        $distanceM = $myAssignment->distance_meters ?? null;
                        $isMyTask = $myAssignment && $myAssignment->pmr_user_id === $pmrUser->id;
                    @endphp
                    @if($distanceM !== null)
                    <p class="text-slate-500 mt-1">
                        Estimasi Jarak:
                        <strong class="text-slate-800">
                            @if($distanceM == 0) Lokasi sangat dekat
                            @else ~{{ $distanceM }} meter dari posisi Anda
                            @endif
                        </strong>
                    </p>
                    @endif
                </div>
                <div>
                    <span class="text-slate-400 font-medium">Pelapor (Siswa):</span>
                    <p class="font-bold text-slate-900 text-sm mt-0.5">{{ $emg->reporter->name }}</p>
                    <p class="text-slate-500 mt-0.5">Kontak: {{ $emg->reporter->phone_number ?? '-' }}</p>
                    @if($myAssignment && !$isMyTask)
                    <p class="text-amber-600 font-bold text-[11px] mt-1">⚠️ Ditangani: {{ $myAssignment->pmrUser->name ?? '-' }}</p>
                    @endif
                </div>
            </div>

            @if($emg->description)
            <div class="bg-red-50 p-3 rounded-lg border border-red-200 text-xs text-red-900">
                <span class="font-bold">Keterangan:</span> {{ $emg->description }}
            </div>
            @endif

            <!-- 🤖 AI Triage & Perlengkapan UKS Wajib Bawa -->
            @if(!empty($emg->ai_guidance))
            <div class="bg-slate-900 text-slate-100 p-3.5 rounded-xl border border-slate-800 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-300 flex items-center gap-1.5">
                        <span class="text-indigo-400">✨</span> Rekomendasi Alat UKS (AI Advisor)
                    </span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700 text-amber-300 font-bold">
                        Triage: {{ $emg->ai_guidance['triage_level'] ?? 'Sedang' }}
                    </span>
                </div>
                @if(!empty($emg->ai_guidance['recommended_equipment']))
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($emg->ai_guidance['recommended_equipment'] as $tool)
                        <span class="px-2 py-1 rounded-md bg-slate-800 text-sky-300 text-[11px] font-medium border border-slate-700">
                            🩹 {{ $tool }}
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="pt-1 flex flex-wrap items-center gap-3">
                @if($emg->status === 'reported' || $emg->status === 'searching_pmr')
                <form action="{{ route('pmr.emergency.accept', $emg->id) }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        TERIMA PANGGILAN DARURAT
                    </button>
                </form>
                @elseif($emg->status === 'pmr_assigned' || $emg->status === 'on_the_way')
                <form action="{{ route('pmr.emergency.status', $emg->id) }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="status" value="handling">
                    <button type="submit" class="w-full py-3 px-4 rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs flex items-center justify-center gap-2 transition-colors">
                        SAYA SUDAH TIBA & MULAI PENANGANAN
                    </button>
                </form>
                @elseif($emg->status === 'handling')
                <a href="{{ route('pmr.emergency.report', $emg->id) }}" class="flex-1 py-3 px-4 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-2 text-center transition-colors">
                    ISI LAPORAN & SELESAIKAN PENANGANAN
                </a>
                @endif

                <a href="{{ route('emergency.show', $emg->id) }}" class="px-4 py-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors">
                    Lihat Peta & Detail
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white p-8 rounded-xl border border-slate-200 text-center space-y-2">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xl mx-auto font-bold">
            ✓
        </div>
        <h3 class="font-bold text-base text-slate-900">Kondisi Sekolah Kondusif</h3>
        <p class="text-xs text-slate-500">Tidak ada emergency aktif saat ini. Tetap siap siaga.</p>
    </div>
    @endif

    <!-- PMR Response History -->
    <div class="space-y-3">
        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">
            Riwayat Penanganan Anda
        </h3>

        @forelse($handledEmergencies as $handled)
        <div class="bg-white p-4 rounded-xl border border-slate-200 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    🩹
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-900">{{ ucfirst($handled->incident_type) }} — {{ $handled->location_display }}</h4>
                    <p class="text-xs text-slate-500">{{ $handled->reported_at->format('d M Y, H:i') }} • Pelapor: {{ $handled->reporter->name }}</p>
                </div>
            </div>
            <span class="px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 shrink-0">
                Selesai
            </span>
        </div>
        @empty
        <div class="bg-white p-6 rounded-xl border border-dashed border-slate-200 text-center">
            <p class="text-xs text-slate-400">Belum ada riwayat penanganan selesai.</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    // Auto Live GPS Broadcast untuk Anggota PMR
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((pos) => {
            fetch("{{ route('pmr.location.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude
                })
            }).catch(e => console.warn('PMR GPS Sync error:', e));
        }, null, { enableHighAccuracy: true });
    }

    // Auto refresh setiap 10 detik
    setInterval(() => {
        if (!document.querySelector('input:focus, textarea:focus')) {
            window.location.reload();
        }
    }, 10000);
</script>
@endpush
@endsection
