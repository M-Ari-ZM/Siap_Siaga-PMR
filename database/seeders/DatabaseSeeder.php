<?php

namespace Database\Seeders;

use App\Models\Emergency;
use App\Models\EmergencyAssignment;
use App\Models\EmergencyTimeline;
use App\Models\HandlingReport;
use App\Models\Location;
use App\Models\PmrProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Preset Locations (Sekolah)
        $locations = [
            [
                'name' => 'Lapangan Utama',
                'building' => 'Area Luar Gedung',
                'latitude' => -6.20000000,
                'longitude' => 106.81666600,
                'description' => 'Lapangan upacara dan kegiatan olahraga terbuka.',
            ],
            [
                'name' => 'Ruang UKS',
                'building' => 'Gedung A - Lantai 1',
                'latitude' => -6.20015000,
                'longitude' => 106.81680000,
                'description' => 'Pusat penanganan medis dan istirahat siswa sakit.',
            ],
            [
                'name' => 'Kantin Sekolah',
                'building' => 'Area Belakang',
                'latitude' => -6.20050000,
                'longitude' => 106.81640000,
                'description' => 'Area kantin dan pujasera siswa.',
            ],
            [
                'name' => 'Laboratorium IPA',
                'building' => 'Gedung B - Lantai 2',
                'latitude' => -6.20025000,
                'longitude' => 106.81710000,
                'description' => 'Lab Biologi, Fisika, dan Kimia.',
            ],
            [
                'name' => 'Kelas X MIPA 1',
                'building' => 'Gedung C - Lantai 2',
                'latitude' => -6.20030000,
                'longitude' => 106.81730000,
                'description' => 'Ruang kelas lantai 2 Gedung C.',
            ],
            [
                'name' => 'Perpustakaan',
                'building' => 'Gedung A - Lantai 2',
                'latitude' => -6.20018000,
                'longitude' => 106.81690000,
                'description' => 'Ruang baca dan peminjaman buku.',
            ],
        ];

        foreach ($locations as $loc) {
            Location::create($loc);
        }

        $lapangan = Location::where('name', 'Lapangan Utama')->first();
        $kantin = Location::where('name', 'Kantin Sekolah')->first();

        // 2. Seed Users & Roles
        // Admin
        $admin = User::create([
            'nomor_induk' => 'ADMIN-001',
            'name' => 'Administrator PMR',
            'phone_number' => '081234567890',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // Student (Pelapor Warga Sekolah)
        $studentAri = User::create([
            'nomor_induk' => '20241001',
            'name' => 'Ari Siswa',
            'phone_number' => '081234567891',
            'role' => 'student',
            'password' => Hash::make('password'),
        ]);

        $studentSiti = User::create([
            'nomor_induk' => '20241002',
            'name' => 'Siti Nurhaliza',
            'phone_number' => '081234567892',
            'role' => 'student',
            'password' => Hash::make('password'),
        ]);

        // PMR Members
        $pmrBudi = User::create([
            'nomor_induk' => 'PMR-2024-001',
            'name' => 'Budi Santoso (PMR)',
            'phone_number' => '081234567893',
            'role' => 'pmr',
            'password' => Hash::make('password'),
        ]);

        PmrProfile::create([
            'user_id' => $pmrBudi->id,
            'nisn_or_member_id' => 'PMR-2024-001',
            'class_grade' => 'XI MIPA 2',
            'availability_status' => 'available',
            'is_on_duty' => true,
            'current_latitude' => -6.20010000,
            'current_longitude' => 106.81670000,
            'last_location_updated_at' => now(),
        ]);

        $pmrCitra = User::create([
            'nomor_induk' => 'PMR-2024-002',
            'name' => 'Citra Lestari (PMR)',
            'phone_number' => '081234567894',
            'role' => 'pmr',
            'password' => Hash::make('password'),
        ]);

        PmrProfile::create([
            'user_id' => $pmrCitra->id,
            'nisn_or_member_id' => 'PMR-2024-002',
            'class_grade' => 'XI IPS 1',
            'availability_status' => 'available',
            'is_on_duty' => true,
            'current_latitude' => -6.20040000,
            'current_longitude' => 106.81650000,
            'last_location_updated_at' => now(),
        ]);

        $pmrDimas = User::create([
            'nomor_induk' => 'PMR-2023-015',
            'name' => 'Dimas Pratama (PMR)',
            'phone_number' => '081234567895',
            'role' => 'pmr',
            'password' => Hash::make('password'),
        ]);

        PmrProfile::create([
            'user_id' => $pmrDimas->id,
            'nisn_or_member_id' => 'PMR-2023-015',
            'class_grade' => 'XII MIPA 1',
            'availability_status' => 'busy',
            'is_on_duty' => false,
            'current_latitude' => -6.20070000,
            'current_longitude' => 106.81700000,
            'last_location_updated_at' => now(),
        ]);

        // 3. Sample Emergency Laporan Aktif
        $emergencyActive = Emergency::create([
            'emergency_code' => 'EMG-' . date('Ymd') . '-001',
            'reporter_id' => $studentAri->id,
            'location_id' => $lapangan->id,
            'latitude' => $lapangan->latitude,
            'longitude' => $lapangan->longitude,
            'incident_type' => 'cedera',
            'description' => 'Siswa terjatuh saat lari olahraga, kaki kanan terkilir dan tidak bisa berdiri.',
            'status' => 'on_the_way',
            'reported_at' => now()->subMinutes(10),
        ]);

        EmergencyAssignment::create([
            'emergency_id' => $emergencyActive->id,
            'pmr_user_id' => $pmrBudi->id,
            'status' => 'accepted',
            'distance_meters' => 45,
            'dispatch_score' => 95.0,
            'offered_at' => now()->subMinutes(9),
            'responded_at' => now()->subMinutes(8),
        ]);

        EmergencyTimeline::create([
            'emergency_id' => $emergencyActive->id,
            'actor_id' => $studentAri->id,
            'status' => 'reported',
            'title' => 'Laporan Darurat Dikirim',
            'description' => 'Laporan cedera di Lapangan Utama telah dibuat oleh Ari.',
            'created_at' => now()->subMinutes(10),
        ]);

        EmergencyTimeline::create([
            'emergency_id' => $emergencyActive->id,
            'actor_id' => $pmrBudi->id,
            'status' => 'pmr_assigned',
            'title' => 'Petugas PMR Menerima',
            'description' => 'Budi Santoso menerima tugas dan bersiap membawa kotak P3K.',
            'created_at' => now()->subMinutes(8),
        ]);

        EmergencyTimeline::create([
            'emergency_id' => $emergencyActive->id,
            'actor_id' => $pmrBudi->id,
            'status' => 'on_the_way',
            'title' => 'Menuju Lokasi Kejadian',
            'description' => 'Budi sedang berjalan cepat menuju Lapangan Utama.',
            'created_at' => now()->subMinutes(6),
        ]);

        // 4. Sample Emergency Riwayat Terselesaikan (Resolved)
        $emergencyResolved = Emergency::create([
            'emergency_code' => 'EMG-' . date('Ymd', strtotime('-1 day')) . '-002',
            'reporter_id' => $studentSiti->id,
            'location_id' => $kantin->id,
            'latitude' => $kantin->latitude,
            'longitude' => $kantin->longitude,
            'incident_type' => 'pingsan',
            'description' => 'Siswa merasa pusing dan pingsan di depan kantin saat jam istirahat.',
            'status' => 'resolved',
            'reported_at' => now()->subDay()->setTime(10, 15),
            'resolved_at' => now()->subDay()->setTime(10, 45),
        ]);

        EmergencyAssignment::create([
            'emergency_id' => $emergencyResolved->id,
            'pmr_user_id' => $pmrCitra->id,
            'status' => 'completed',
            'distance_meters' => 30,
            'dispatch_score' => 92.0,
            'offered_at' => now()->subDay()->setTime(10, 16),
            'responded_at' => now()->subDay()->setTime(10, 17),
        ]);

        HandlingReport::create([
            'emergency_id' => $emergencyResolved->id,
            'pmr_user_id' => $pmrCitra->id,
            'action_taken' => 'Diberikan minyak kayu putih, dilonggarkan pakaian, dipindahkan ke ruang UKS dengan tandu, dan diberikan teh manis hangat.',
            'notes' => 'Siswa belum sarapan dari pagi. Kondisi sudah pulih dan dijemput orang tua.',
            'final_condition' => 'Pulih / Pulang bersama orang tua',
            'created_at' => now()->subDay()->setTime(10, 45),
        ]);

        EmergencyTimeline::create([
            'emergency_id' => $emergencyResolved->id,
            'actor_id' => $studentSiti->id,
            'status' => 'reported',
            'title' => 'Laporan Diterima',
            'description' => 'Laporan pingsan di Kantin Sekolah.',
            'created_at' => now()->subDay()->setTime(10, 15),
        ]);

        EmergencyTimeline::create([
            'emergency_id' => $emergencyResolved->id,
            'actor_id' => $pmrCitra->id,
            'status' => 'resolved',
            'title' => 'Penanganan Selesai',
            'description' => 'Penanganan selesai dan laporan tindakan telah diisi.',
            'created_at' => now()->subDay()->setTime(10, 45),
        ]);
    }
}
