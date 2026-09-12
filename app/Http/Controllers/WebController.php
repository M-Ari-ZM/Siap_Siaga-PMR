<?php

namespace App\Http\Controllers;

use App\Models\Emergency;
use App\Models\EmergencyAssignment;
use App\Models\EmergencyTimeline;
use App\Models\HandlingReport;
use App\Models\Location;
use App\Models\PmrProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebController extends Controller
{
    public function info()
    {
        return view('info');
    }

    public function studentDashboard()
    {
        $user = Auth::user();

        $activeEmergencies = Emergency::with(['location', 'activeAssignment.pmrUser'])
            ->where('reporter_id', $user->id)
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->latest('reported_at')
            ->get();

        $recentEmergencies = Emergency::with(['location'])
            ->where('reporter_id', $user->id)
            ->where('status', 'resolved')
            ->latest('reported_at')
            ->take(5)
            ->get();

        return view('student.dashboard', compact('user', 'activeEmergencies', 'recentEmergencies'));
    }

    public function createEmergency()
    {
        $locations = Location::all();
        return view('student.emergency-create', compact('locations'));
    }

    public function storeEmergency(Request $request, \App\Services\DispatchService $dispatchService)
    {
        $user = Auth::user();
        $locationId = $request->location_id === 'custom' ? null : $request->location_id;

        // Koordinat default (fallback paling akhir jika tidak ada GPS & tidak ada preset)
        $lat = null;
        $lng = null;

        // Prioritas 1: Gunakan koordinat dari preset lokasi jika tersedia
        if ($locationId) {
            $loc = Location::find($locationId);
            if ($loc && $loc->latitude && $loc->longitude) {
                $lat = $loc->latitude;
                $lng = $loc->longitude;
            }
        }

        // Prioritas 2: Gunakan koordinat GPS dari browser (dikirim lewat hidden input)
        // Ini aktif jika: (a) pilih "custom/manual", atau (b) preset tidak punya koordinat
        if (!$lat && $request->filled('latitude') && $request->filled('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;
        }

        $emergency = Emergency::create([
            'emergency_code' => 'EMG-' . date('Ymd') . '-' . rand(100, 999),
            'reporter_id' => $user->id,
            'location_id' => $locationId,
            'custom_location_name' => $request->custom_location_name,
            'latitude' => $lat,
            'longitude' => $lng,
            'incident_type' => $request->incident_type,
            'description' => $request->description,
            'status' => 'searching_pmr',
            'reported_at' => now(),
        ]);

        // Catat timeline awal
        EmergencyTimeline::create([
            'emergency_id' => $emergency->id,
            'actor_id' => $user->id,
            'status' => 'reported',
            'title' => 'Laporan Darurat Dikirim',
            'description' => "Laporan {$emergency->incident_type} telah masuk ke sistem tanggap darurat.",
            'created_at' => now(),
        ]);

        // Jalankan Algoritma Dispatch Cerdas PMR Terdekat & Sesuai
        $assignedPmr = $dispatchService->dispatchBestPmr($emergency);

        return redirect()->route('student.emergency.show', $emergency->id);
    }

    public function getEmergencyLiveStatus(Emergency $emergency)
    {
        $user = Auth::user();

        if ($user->role === 'student' && $emergency->reporter_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $emergency->load(['activeAssignment.pmrUser.pmrProfile', 'timelines', 'handlingReport']);

        return response()->json([
            'status' => $emergency->status,
            'status_label' => strtoupper(str_replace('_', ' ', $emergency->status)),
            'pmr_name' => $emergency->activeAssignment->pmrUser->name ?? null,
            'pmr_phone' => $emergency->activeAssignment->pmrUser->phone_number ?? null,
            'distance_meters' => $emergency->activeAssignment->distance_meters ?? null,
            'dispatch_score' => $emergency->activeAssignment->dispatch_score ?? null,
            'timelines_count' => $emergency->timelines->count(),
            'is_resolved' => $emergency->status === 'resolved',
        ]);
    }

    public function showEmergency(Emergency $emergency)
    {
        $user = Auth::user();

        // Ensure student can only view their own emergency, while PMR/Admin can view all
        if ($user->role === 'student' && $emergency->reporter_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke laporan ini.');
        }

        $emergency->load(['reporter', 'location', 'activeAssignment.pmrUser.pmrProfile', 'timelines', 'handlingReport']);
        return view('student.emergency-status', compact('emergency'));
    }

    public function pmrDashboard()
    {
        $pmrUser = Auth::user();

        // Emergency yang di-assign ke PMR ini, atau yang belum di-assign (bisa diambil siapapun)
        $assignedIds = $pmrUser->pmrAssignments()->pluck('emergency_id');

        $activeEmergencies = Emergency::with(['reporter', 'location', 'activeAssignment.pmrUser'])
            ->whereNotIn('status', ['resolved', 'cancelled'])
            ->where(function ($q) use ($pmrUser, $assignedIds) {
                // Tampilkan: (1) Yang di-assign ke saya, atau (2) Yang belum punya petugas sama sekali
                $q->whereIn('id', $assignedIds)
                  ->orWhereDoesntHave('activeAssignment');
            })
            ->latest('reported_at')
            ->get();

        // Riwayat penanganan yang diselesaikan oleh PMR ini sendiri
        $handledEmergencies = Emergency::with(['reporter', 'location'])
            ->where('status', 'resolved')
            ->whereIn('id', $assignedIds)
            ->latest('resolved_at')
            ->take(10)
            ->get();

        return view('pmr.dashboard', compact('pmrUser', 'activeEmergencies', 'handledEmergencies'));
    }

    public function toggleAvailability(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'available');

        if ($user->pmrProfile) {
            $user->pmrProfile->update([
                'availability_status' => $status,
                'last_location_updated_at' => now(),
            ]);
        }

        return back()->with('status', 'Status ketersediaan berhasil diperbarui ke ' . strtoupper($status));
    }

    public function updatePmrLocation(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($user->pmrProfile) {
            $user->pmrProfile->update([
                'current_latitude' => $request->latitude,
                'current_longitude' => $request->longitude,
                'last_location_updated_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function acceptEmergency(Emergency $emergency)
    {
        $pmrUser = Auth::user();
        $emergency->update(['status' => 'on_the_way']);

        EmergencyTimeline::create([
            'emergency_id' => $emergency->id,
            'actor_id' => $pmrUser->id,
            'status' => 'on_the_way',
            'title' => 'PMR Menuju Lokasi',
            'description' => $pmrUser->name . ' telah menerima panggilan dan bergerak cepat menuju lokasi kejadian.',
            'created_at' => now(),
        ]);

        return back();
    }

    public function updateEmergencyStatus(Request $request, Emergency $emergency)
    {
        $pmrUser = Auth::user();
        $status = $request->status ?? 'handling';
        $emergency->update(['status' => $status]);

        EmergencyTimeline::create([
            'emergency_id' => $emergency->id,
            'actor_id' => $pmrUser->id,
            'status' => $status,
            'title' => 'Penanganan Dimulai',
            'description' => $pmrUser->name . ' telah tiba di lokasi dan sedang memberikan pertolongan medis.',
            'created_at' => now(),
        ]);

        return back();
    }

    public function showHandlingReportForm(Emergency $emergency)
    {
        $emergency->load(['reporter', 'location']);
        return view('pmr.handling-report', compact('emergency'));
    }

    public function storeHandlingReport(Request $request, Emergency $emergency)
    {
        $pmrUser = Auth::user();

        HandlingReport::create([
            'emergency_id' => $emergency->id,
            'pmr_user_id' => $pmrUser->id,
            'action_taken' => $request->action_taken,
            'notes' => $request->notes,
            'final_condition' => $request->final_condition,
        ]);

        $emergency->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        EmergencyTimeline::create([
            'emergency_id' => $emergency->id,
            'actor_id' => $pmrUser->id,
            'status' => 'resolved',
            'title' => 'Penanganan Selesai & Laporan Tercatat',
            'description' => 'Penanganan selesai: ' . $request->final_condition,
            'created_at' => now(),
        ]);

        return redirect()->route('pmr.dashboard');
    }

    public function adminDashboard()
    {
        $emergencies = Emergency::with(['reporter', 'location', 'activeAssignment.pmrUser'])
            ->latest('reported_at')
            ->get();

        $stats = [
            'total_emergencies' => Emergency::count(),
            'active_emergencies' => Emergency::whereNotIn('status', ['resolved', 'cancelled'])->count(),
            'resolved_emergencies' => Emergency::where('status', 'resolved')->count(),
            'total_pmr' => User::where('role', 'pmr')->count(),
            'available_pmr' => PmrProfile::where('availability_status', 'available')->count(),
        ];

        return view('admin.dashboard', compact('emergencies', 'stats'));
    }
}
