<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Bobot AHP</title>
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
        margin-bottom: 30px;
    }

    .info-row {
        display: flex;
        margin-bottom: 5px;
    }

    .info-label {
        font-weight: bold;
        width: 150px;
    }

    .cr-section {
        background-color: #f0f9ff;
        border: 2px solid #0284c7;
        padding: 15px;
        margin-bottom: 30px;
        border-radius: 5px;
    }

    .cr-section .cr-label {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .cr-section .cr-value {
        font-size: 24px;
        font-weight: bold;
        color: #0284c7;
    }

    .cr-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
        margin-top: 10px;
    }

    .cr-consistent {
        background-color: #d1fae5;
        color: #065f46;
    }

    .cr-inconsistent {
        background-color: #fee2e2;
        color: #991b1b;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 12px;
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

    .weight-bar {
        background-color: #e5e7eb;
        height: 20px;
        border-radius: 3px;
        overflow: hidden;
        position: relative;
    }

    .weight-fill {
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
        font-weight: bold;
    }

    .comparison-matrix {
        margin-top: 30px;
    }

    .comparison-matrix h3 {
        margin-bottom: 15px;
        font-size: 14px;
        text-transform: uppercase;
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

    .notes {
        background-color: #fef3c7;
        border-left: 4px solid #f59e0b;
        padding: 15px;
        margin-top: 30px;
        font-size: 11px;
    }

    .notes strong {
        display: block;
        margin-bottom: 5px;
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
        <h1>Laporan Bobot AHP</h1>
        <div class="period">{{ $period->name }} - {{ $period->academic_year }} ({{ $period->semester }})</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">Model AHP:</div>
            <div>{{ $ahpModel->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div>{{ $ahpModel->status === 'finalized' ? 'Finalized' : 'Draft' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Finalisasi:</div>
            <div>{{ $ahpModel->finalized_at?->format('d M Y H:i') ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal Export:</div>
            <div>{{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    <div class="cr-section">
        <div class="cr-label">Consistency Ratio (CR)</div>
        <div class="cr-value">{{ number_format($ahpModel->consistency_ratio ?? 0, 4) }}</div>
        @if($ahpModel->consistency_ratio !== null)
        @if($ahpModel->consistency_ratio <= 0.1) <span class="cr-status cr-consistent">✓ Konsisten (CR ≤ 0.1)</span>
            @else
            <span class="cr-status cr-inconsistent">✗ Tidak Konsisten (CR > 0.1)</span>
            @endif
            @endif
    </div>

    <h3 style="font-size: 14px; margin-bottom: 15px; text-transform: uppercase;">Bobot Kriteria</h3>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Kriteria</th>
                <th style="width: 15%;">Bobot</th>
                <th style="width: 15%;">Persentase</th>
                <th style="width: 30%;">Visualisasi</th>
            </tr>
        </thead>
        <tbody>
            @php $totalWeight = 0; @endphp
            @forelse($criteria as $index => $criterion)
            @php
            $weight = $weights->get($criterion->id);
            $weightValue = $weight ? $weight->weight : 0;
            $totalWeight += $weightValue;
            @endphp
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td><strong>{{ $criterion->name }}</strong></td>
                <td style="text-align: center;">{{ number_format($weightValue, 4) }}</td>
                <td style="text-align: center;"><strong>{{ number_format($weightValue * 100, 2) }}%</strong></td>
                <td>
                    <div class="weight-bar">
                        <div class="weight-fill" style="width: {{ $weightValue * 100 }}%;">
                            @if($weightValue > 0.15)
                            {{ number_format($weightValue * 100, 1) }}%
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px; color: #999;">
                    Belum ada kriteria untuk model AHP ini
                </td>
            </tr>
            @endforelse
            @if($criteria->isNotEmpty())
            <tr style="background-color: #f3f4f6; font-weight: bold;">
                <td colspan="2" style="text-align: right;">Total:</td>
                <td style="text-align: center;">{{ number_format($totalWeight, 4) }}</td>
                <td style="text-align: center;">{{ number_format($totalWeight * 100, 2) }}%</td>
                <td></td>
            </tr>
            @endif
        </tbody>
    </table>

    @if($comparisons->isNotEmpty())
    <div class="comparison-matrix">
        <h3>Perbandingan Berpasangan</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">Kriteria A</th>
                    <th style="width: 40%;">Kriteria B</th>
                    <th style="width: 20%;">Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comparisons->take(20) as $comparison)
                <tr>
                    <td>{{ $comparison->criterionA->name ?? '-' }}</td>
                    <td>{{ $comparison->criterionB->name ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $comparison->value }}</td>
                </tr>
                @endforeach
                @if($comparisons->count() > 20)
                <tr>
                    <td colspan="3" style="text-align: center; color: #999; font-style: italic;">
                        ... dan {{ $comparisons->count() - 20 }} perbandingan lainnya
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endif

    <div class="notes">
        <strong>Catatan:</strong>
        <ul style="margin-left: 20px; margin-top: 5px;">
            <li>Consistency Ratio (CR) mengukur konsistensi perbandingan berpasangan</li>
            <li>CR ≤ 0.1 dianggap konsisten dan dapat diterima</li>
            <li>Jika CR > 0.1, perlu dilakukan revisi pada perbandingan berpasangan</li>
            <li>Total bobot harus = 1.0000 (100%)</li>
        </ul>
    </div>

    <div class="footer">
        <div>Status Model: {{ $ahpModel->status === 'finalized' ? 'Finalized & Locked' : 'Draft' }}</div>
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
