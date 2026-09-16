@props(['guidance' => null, 'role' => null])

@if(!empty($guidance))
@php
    $userRole = $role ?? (auth()->user()?->role ?? 'student');
    $triage = $guidance['triage_level'] ?? 'Sedang';
    $badge = $guidance['triage_badge'] ?? 'yellow';
    
    // Triage styling
    $triageStyles = [
        'green' => ['bg' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', 'pill' => 'bg-emerald-500', 'label' => '🟢 Ringan / Non-Kritis'],
        'yellow' => ['bg' => 'bg-amber-500/10 text-amber-400 border-amber-500/30', 'pill' => 'bg-amber-500', 'label' => '🟡 Sedang / Butuh Penanganan Segera'],
        'orange' => ['bg' => 'bg-orange-500/10 text-orange-400 border-orange-500/30', 'pill' => 'bg-orange-500', 'label' => '🟠 Darurat / Perlu Perawatan Cepat'],
        'red' => ['bg' => 'bg-rose-500/10 text-rose-400 border-rose-500/30', 'pill' => 'bg-rose-500', 'label' => '🔴 Kritis / Prioritas Utama'],
    ];

    $currentStyle = $triageStyles[$badge] ?? $triageStyles['yellow'];
@endphp

<div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-4 sm:p-5 shadow-xl text-slate-100 relative overflow-hidden backdrop-blur-md">
    <!-- Background subtle gradient decoration -->
    <div class="absolute -top-12 -right-12 w-40 h-40 bg-gradient-to-bl from-red-600/20 via-indigo-600/10 to-transparent rounded-full blur-2xl pointer-events-none"></div>

    <!-- Header Section -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3.5 border-b border-slate-800">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-red-500 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-red-500/20 text-sm shrink-0">
                ✨
            </div>
            <div>
                <h3 class="font-bold text-sm text-slate-100 flex items-center gap-1.5">
                    @if($userRole === 'student')
                        AI Panduan Pertolongan Pertama Instan
                    @elseif($userRole === 'pmr')
                        AI Rekomendasi Perlengkapan & Protokol PMR
                    @else
                        AI First-Aid Advisor & Triage PMR (Supervisi Admin)
                    @endif
                </h3>
                <p class="text-[11px] text-slate-400">
                    @if($userRole === 'student')
                        Tindakan aman yang bisa Anda lakukan sambil menunggu petugas tiba
                    @elseif($userRole === 'pmr')
                        Persiapan alat UKS dan prosedur tindakan medis sebelum tiba di TKP
                    @else
                        Analisis otomatis tingkat keparahan (triage) dan instruksi medis
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $currentStyle['bg'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $currentStyle['pill'] }} animate-pulse"></span>
                Triage: {{ $triage }}
            </span>
        </div>
    </div>

    <!-- Summary -->
    @if(!empty($guidance['summary']))
        <div class="mt-3.5 px-3.5 py-2.5 bg-slate-800/60 rounded-xl border border-slate-700/50 text-xs text-slate-300 leading-relaxed flex items-start gap-2">
            <span class="text-sm">💡</span>
            <div><strong class="text-slate-200">Analisis Kondisi:</strong> {{ $guidance['summary'] }}</div>
        </div>
    @endif

    <!-- Content Sections Based on Role -->
    <div class="mt-4">
        @if($userRole === 'student')
            <!-- ================= TAMPILAN KHUSUS SISWA (PELAPOR) ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Langkah Pertolongan Pertama Aman -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold text-emerald-400 uppercase tracking-wider">
                        <span>🛡️</span> Tindakan Aman yang Bisa Dilakukan Sekarang:
                    </div>
                    @if(!empty($guidance['first_aid_steps']))
                        <ul class="space-y-2">
                            @foreach($guidance['first_aid_steps'] as $idx => $step)
                                <li class="flex items-start gap-2.5 text-xs text-slate-300 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800">
                                    <span class="shrink-0 w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[11px] flex items-center justify-center">
                                        {{ $idx + 1 }}
                                    </span>
                                    <span class="leading-relaxed">{{ $step }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <!-- Pantangan Medis (Do Nots) & Status Menunggu -->
                <div class="space-y-3 flex flex-col justify-between">
                    @if(!empty($guidance['do_nots']))
                        <div class="p-3.5 bg-rose-950/40 border border-rose-800/40 rounded-xl">
                            <div class="flex items-center gap-1.5 text-xs font-bold text-rose-400 uppercase tracking-wider mb-2">
                                <span>⛔</span> Dilarang Dilakukan (Pantangan):
                            </div>
                            <ul class="space-y-2 text-xs text-rose-200/90">
                                @foreach($guidance['do_nots'] as $dont)
                                    <li class="flex items-start gap-2">
                                        <span class="text-rose-400 font-bold shrink-0">✕</span>
                                        <span class="leading-relaxed">{{ $dont }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="p-3 bg-indigo-950/40 border border-indigo-800/40 rounded-xl flex items-center gap-2.5 text-xs text-indigo-200">
                        <span class="text-lg">🚑</span>
                        <div>
                            <strong class="text-indigo-100 block font-semibold">Petugas PMR Sedang Menuju Lokasi</strong>
                            <p class="text-[11px] text-indigo-300/90">Peralatan medis resmi sedang disiapkan dan dibawa oleh tim PMR.</p>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($userRole === 'pmr')
            <!-- ================= TAMPILAN KHUSUS PETUGAS PMR ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Alat UKS yang Harus Dibawa -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold text-sky-400 uppercase tracking-wider">
                        <span>🎒</span> Bawa Alat/Obat UKS Ini Sebelum Berangkat:
                    </div>
                    @if(!empty($guidance['recommended_equipment']))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($guidance['recommended_equipment'] as $item)
                                <div class="flex items-center gap-2 px-3 py-2 bg-sky-950/50 text-sky-300 border border-sky-800/50 rounded-xl text-xs font-medium">
                                    <span class="text-sky-400 shrink-0">🩹</span>
                                    <span class="leading-tight">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($guidance['do_nots']))
                        <div class="p-3 bg-rose-950/30 border border-rose-800/30 rounded-xl text-xs text-rose-200 mt-2">
                            <span class="font-bold text-rose-400">⚠️ Kontraindikasi:</span>
                            {{ implode(' • ', $guidance['do_nots']) }}
                        </div>
                    @endif
                </div>

                <!-- Protokol Tindakan PMR di Lokasi -->
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold text-indigo-300 uppercase tracking-wider">
                        <span>📋</span> SOP & Protokol Tindakan di Lokasi:
                    </div>
                    @if(!empty($guidance['pmr_protocol_notes']))
                        <ul class="space-y-2">
                            @foreach($guidance['pmr_protocol_notes'] as $protocol)
                                <li class="flex items-start gap-2.5 text-xs text-slate-300 bg-slate-800/60 p-2.5 rounded-xl border border-slate-700/60">
                                    <span class="text-indigo-400 font-bold shrink-0">✓</span>
                                    <span class="leading-relaxed">{{ $protocol }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-3 bg-slate-800/40 rounded-xl text-xs text-slate-400">
                            Lakukan pemeriksaan fisik dasar (ABC) dan tenangkan korban.
                        </div>
                    @endif
                </div>
            </div>

        @else
            <!-- ================= TAMPILAN ADMIN (SUPERVISI LENGKAP) ================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="space-y-3">
                    <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">
                        🛡️ Instruksi Pertolongan Pertama (Siswa)
                    </div>
                    @if(!empty($guidance['first_aid_steps']))
                        <ul class="space-y-1.5 text-xs text-slate-300">
                            @foreach($guidance['first_aid_steps'] as $idx => $step)
                                <li>{{ $idx + 1 }}. {{ $step }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="space-y-3">
                    <div class="text-xs font-bold text-sky-400 uppercase tracking-wider">
                        🎒 Rekomendasi Perlengkapan & Protokol PMR
                    </div>
                    @if(!empty($guidance['recommended_equipment']))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($guidance['recommended_equipment'] as $item)
                                <span class="px-2 py-1 bg-sky-950/60 text-sky-300 text-xs rounded-md border border-sky-800">
                                    {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Engine Footer Tag -->
    <div class="mt-4 pt-2.5 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-400">
        <span class="flex items-center gap-1.5">
            <span>⚡</span> Sumber Analisis: 
            @if(($guidance['engine_used'] ?? '') === 'Google Gemini AI')
                <strong class="px-2 py-0.5 rounded-md bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-ping"></span>
                    ✨ Google Gemini AI (Online Active)
                </strong>
            @else
                <strong class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 border border-slate-700">
                    💾 {{ $guidance['engine_used'] ?? 'Sistem Pakar PMR' }}
                </strong>
            @endif
        </span>
        <span class="text-[10px] text-slate-500 italic">Disesuaikan otomatis untuk peran: {{ strtoupper($userRole) }}</span>
    </div>
</div>
@endif
