@props(['guidance' => null, 'role' => null])

@if(!empty($guidance))
@php
    $userRole = $role ?? (auth()->user()?->role ?? 'student');
    $triage = $guidance['triage_level'] ?? 'Sedang';
    $badge = $guidance['triage_badge'] ?? 'yellow';
    
    // Triage light-mode styling
    $triageStyles = [
        'green' => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'pill' => 'bg-emerald-500', 'label' => '🟢 Ringan / Non-Kritis'],
        'yellow' => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200', 'pill' => 'bg-amber-500', 'label' => '🟡 Sedang / Butuh Penanganan Segera'],
        'orange' => ['bg' => 'bg-orange-50 text-orange-700 border-orange-200', 'pill' => 'bg-orange-500', 'label' => '🟠 Darurat / Perlu Perawatan Cepat'],
        'red' => ['bg' => 'bg-rose-50 text-rose-700 border-rose-200', 'pill' => 'bg-rose-500', 'label' => '🔴 Kritis / Prioritas Utama'],
    ];

    $currentStyle = $triageStyles[$badge] ?? $triageStyles['yellow'];
@endphp

<div class="bg-gradient-to-b from-slate-50 to-white border border-slate-200 rounded-xl p-4 sm:p-5 text-slate-800 space-y-4 shadow-sm">
    <!-- Header Section -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-200/80">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center font-bold text-sm shrink-0 border border-red-200">
                ✨
            </div>
            <div>
                <h3 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                    @if($userRole === 'student')
                        AI Panduan Pertolongan Pertama Instan
                    @elseif($userRole === 'pmr')
                        AI Rekomendasi Perlengkapan & Protokol PMR
                    @else
                        AI First-Aid Advisor & Triage PMR (Supervisi Admin)
                    @endif
                </h3>
                <p class="text-[11px] text-slate-500">
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
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $currentStyle['bg'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $currentStyle['pill'] }}"></span>
                Triage: {{ $triage }}
            </span>
        </div>
    </div>

    <!-- Summary -->
    @if(!empty($guidance['summary']))
        <div class="px-3.5 py-2.5 bg-blue-50/70 rounded-lg border border-blue-100 text-xs text-slate-700 leading-relaxed flex items-start gap-2">
            <span class="text-sm">💡</span>
            <div><strong class="text-slate-900">Analisis Kondisi:</strong> {{ $guidance['summary'] }}</div>
        </div>
    @endif

    <!-- Content Sections Based on Role -->
    <div>
        @if($userRole === 'student')
            <!-- ================= TAMPILAN KHUSUS SISWA (PELAPOR) ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Langkah Pertolongan Pertama Aman -->
                <div class="space-y-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-emerald-800 uppercase tracking-wider">
                        <span>🛡️</span> Tindakan Aman Saat Ini:
                    </div>
                    @if(!empty($guidance['first_aid_steps']))
                        <ul class="space-y-2">
                            @foreach($guidance['first_aid_steps'] as $idx => $step)
                                <li class="flex items-start gap-2.5 text-xs text-slate-700 bg-white p-2.5 rounded-lg border border-slate-200">
                                    <span class="shrink-0 w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 font-bold text-[11px] flex items-center justify-center">
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
                        <div class="p-3 bg-red-50/80 border border-red-200 rounded-lg">
                            <div class="flex items-center gap-1.5 text-xs font-extrabold text-red-800 uppercase tracking-wider mb-2">
                                <span>⛔</span> Dilarang Dilakukan:
                            </div>
                            <ul class="space-y-1.5 text-xs text-red-900">
                                @foreach($guidance['do_nots'] as $dont)
                                    <li class="flex items-start gap-2">
                                        <span class="text-red-600 font-bold shrink-0">✕</span>
                                        <span class="leading-relaxed">{{ $dont }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="p-3 bg-slate-100 border border-slate-200 rounded-lg flex items-center gap-2.5 text-xs text-slate-700">
                        <span class="text-lg">🚑</span>
                        <div>
                            <strong class="text-slate-900 block font-bold">Petugas PMR Sedang Menuju Lokasi</strong>
                            <p class="text-[11px] text-slate-500">Peralatan medis resmi sedang disiapkan dan dibawa oleh tim PMR.</p>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($userRole === 'pmr')
            <!-- ================= TAMPILAN KHUSUS PETUGAS PMR ================= -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Alat UKS yang Harus Dibawa -->
                <div class="space-y-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-sky-800 uppercase tracking-wider">
                        <span>🎒</span> Bawa Alat/Obat UKS Ini:
                    </div>
                    @if(!empty($guidance['recommended_equipment']))
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($guidance['recommended_equipment'] as $item)
                                <div class="flex items-center gap-2 px-3 py-2 bg-sky-50 text-sky-900 border border-sky-200 rounded-lg text-xs font-medium">
                                    <span class="text-sky-600 shrink-0">🩹</span>
                                    <span class="leading-tight">{{ $item }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($guidance['do_nots']))
                        <div class="p-2.5 bg-red-50 border border-red-200 rounded-lg text-xs text-red-900 mt-2">
                            <span class="font-bold text-red-700">⚠️ Kontraindikasi:</span>
                            {{ implode(' • ', $guidance['do_nots']) }}
                        </div>
                    @endif
                </div>

                <!-- Protokol Tindakan PMR di Lokasi -->
                <div class="space-y-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                        <span>📋</span> SOP Tindakan di Lokasi:
                    </div>
                    @if(!empty($guidance['pmr_protocol_notes']))
                        <ul class="space-y-2">
                            @foreach($guidance['pmr_protocol_notes'] as $protocol)
                                <li class="flex items-start gap-2 text-xs text-slate-700 bg-white p-2.5 rounded-lg border border-slate-200">
                                    <span class="text-emerald-600 font-bold shrink-0">✓</span>
                                    <span class="leading-relaxed">{{ $protocol }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-500">
                            Lakukan pemeriksaan fisik dasar (ABC) dan tenangkan korban.
                        </div>
                    @endif
                </div>
            </div>

        @else
            <!-- ================= TAMPILAN ADMIN (SUPERVISI LENGKAP) ================= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="space-y-2.5">
                    <div class="text-xs font-extrabold text-emerald-800 uppercase tracking-wider">
                        🛡️ Instruksi Pertolongan Pertama (Siswa)
                    </div>
                    @if(!empty($guidance['first_aid_steps']))
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            @foreach($guidance['first_aid_steps'] as $idx => $step)
                                <li>{{ $idx + 1 }}. {{ $step }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="space-y-2.5">
                    <div class="text-xs font-extrabold text-sky-800 uppercase tracking-wider">
                        🎒 Rekomendasi Perlengkapan & Protokol PMR
                    </div>
                    @if(!empty($guidance['recommended_equipment']))
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($guidance['recommended_equipment'] as $item)
                                <span class="px-2.5 py-1 bg-sky-50 text-sky-800 text-xs rounded-md border border-sky-200 font-medium">
                                    🩹 {{ $item }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Engine Footer Tag -->
    <div class="pt-2.5 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 text-[11px] text-slate-500">
        <span class="flex items-center gap-1.5">
            <span>⚡</span> Sumber Analisis: 
            @if(($guidance['engine_used'] ?? '') === 'Google Gemini AI')
                <strong class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1 font-semibold">
                    ✨ Google Gemini AI (Online Active)
                </strong>
            @else
                <strong class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 border border-slate-200 font-semibold">
                    💾 {{ $guidance['engine_used'] ?? 'Sistem Pakar PMR' }}
                </strong>
            @endif
        </span>
        <span class="text-[10px] text-slate-400 italic">Disesuaikan otomatis untuk peran: {{ strtoupper($userRole) }}</span>
    </div>
</div>
@endif
