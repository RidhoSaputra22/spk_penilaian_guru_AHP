<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Penilaian - {{ $period->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .period-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }

        .period-info table {
            width: 100%;
        }

        .period-info td {
            padding: 5px;
        }

        .period-info td:first-child {
            width: 150px;
            font-weight: bold;
        }

        table.results {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table.results th,
        table.results td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table.results th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
        }

        table.results tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.results tr:hover {
            background-color: #f1f1f1;
        }

        .rank-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }

        .rank-1 {
            background-color: #ffd700;
            color: #000;
        }

        .rank-2 {
            background-color: #c0c0c0;
            color: #000;
        }

        .rank-3 {
            background-color: #cd7f32;
            color: #fff;
        }

        .grade-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }

        .grade-A { background-color: #10b981; }
        .grade-B { background-color: #3b82f6; }
        .grade-C { background-color: #f59e0b; }
        .grade-D { background-color: #ef4444; }
        .grade-E { background-color: #991b1b; }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
            color: #666;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }

            @page {
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN HASIL PENILAIAN KINERJA GURU</h1>
        <h2>{{ auth()->user()->institution->name ?? 'MADRASAH' }}</h2>
    </div>

    <div class="period-info">
        <table>
            <tr>
                <td>Periode Penilaian</td>
                <td>: {{ $period->name }}</td>
            </tr>
            <tr>
                <td>Tahun Akademik</td>
                <td>: {{ $period->academic_year }}</td>
            </tr>
            <tr>
                <td>Semester</td>
                <td>: {{ $period->semester }}</td>
            </tr>
            <tr>
                <td>Total Guru Dinilai</td>
                <td>: {{ $results->count() }} orang</td>
            </tr>
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ now()->format('d F Y H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <table class="results">
        <thead>
            <tr>
                <th class="text-center" style="width: 50px;">Rank</th>
                <th>Nama Guru</th>
                <th style="width: 120px;">NIP</th>
                @foreach($criteria as $criterion)
                <th class="text-center" style="width: 80px;">{{ $criterion->code }}</th>
                @endforeach
                <th class="text-center" style="width: 80px;">Nilai Akhir</th>
                <th class="text-center" style="width: 60px;">Grade</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td class="text-center">
                    @if($result->rank <= 3)
                        <span class="rank-badge rank-{{ $result->rank }}">
                            #{{ $result->rank }}
                        </span>
                    @else
                        #{{ $result->rank }}
                    @endif
                </td>
                <td>{{ $result->teacher->user->name ?? '-' }}</td>
                <td>{{ $result->teacher->nip ?? '-' }}</td>
                @foreach($criteria as $criterion)
                <td class="text-center">
                    @php
                        $criteriaScore = $result->criteriaScores->firstWhere('criteria_node_id', $criterion->id);
                    @endphp
                    {{ number_format($criteriaScore->weighted_score ?? 0, 2) }}
                </td>
                @endforeach
                <td class="text-center">
                    <strong>{{ number_format($result->final_score, 2) }}</strong>
                </td>
                <td class="text-center">
                    <span class="grade-badge grade-{{ $result->grade }}">
                        {{ $result->grade }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dicetak oleh: {{ auth()->user()->name }}</p>
        <p>Sistem Penilaian Kinerja Guru</p>
    </div>

    <div class="no-print" style="position: fixed; top: 10px; right: 10px; background: white; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #4a5568; color: white; border: none; border-radius: 5px;">
            Print / Save as PDF
        </button>
    </div>
</body>
</html>
