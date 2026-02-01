<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Hasil Penilaian - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background: #f5f5f5;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background: #f5f5f5;
            font-weight: bold;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            width: 30%;
            padding: 5px 0;
            color: #666;
        }
        .info-value {
            display: table-cell;
            padding: 5px 0;
            font-weight: bold;
        }
        .score-box {
            text-align: center;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            margin: 15px 0;
        }
        .score-box .score {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        .score-box .label {
            color: #666;
            margin-top: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .grid-3 {
            display: table;
            width: 100%;
        }
        .grid-3-item {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN HASIL PENILAIAN KINERJA GURU</h1>
        <p>SPK Penilaian Guru - Metode AHP</p>
    </div>

    <div class="section">
        <div class="section-title">Informasi Guru</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Lengkap</div>
                <div class="info-value">{{ $user->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">NIP/NIK</div>
                <div class="info-value">{{ $teacher->employee_no ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Mata Pelajaran</div>
                <div class="info-value">{{ $teacher->subject ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Status Kepegawaian</div>
                <div class="info-value">{{ $teacher->employment_status ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Informasi Periode</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nama Periode</div>
                <div class="info-value">{{ $result->period->name ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tahun Akademik</div>
                <div class="info-value">{{ $result->period->academic_year ?? '-' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Semester</div>
                <div class="info-value">{{ $result->period->semester ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Hasil Penilaian</div>
        <div class="grid-3">
            <div class="grid-3-item">
                <div style="font-size: 24px; font-weight: bold;">{{ number_format($result->final_score ?? 0, 2) }}</div>
                <div style="color: #666;">Skor Akhir</div>
            </div>
            <div class="grid-3-item">
                <div style="font-size: 24px; font-weight: bold;">{{ $result->rank ?? '-' }}</div>
                <div style="color: #666;">Ranking</div>
            </div>
            <div class="grid-3-item">
                <div style="font-size: 24px; font-weight: bold;">{{ $result->grade ?? '-' }}</div>
                <div style="color: #666;">Grade</div>
            </div>
        </div>
    </div>

    @if(!empty($criteriaScores))
    <div class="section">
        <div class="section-title">Breakdown Skor per Kriteria</div>
        <table>
            <thead>
                <tr>
                    <th>Kriteria</th>
                    <th style="text-align: center;">Bobot</th>
                    <th style="text-align: center;">Skor</th>
                    <th style="text-align: center;">Skor Terbobot</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criteriaScores as $criteria => $data)
                    <tr>
                        <td>{{ $criteria }}</td>
                        <td style="text-align: center;">{{ isset($data['weight']) ? number_format($data['weight'] * 100, 1) . '%' : '-' }}</td>
                        <td style="text-align: center;">{{ number_format($data['score'] ?? 0, 2) }}</td>
                        <td style="text-align: center;">{{ isset($data['weighted_score']) ? number_format($data['weighted_score'], 2) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($assessments->isNotEmpty())
    <div class="section">
        <div class="section-title">Daftar Penilai</div>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Penilai</th>
                    <th>Jabatan</th>
                    <th>Tanggal Penilaian</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $index => $assessment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $assessment->assessor->user->name ?? '-' }}</td>
                        <td>{{ $assessment->assessor->title ?? 'Tim Penilai' }}</td>
                        <td>{{ $assessment->submitted_at?->format('d M Y') ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh sistem SPK Penilaian Guru AHP</p>
        <p>Tanggal cetak: {{ now()->format('d M Y H:i') }}</p>
    </div>
</body>
</html>
