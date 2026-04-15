<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Aktivitas - Sisirine™</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
        }

        h1 {
            margin: 0;
            color: #1e40af;
            font-size: 24px;
        }

        .subtitle {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }

        .export-date {
            text-align: right;
            margin-bottom: 20px;
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        thead {
            background-color: #1e40af;
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }

        td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }

        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        tbody tr:hover {
            background-color: #f0f9ff;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }

        .stats {
            margin-bottom: 20px;
            display: flex;
            gap: 20px;
            justify-content: flex-start;
        }

        .stat-item {
            padding: 10px 15px;
            background-color: #f3f4f6;
            border-left: 4px solid #1e40af;
            border-radius: 4px;
        }

        .stat-label {
            font-size: 11px;
            color: #666;
            font-weight: bold;
        }

        .stat-value {
            font-size: 16px;
            color: #1e40af;
            font-weight: bold;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <header>
        <h1>📋 Riwayat Aktivitas Sirine</h1>
        <p class="subtitle">Laporan Lengkap Aktivitas Sistem Sisirine™</p>
    </header>

    <div class="export-date">
        Diekspor pada: {{ now()->format('d F Y H:i:s') }}
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-label">Total Data</div>
            <div class="stat-value">{{ count($logs) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 20%">Waktu</th>
                <th style="width: 20%">Pengguna</th>
                <th style="width: 15%">Jenis Aktivitas</th>
                <th style="width: 20%">IP Address</th>
                <th style="width: 20%">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $log->event_time->format('d-m-Y H:i:s') }}</td>
                    <td>{{ $log->user->name ?? 'Unknown' }}</td>
                    <td>{{ ucfirst($log->action) }}</td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                    <td>{{ $log->description ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} Sisirine™ - Sistem Informasi Sirine Seloich</p>
        <p>Dokumen ini dicetak secara otomatis dan sah untuk keperluan administratif</p>
    </div>
</body>
</html>
