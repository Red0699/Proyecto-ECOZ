<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte histórico — {{ $estacionNombre }}</title>

    <style>
        /* --- Página / tipografía --- */
        @page {
            margin: 28px 28px 42px;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #141414;
        }

        h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            line-height: 1.25;
        }

        h2 {
            font-size: 14px;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .muted {
            color: #6b7280;
        }

        /* --- Encabezado (logo + título) --- */
        .brand {
            display: table;
            width: 100%;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .brand .logo,
        .brand .title {
            display: table-cell;
            vertical-align: middle;
        }

        .brand .logo {
            width: 140px;
        }

        .brand .logo img {
            height: 42px;
        }

        .brand .title small {
            display: block;
            margin-top: 2px;
        }

        /* --- Tarjetas KPI --- */
        .grid {
            display: table;
            width: 100%;
            border-spacing: 12px 0;
            /* hueco horizontal visual */
        }

        .grid .col {
            display: table-cell;
            width: 25%;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            background: #fff;
        }

        .kpi-label {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 14px;
            font-weight: 700;
        }

        /* --- Tabla serie --- */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: #374151;
            background: #f3f4f6;
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
        }

        tbody tr:nth-child(odd) td {
            background: #fafafa;
        }

        /* --- Footer con numeración de páginas --- */
        footer {
            position: fixed;
            bottom: -6px;
            left: 0;
            right: 0;
            text-align: right;
            font-size: 10px;
            color: #9ca3af;
        }

        .pageno:after {
            content: counter(page) " / " counter(pages);
        }

        /* --- Chips/label ligeros --- */
        .chip {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            color: #374151;
            background: #f9fafb;
        }
    </style>
</head>

<body>

    {{-- Encabezado --}}
    <div class="brand">
        <div class="logo">
            @if(is_file($logoPath))
            <img src="{{ $logoPath }}" alt="Logo">
            @endif
        </div>
        <div class="title">
            <h1>Reporte histórico — {{ $estacionNombre }}</h1>
            <small class="muted">
                @if($mode==='year')
                Periodo: <span class="chip">Año {{ $year }}</span>
                @elseif($mode==='month')
                Periodo: <span class="chip">{{ \Carbon\Carbon::createFromDate($year,$month,1)->isoFormat('MMMM YYYY') }}</span>
                @else
                Rango: <span class="chip">{{ $from }}</span> → <span class="chip">{{ $to }}</span>
                @endif
            </small>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="grid">
        <div class="col">
            <div class="card">
                <div class="kpi-label">COV total</div>
                <div class="kpi-value">{{ number_format($kpis['cov_total_kg'] ?? 0, 3) }} kg</div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="kpi-label">CO₂ total</div>
                <div class="kpi-value">{{ number_format($kpis['co2_total_kg'] ?? 0, 3) }} kg</div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="kpi-label">Factor (CO₂/COV)</div>
                <div class="kpi-value">
                    @if(!empty($kpis['factor_cov_to_co2'])) {{ number_format($kpis['factor_cov_to_co2'], 4) }} kg
                    @else — @endif
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card">
                <div class="kpi-label">Último inventario</div>
                <div class="kpi-value">{{ $kpis['inventario_ultimo_str'] ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Mensaje sin datos (si aplica) --}}
    @isset($noDataMsg)
    @if($noDataMsg)
    <div class="card" style="margin-top:12px;">
        <strong>Resumen</strong><br>
        <span class="muted">{{ $noDataMsg }}</span>
    </div>
    @endif
    @endisset

    {{-- Serie resumida --}}
    @if(!empty($series['labels']))
    <div class="card" style="margin-top:12px;">
        <h2>Series (resumen)</h2>
        <table>
            <thead>
                <tr>
                    <th style="width:22%;">Periodo</th>
                    <th style="width:19%;">Inventario (gl)</th>
                    <th style="width:14%;">Psi máx</th>
                    <th style="width:23%;">COV (kg)</th>
                    <th style="width:22%;">CO₂ (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($series['labels'] as $i => $lab)
                <tr>
                    <td>{{ $lab }}</td>
                    <td>{{ number_format($series['inventario'][$i] ?? 0, 2) }}</td>
                    <td>{{ number_format($series['psi_max'][$i] ?? 0, 2) }}</td>
                    <td>{{ number_format($series['cov_kg'][$i] ?? 0, 3) }}</td>
                    <td>{{ number_format($series['co2_kg'][$i] ?? 0, 3) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer con numeración y fecha de generación --}}
    <footer>
        Generado: {{ now()->format('Y-m-d H:i') }}
    </footer>
</body>

</html>