<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Progress Penilaian</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
        line-height: 1.6;
        color: #333;
        padding: 20px;
    }

    .header {
        text-align: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 3px solid #333;
    }

    .header h1 {
        font-size: 20px;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .header .period {
        font-size: 14px;
        color: #666;
        margin: 5px 0;
    }

    .info-section {
        margin-bottom: 20px;
    }

    .info-row {
        display: flex;
        margin-bottom: 5px;
    }

    .info-label {
        font-weight: bold;
        width: 150px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }

    .stat-card {
        border: 1px solid #ddd;
        padding: 15px;
        text-align: center;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .stat-card .label {
        font-size: 11px;
        color: #666;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .stat-card .value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #333;
        color: white;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 11px;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .status-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .status-in_progress {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .status-submitted,
    .status-finalized {
        background-color: #d1fae5;
        color: #065f46;
    }

    .footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
        text-align: right;
        font-size: 11px;
        color: #666;
    }

    .no-print {
        margin-bottom: 20px;
    }

    @media print {
        .no-print {
            display: none;
        }

        body {
            padding: 0;
        }
    }
    </style>
</head>

<body>


    <div class="header">
        <h1>Laporan Progress Penilaian Guru</h1>
        <div class="period">{{ $period->name }} - {{ $period->academic_year }} ({{ $period->semester }})</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Periode Penilaian:</div>
            <div>{{ $period->scoring_open_at?->format('d M Y') }} - {{ $period->scoring_close_at?->format('d M Y') }}
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Status Periode:</div>
            <div>{{ ucfirst($period->status) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Export:</div>
            <div>{{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total Penilaian</div>
            <div class="value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Pending</div>
            <div class="value">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">In Progress</div>
            <div class="value">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Completed</div>
            <div class="value">{{ $stats['completed'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Nama Guru</th>
                <th style="width: 25%;">Penilai</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 15%;">Tanggal Mulai</th>
                <th style="width: 15%;">Tanggal Selesai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assessments as $index => $assessment)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $assessment->teacher->user->name ?? '-' }}</td>
                <td>{{ $assessment->assessor->user->name ?? '-' }}</td>
                <td>
                    <span class="status-badge status-{{ $assessment->status }}">
                        {{ ucfirst(str_replace('_', ' ', $assessment->status)) }}
                    </span>
                </td>
                <td>{{ $assessment->started_at?->format('d M Y H:i') ?? '-' }}</td>
                <td>{{ $assessment->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                    Belum ada data penilaian untuk periode ini
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <div>Progress Rate:
            {{ $stats['total'] > 0 ? number_format(($stats['completed'] / $stats['total']) * 100, 1) : 0 }}%</div>
        <div>Dicetak pada: {{ now()->format('d M Y H:i:s') }}</div>
    </div>
    <div class="no-print"
        style="position: fixed; top: 10px; right: 10px; background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <button onclick="window.print()"
            style="padding: 10px 20px; cursor: pointer; background: #4a5568; color: white; border: none; border-radius: 5px;">
            Print / Save as PDF
        </button>
    </div>
</body>

</html>
