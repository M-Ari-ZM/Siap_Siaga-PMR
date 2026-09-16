<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    /**
     * Generate First-Aid guidance and PMR UKS kit recommendations.
     * Uses Google Gemini AI if API Key is configured, with seamless fallback
     * to the internal Medical Expert Knowledge Base if offline/unconfigured.
     */
    public function generateFirstAidGuidance(Emergency $emergency): array
    {
        $apiKey = config('services.gemini.api_key');
        $primaryModel = config('services.gemini.model', 'gemini-3.5-flash-lite');
        $modelCandidates = array_unique([$primaryModel, 'gemini-3.5-flash-lite', 'gemini-3.5-flash', 'gemini-3.6-flash', 'gemini-flash-latest']);

        if (!empty($apiKey)) {
            foreach ($modelCandidates as $candidateModel) {
                try {
                    $guidance = $this->callGeminiApi($emergency, $apiKey, $candidateModel);
                    if ($guidance && !empty($guidance['first_aid_steps'])) {
                        $guidance['engine_used'] = 'Google Gemini AI';
                        $guidance['model_used'] = $candidateModel;
                        $guidance['generated_at'] = now()->toIso8601String();
                        return $guidance;
                    }
                } catch (\Throwable $e) {
                    Log::warning("Gemini AI ({$candidateModel}) request failed: " . $e->getMessage());
                }
            }
        }

        // Fallback ke sistem pakar internal PMR lokal
        $fallback = $this->fallbackKnowledgeBase($emergency);
        $fallback['engine_used'] = 'Sistem Pakar PMR (Offline Knowledge Base)';
        $fallback['generated_at'] = now()->toIso8601String();

        return $fallback;
    }

    /**
     * Call Google Gemini API with structured JSON output schema.
     */
    protected function callGeminiApi(Emergency $emergency, string $apiKey, string $model): ?array
    {
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $systemInstruction = "Anda adalah Asisten Medis & Pertolongan Pertama PMR (Palang Merah Remaja) Sekolah resmi. "
            . "Tugas Anda menganalisis laporan darurat di lingkungan sekolah dan memberikan instruksi pertolongan pertama instan untuk saksi/siswa di lokasi serta rekomendasi alat UKS untuk petugas PMR yang berangkat. "
            . "Berikan jawaban DALAM BAHASA INDONESIA yang jelas, ringkas, dan sesuai standar medis Palang Merah. "
            . "Format output HARUS selalu berupa JSON valid murni (tanpa tag markdown ```json).";

        $prompt = "Berikut adalah laporan darurat medis di sekolah:\n"
            . "- Jenis Kejadian / Keluhan: {$emergency->incident_type}\n"
            . "- Lokasi Kejadian: " . ($emergency->location_display ?? 'Lingkungan Sekolah') . "\n"
            . "- Deskripsi / Gejala Korban: " . ($emergency->description ?? 'Tidak ada deskripsi rinci') . "\n\n"
            . "Berikan analisis dalam format JSON dengan struktur persis berikut:\n"
            . "{\n"
            . '  "triage_level": "Ringan / Sedang / Darurat / Kritis",' . "\n"
            . '  "triage_badge": "green / yellow / orange / red",' . "\n"
            . '  "summary": "Ringkasan 1 kalimat tentang kondisi korban",' . "\n"
            . '  "first_aid_steps": ["Langkah 1 aman untuk saksi/teman korban", "Langkah 2", "Langkah 3"],' . "\n"
            . '  "do_nots": ["Pantangan 1 yang dilarang dilakukan", "Pantangan 2"],' . "\n"
            . '  "recommended_equipment": ["Nama alat/obat UKS 1 yang harus dibawa petugas PMR", "Alat 2", "Alat 3"],' . "\n"
            . '  "pmr_protocol_notes": ["Catatan teknis tindakan untuk petugas PMR di lokasi"]' . "\n"
            . "}";

        $response = Http::timeout(6)->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($endpoint, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemInstruction . "\n\n" . $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 800,
                'responseMimeType' => 'application/json',
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($text) {
                // Bersihkan kemungkinan markdown code fence jika ada
                $text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($text));
                $parsed = json_decode($text, true);
                if (is_array($parsed) && isset($parsed['first_aid_steps'])) {
                    return $parsed;
                }
            }
        } else {
            Log::error('Gemini API Error: Status ' . $response->status() . ' - Body: ' . $response->body());
        }

        return null;
    }

    /**
     * Comprehensive Offline First-Aid Expert Knowledge Base (Standard PMR & Kemenkes).
     */
    public function fallbackKnowledgeBase(Emergency $emergency): array
    {
        $incident = mb_strtolower($emergency->incident_type ?? '');
        $description = mb_strtolower($emergency->description ?? '');
        $context = $incident . ' ' . $description;

        // 1. Kasus Pingsan / Hilang Kesadaran / Heatstroke
        if (str_contains($context, 'pingsan') || str_contains($context, 'sadar') || str_contains($context, 'gelap') || str_contains($context, 'lemas')) {
            return [
                'triage_level' => 'Sedang',
                'triage_badge' => 'yellow',
                'summary' => 'Korban mengalami penurunan kesadaran sementara (sinkop/pingsan).',
                'first_aid_steps' => [
                    'Baringkan korban di tempat yang teduh, sejuk, dan sirkulasi udara lancar.',
                    'Tinggikan posisi kaki korban sekitar 20-30 cm agar aliran darah ke otak kembali lancar.',
                    'Longgarkan pakaian korban yang ketat (kerah kancing, dasi, ikat pinggang).',
                    'Cek respon pernapasan dan denyut nadi korban secara berkala.',
                    'Beri minyak kayu putih atau aroma menyegarkan di dekat hidung (jangan terlalu dekat ke mata).'
                ],
                'do_nots' => [
                    'DILARANG mengerumuni korban agar pasokan oksigen tidak terhalang.',
                    'DILARANG memberi minuman atau makanan saat korban masih belum sadar penuh (risiko tersedak).',
                    'DILARANG mendudukkan korban secara tiba-tiba saat baru sadar.'
                ],
                'recommended_equipment' => [
                    'Tandu Lipat Darurat',
                    'Minyak Kayu Putih / Aromaterapi',
                    'Tabung Oksigen Portable UKS',
                    'Air Teh Manis Hangat (setelah sadar penuh)',
                    'Tensimeter & Pulse Oximeter'
                ],
                'pmr_protocol_notes' => [
                    'Jika korban tidak sadar > 5 menit atau henti napas, segera lakukan RJP (Resusitasi Jantung Paru) dan panggil ambulance Puskesmas.',
                    'Setelah sadar, tanyakan apakah ada riwayat penyakit jantung/diabetes atau belum sarapan.'
                ]
            ];
        }

        // 2. Kasus Luka Berdarah / Sayat / Sobek / Perdarahan
        if (str_contains($context, 'luka') || str_contains($context, 'darah') || str_contains($context, 'sayat') || str_contains($context, 'bocor') || str_contains($context, 'lecet')) {
            return [
                'triage_level' => 'Sedang',
                'triage_badge' => 'yellow',
                'summary' => 'Terjadi luka terbuka dengan perdarahan aktif yang memerlukan sterilisasi dan balut tekan.',
                'first_aid_steps' => [
                    'Lakukan penekanan langsung pada area luka menggunakan kain bersih atau kasa steril untuk menghentikan perdarahan.',
                    'Posisikan bagian yang terluka lebih tinggi dari jantung jika memungkinkan (elevasi).',
                    'Tenangkan korban agar tidak panik (panik meningkatkan detak jantung dan memperbanyak aliran darah).',
                    'Jaga area luka tetap bersih dari debu atau kotoran tanah di sekitar.'
                ],
                'do_nots' => [
                    'DILARANG menyentuh luka secara langsung dengan tangan telanjang tanpa sarung tangan medis.',
                    'DILARANG mencabut benda tajam/serpihan jika tertancap dalam pada tubuh korban.',
                    'DILARANG menaburkan kopi, pasta gigi, atau bubuk non-medis ke atas luka terbuka.'
                ],
                'recommended_equipment' => [
                    'Kasa Steril & Kasa Gulung Hydrofile',
                    'Plester / Micropore Medical Tape',
                    'Cairan Antiseptik (Povidone Iodine / Rivanol / NaCl 0.9%)',
                    'Sarung Tangan Medis (Latex Gloves)',
                    'Gunting Perban Medis'
                ],
                'pmr_protocol_notes' => [
                    'Bersihkan sekitar luka dengan cairan antiseptik dari arah dalam ke luar.',
                    'Lakukan pembalutan tekan (balut cepat) jika perdarahan deras.',
                    'Rujuk ke fasilitas kesehatan jika luka memerlukan jahitan (kedalaman > 0.5 cm).'
                ]
            ];
        }

        // 3. Kasus Asma / Sesak Napas / Hiperventilasi
        if (str_contains($context, 'asma') || str_contains($context, 'sesak') || str_contains($context, 'napas') || str_contains($context, 'nafas') || str_contains($context, 'dada')) {
            return [
                'triage_level' => 'Darurat',
                'triage_badge' => 'orange',
                'summary' => 'Gangguan pernapasan akut atau serangan asma membutuhkan sirkulasi udara dan bantuan oksigen.',
                'first_aid_steps' => [
                    'Posisikan korban duduk tegak bersandar (setengah duduk / posisi fowler) agar rongga dada lebih lega.',
                    'Bantu korban bernapas perlahan dengan teknik pernapasan bibir terkatup (pursed-lip breathing).',
                    'Bawa korban ke tempat terbuka yang sejuk dan bebas debu atau asap.',
                    'Tanyakan apakah korban membawa obat inhaler pribadi di tasnya.'
                ],
                'do_nots' => [
                    'DILARANG membaringkan korban telentang karena akan memperberat kerja paru-paru.',
                    'DILARANG mengerumuni korban.',
                    'DILARANG memaksa korban bicara panjang.'
                ],
                'recommended_equipment' => [
                    'Tabung Oksigen Portable UKS + Masker Oksigen',
                    'Pulse Oximeter (Cek SpO2 Saturation)',
                    'Kursi Roda / Tandu Duduk',
                    'Minyak Kayu Putih / Inhaler Cadangan UKS'
                ],
                'pmr_protocol_notes' => [
                    'Cek saturasi oksigen menggunakan oximeter. Jika SpO2 < 92%, segera berikan terapi oksigen 2-4 liter/menit.',
                    'Jika dalam 10 menit sesak tidak berkurang setelah inhaler, segera evakuasi ke IGD Puskesmas/RS.'
                ]
            ];
        }

        // 4. Kasus Terkilir / Keseleo / Patah Tulang / Cedera Sendi
        if (str_contains($context, 'terkilir') || str_contains($context, 'keseleo') || str_contains($context, 'patah') || str_contains($context, 'tulang') || str_contains($context, 'sendi') || str_contains($context, 'jatuh')) {
            return [
                'triage_level' => 'Sedang',
                'triage_badge' => 'yellow',
                'summary' => 'Cedera muskuloskeletal (sendi/tulang) memerlukan imobilisasi prinsip RICE (Rest, Ice, Compression, Elevation).',
                'first_aid_steps' => [
                    'Istirahatkan bagian tubuh yang cedera, jangan digerakkan (Rest).',
                    'Beri kompres dingin atau es berbalut kain pada area yang bengkak selama 15 menit (Ice).',
                    'Topang bagian yang cedera dengan bantal atau jaket agar posisinya nyaman.',
                    'Tenangkan korban dan jaga agar tidak melakukan tumpuan beban pada kaki/tangan yang sakit.'
                ],
                'do_nots' => [
                    'DILARANG memijat, mengurut, atau memutar sendi/tulang yang dicurigai cedera atau patah.',
                    'DILARANG memaksa meluruskan posisi tulang/sendi yang tampak bengkok/deformitas.',
                    'DILARANG menempelkan es batu langsung ke kulit tanpa lapisan kain/handuk.'
                ],
                'recommended_equipment' => [
                    'Spalk / Bidai Kayu / Splint Imobilisasi',
                    'Mitela (Pembalut Segitiga)',
                    'Elastic Bandage (Perban Elastis)',
                    'Ice Gel Pack / Kantong Kompres Es UKS',
                    'Tandu Evakuasi'
                ],
                'pmr_protocol_notes' => [
                    'Terapkan pembidaian (splinting) melewati dua sendi di atas dan di bawah area patah/cedera.',
                    'Periksa PMS (Pulse, Motoric, Sensory) pada ujung jari sebelum dan sesudah pembidaian.',
                    'Evakuasi menggunakan tandu datar jika dicurigai fraktur tulang ekstremitas bawah.'
                ]
            ];
        }

        // 5. Kasus Mimisan / Perdarahan Hidung (Epistaksis)
        if (str_contains($context, 'mimisan') || str_contains($context, 'hidung')) {
            return [
                'triage_level' => 'Ringan',
                'triage_badge' => 'green',
                'summary' => 'Perdarahan dari rongga hidung (epistaksis anterior), umum terjadi karena panas atau benturan ringan.',
                'first_aid_steps' => [
                    'Dudukkan korban dengan posisi kepala sedikit CONDONG KE DEPAN (menunduk).',
                    'Jepit cuping hidung korban dengan ibu jari dan telunjuk secara rapat selama 5-10 menit.',
                    'Minta korban bernapas lewat mulut selama hidung dijepit.',
                    'Letakkan kompres dingin pada pangkal hidung atau kening.'
                ],
                'do_nots' => [
                    'DILARANG menengadahkan kepala ke belakang (darah bisa tertelan dan memicu muntah atau tersedak ke saluran napas).',
                    'DILARANG menyumpal hidung dengan tisu kotor atau daun sirih tanpa sterilisasi.',
                    'DILARANG membuang ingus atau menghembuskan napas kuat dari hidung setelah perdarahan berhenti.'
                ],
                'recommended_equipment' => [
                    'Kasa Steril Tampon Hidung',
                    'Kompres Es / Ice Bag',
                    'Sarung Tangan Medis (Latex)',
                    'Tisu Kering Higienis'
                ],
                'pmr_protocol_notes' => [
                    'Jika perdarahan tidak kunjung berhenti setelah 15 menit penekanan, pasang tampon kasa steril berantiseptik.',
                    'Rujuk ke dokter jika mimisan disertai trauma kepala hebat atau keluar cairan bening dari hidung.'
                ]
            ];
        }

        // 6. Kasus Kram Otot / Kejang Otot / Dehidrasi Olahraga
        if (str_contains($context, 'kram') || str_contains($context, 'kejang') || str_contains($context, 'otot') || str_contains($context, 'olahraga')) {
            return [
                'triage_level' => 'Ringan',
                'triage_badge' => 'green',
                'summary' => 'Kontraksi otot mendadak akibat kelelahan, kekurangan cairan, atau belum pemanasan.',
                'first_aid_steps' => [
                    'Hentikan aktivitas dan istirahatkan korban di tempat teduh.',
                    'Lakukan peregangan (stretching) perlahan dan lembut ke arah berlawanan dari tarikan kram.',
                    'Pijat ringan otot yang kram setelah tarikan mereda.',
                    'Berikan air mineral atau larutan elektrolit/oralit sedikit demi sedikit.'
                ],
                'do_nots' => [
                    'DILARANG menarik otot secara paksa atau menghentakkan kaki secara tiba-tiba.',
                    'DILARANG meminum air es terlalu banyak secara terburu-buru.'
                ],
                'recommended_equipment' => [
                    'Balsem Otot / Krim Penghangat (Methyl Salicylate)',
                    'Minuman Isotonik / Elektrolit UKS',
                    'Handuk Bersih & Air Hangat'
                ],
                'pmr_protocol_notes' => [
                    'Kompres hangat pada area otot yang tegang untuk memperlancar sirkulasi darah.',
                    'Pantau korban selama 15 menit di UKS hingga relaksasi otot pulih sempurna.'
                ]
            ];
        }

        // 7. Kasus Umum / Default
        return [
            'triage_level' => 'Sedang',
            'triage_badge' => 'yellow',
            'summary' => 'Insiden darurat medis sekolah yang memerlukan pemeriksaan vital sign dan pertolongan pertama terarah.',
            'first_aid_steps' => [
                'Jaga ketenangan di lokasi dan amankan lingkungan sekitar korban dari bahaya (Prinsip 3A: Aman Diri, Aman Pasien, Aman Lingkungan).',
                'Temani korban dan posisikan korban dalam posisi yang paling nyaman dan aman.',
                'Periksa tingkat kesadaran dan keluhan utama korban.',
                'Tunggu petugas PMR yang sedang meluncur menuju lokasi dengan membawa peralatan UKS.'
            ],
            'do_nots' => [
                'DILARANG memindahkan korban sembarangan jika terdapat kecurigaan cedera leher atau tulang belakang.',
                'DILARANG memberikan obat-obatan keras tanpa instruksi tenaga medis resmi.',
                'DILARANG meninggalkan korban sendirian sebelum petugas PMR atau guru tiba.'
            ],
            'recommended_equipment' => [
                'Tas Pertolongan Pertama (First Aid Kit PMR)',
                'Kasa Steril & Antiseptik',
                'Tandu Darurat',
                'Tensimeter Digital & Thermometer'
            ],
            'pmr_protocol_notes' => [
                'Lakukan pemeriksaan primer (ABC: Airway, Breathing, Circulation) saat tiba di lokasi.',
                'Catat riwayat singkat keluhan korban dan laporkan ke Pembina UKS / Guru Piket.'
            ]
        ];
    }
}
