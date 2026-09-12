<?php

namespace App\Services;

use App\Models\Emergency;
use App\Models\EmergencyAssignment;
use App\Models\EmergencyTimeline;
use App\Models\User;

class DispatchService
{
    /**
     * Cari kandidat PMR terbaik berdasarkan:
     * 1. Status Availability ('available')
     * 2. Status Jadwal Piket ('is_on_duty' => bobot +30)
     * 3. Jarak Terdekat (Haversine Formula => bobot jarak hingga 50 poin)
     * 4. Beban Tugas aktif saat ini (sedang menangani kasus => penalti)
     */
    public function dispatchBestPmr(Emergency $emergency): ?User
    {
        $pmrCandidates = User::where('role', 'pmr')
            ->whereHas('pmrProfile', function ($query) {
                $query->where('availability_status', 'available');
            })
            ->with(['pmrProfile', 'pmrAssignments' => function ($q) {
                $q->whereIn('status', ['offered', 'accepted']);
            }])
            ->get();

        if ($pmrCandidates->isEmpty()) {
            // Jika tidak ada yang available, fallback cari PMR on-duty mana saja
            $pmrCandidates = User::where('role', 'pmr')
                ->whereHas('pmrProfile', function ($query) {
                    $query->where('is_on_duty', true);
                })
                ->with(['pmrProfile', 'pmrAssignments'])
                ->get();
        }

        if ($pmrCandidates->isEmpty()) {
            return null;
        }

        $scoredCandidates = $pmrCandidates->map(function ($pmr) use ($emergency) {
            $profile = $pmr->pmrProfile;
            
            // 1. Hitung jarak (dalam meter)
            $distance = 50; // default jika koordinat belum tercatat
            if ($emergency->latitude && $emergency->longitude && $profile && $profile->current_latitude && $profile->current_longitude) {
                $distance = $this->calculateHaversineDistance(
                    $emergency->latitude,
                    $emergency->longitude,
                    $profile->current_latitude,
                    $profile->current_longitude
                );
            }

            // 2. Skoring algoritma (Skala 0 - 100)
            $score = 50.0; // Base score

            // Faktor Piket (+30 poin)
            if ($profile && $profile->is_on_duty) {
                $score += 30;
            }

            // Faktor Jarak (+20 poin untuk jarak < 50m, berkurang jika lebih jauh)
            $distanceScore = max(0, 20 - ($distance / 20));
            $score += $distanceScore;

            // Faktor Beban Tugas (-20 jika sedang ada tugas aktif)
            $activeLoad = $pmr->pmrAssignments->count();
            $score -= ($activeLoad * 20);

            return [
                'user' => $pmr,
                'distance' => round($distance),
                'score' => round(max(10, min(99, $score)), 1),
            ];
        });

        // Urutkan kandidat berdasarkan skor tertinggi
        $best = $scoredCandidates->sortByDesc('score')->first();

        if (!$best) {
            return null;
        }

        $bestUser = $best['user'];

        // Buat penugasan
        EmergencyAssignment::create([
            'emergency_id' => $emergency->id,
            'pmr_user_id' => $bestUser->id,
            'status' => 'offered',
            'distance_meters' => $best['distance'],
            'dispatch_score' => $best['score'],
            'offered_at' => now(),
        ]);

        $emergency->update(['status' => 'pmr_assigned']);

        EmergencyTimeline::create([
            'emergency_id' => $emergency->id,
            'actor_id' => $bestUser->id,
            'status' => 'pmr_assigned',
            'title' => 'Petugas PMR Otomatis Ditugaskan',
            'description' => "Petugas {$bestUser->name} terpilih otomatis (Skor Kesesuaian: {$best['score']}%, Jarak: ~{$best['distance']}m).",
            'created_at' => now(),
        ]);

        return $bestUser;
    }

    /**
     * Rumus Haversine untuk menghitung jarak antara 2 titik koordinat (dalam meter)
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
