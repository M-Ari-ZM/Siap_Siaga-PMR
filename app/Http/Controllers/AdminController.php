<?php

namespace App\Http\Controllers;

use App\Models\Emergency;
use App\Models\HandlingReport;
use App\Models\Location;
use App\Models\PmrProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $query = Emergency::with(['reporter', 'location', 'activeAssignment.pmrUser', 'handlingReport']);

        // Filter status / type if requested
        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $emergencies = $query->latest('reported_at')->get();

        $locations = Location::withCount('emergencies')->get();
        $pmrMembers = User::where('role', 'pmr')->with('pmrProfile')->get();

        $stats = [
            'total_emergencies' => Emergency::count(),
            'active_emergencies' => Emergency::whereNotIn('status', ['resolved', 'cancelled'])->count(),
            'resolved_emergencies' => Emergency::where('status', 'resolved')->count(),
            'total_pmr' => User::where('role', 'pmr')->count(),
            'available_pmr' => PmrProfile::where('availability_status', 'available')->count(),
            'on_duty_pmr' => PmrProfile::where('is_on_duty', true)->count(),
        ];

        return view('admin.dashboard', compact('emergencies', 'locations', 'pmrMembers', 'stats'));
    }

    // --- MANAJEMEN LOKASI SEKOLAH ---
    public function storeLocation(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        Location::create($validated);

        return back()->with('status', 'Lokasi sekolah baru berhasil ditambahkan.');
    }

    public function updateLocation(Request $request, Location $location)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $location->update($validated);

        return back()->with('status', 'Data lokasi berhasil diperbarui.');
    }

    public function destroyLocation(Location $location)
    {
        $location->delete();
        return back()->with('status', 'Lokasi berhasil dihapus.');
    }

    // --- MANAJEMEN ANGGOTA & JADWAL PIKET PMR ---
    public function storePmrMember(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nisn_or_member_id' => 'required|string|max:50|unique:users,nomor_induk',
            'phone_number' => 'required|string|max:20',
            'class_grade' => 'required|string|max:50',
            'password' => 'required|min:6',
        ], [
            'nisn_or_member_id.unique' => 'Nomor ID Anggota / NISN ini sudah terdaftar sebagai akun pengguna.',
        ]);

        $user = User::create([
            'nomor_induk' => $validated['nisn_or_member_id'],
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'role' => 'pmr',
            'password' => Hash::make($validated['password']),
        ]);

        PmrProfile::create([
            'user_id' => $user->id,
            'nisn_or_member_id' => $validated['nisn_or_member_id'],
            'class_grade' => $validated['class_grade'],
            'availability_status' => 'available',
            'is_on_duty' => true,
            'last_location_updated_at' => now(),
        ]);

        return back()->with('status', 'Anggota PMR baru berhasil didaftarkan.');
    }

    public function togglePmrDuty(User $user)
    {
        if ($user->pmrProfile) {
            $user->pmrProfile->update([
                'is_on_duty' => !$user->pmrProfile->is_on_duty,
            ]);
        }

        return back()->with('status', 'Status jadwal piket berhasil diperbarui.');
    }

    // --- EXPORT / REKAP CETAK LAPORAN MEDIS ---
    public function exportReport(Request $request)
    {
        $emergencies = Emergency::with(['reporter', 'location', 'activeAssignment.pmrUser', 'handlingReport'])
            ->where('status', 'resolved')
            ->latest('reported_at')
            ->get();

        return view('admin.export-report', compact('emergencies'));
    }
}
