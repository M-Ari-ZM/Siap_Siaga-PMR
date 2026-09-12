<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Laporan Kejadian & Penanganan Medis PMR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #1e293b;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #dc2626;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #dc2626;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 10px;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 10px;
            background-color: #e2e8f0;
        }
        .badge-emergency { background-color: #fee2e2; color: #991b1b; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
        <button onclick="window.print()" style="background-color: #dc2626; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak / Simpan PDF
        </button>
        <span style="font-size: 12px; color: #64748b;">Total {{ $emergencies->count() }} Laporan Medis Tercatat</span>
    </div>

    <div class="header">
        <h1>Palang Merah Remaja (PMR) — Siap Siaga</h1>
        <p>Rekapitulasi Penanganan Insiden & Kasus Darurat Medis Sekolah</p>
        <p style="font-size: 11px; margin-top: 5px;">Dicetak pada: {{ date('d F Y, H:i') }} WIB</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode & Waktu</th>
                <th>Pelapor</th>
                <th>Jenis & Lokasi</th>
                <th>Petugas PMR</th>
                <th>Tindakan Penanganan</th>
                <th>Kondisi Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($emergencies as $index => $emg)
            <tr>
                <td style="text-align: center; width: 25px;">{{ $index + 1 }}</td>
                <td style="width: 110px;">
                    <strong>#{{ $emg->emergency_code }}</strong><br>
                    <span style="color: #64748b;">{{ $emg->reported_at->format('d/m/Y H:i') }}</span>
                </td>
                <td style="width: 100px;">
                    <strong>{{ $emg->reporter->name }}</strong><br>
                    <span style="color: #64748b;">{{ $emg->reporter->phone_number ?? '-' }}</span>
                </td>
                <td style="width: 140px;">
                    <span class="badge badge-emergency">{{ strtoupper($emg->incident_type) }}</span><br>
                    <strong style="margin-top: 3px; display: inline-block;">{{ $emg->location_display }}</strong>
                </td>
                <td style="width: 110px;">
                    {{ $emg->activeAssignment->pmrUser->name ?? '-' }}
                </td>
                <td>
                    @if($emg->handlingReport)
                        <strong>Tindakan:</strong> {{ $emg->handlingReport->action_taken }}<br>
                        @if($emg->handlingReport->notes)
                            <em style="color: #64748b;">Catatan: {{ $emg->handlingReport->notes }}</em>
                        @endif
                    @else
                        <span style="color: #94a3b8; font-style: italic;">Laporan belum diisi</span>
                    @endif
                </td>
                <td style="width: 120px;">
                    <span class="badge badge-success">{{ $emg->handlingReport->final_condition ?? 'Resolved' }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 20px;">Belum ada rekap laporan insiden.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
