<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            color: #1a202c;
            background: #fff;
            line-height: 1.5;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 8px;
            page-break-after: avoid;
        }
        
        .header h1 {
            font-size: 28pt;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .header-meta {
            font-size: 10pt;
            opacity: 0.9;
        }
        
        .kpi-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
            border-spacing: 15px;
            page-break-inside: avoid;
        }
        
        .kpi-card {
            display: table-cell;
            width: 25%;
            border: 2px solid #e2e8f0;
            padding: 20px;
            background: #f7fafc;
            border-radius: 8px;
            vertical-align: top;
        }
        
        .kpi-card.avancement { border-left: 5px solid #3b82f6; }
        .kpi-card.absence { border-left: 5px solid #ef4444; }
        .kpi-card.emploi { border-left: 5px solid #a855f7; }
        
        .kpi-label {
            font-size: 9pt;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .kpi-value {
            font-size: 24pt;
            font-weight: bold;
            color: #1a202c;
            margin-bottom: 5px;
        }
        
        .section-title {
            font-size: 16pt;
            font-weight: bold;
            color: #2d3748;
            margin-top: 30px;
            margin-bottom: 15px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 8px;
            page-break-after: avoid;
        }
        
        .section {
            page-break-inside: avoid;
            margin-bottom: 25px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        
        .data-table thead {
            background: #2d3748;
            color: white;
        }
        
        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #e2e8f0;
        }
        
        .data-table td {
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #f7fafc;
        }
        
        .data-table tbody tr:hover {
            background: #edf2f7;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 9pt;
            font-weight: bold;
            white-space: nowrap;
        }
        
        .badge-green { background: #dcfce7; color: #166534; }
        .badge-blue { background: #dbeafe; color: #1e40af; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-purple { background: #f3e8ff; color: #6b21a8; }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin: 5px 0;
        }
        
        .progress-fill {
            height: 100%;
            background: #667eea;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e2e8f0;
            font-size: 9pt;
            color: #718096;
            text-align: center;
            page-break-before: avoid;
        }
        
        .chart-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .chart-image {
            width: 100%;
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        
        @page {
            size: A4 portrait;
            margin: 15mm;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .no-data {
            padding: 20px;
            text-align: center;
            color: #718096;
            background: #f7fafc;
            border: 1px dashed #cbd5e0;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        /* Avancement par groupe — lisibilité */
        .table-avancement-groupes {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            font-size: 10pt;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08);
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .table-avancement-groupes thead {
            background: #1e293b;
            color: #fff;
        }
        .table-avancement-groupes th {
            padding: 14px 14px;
            text-align: left;
            font-weight: bold;
            border: none;
            vertical-align: middle;
        }
        .table-avancement-groupes th.col-hours,
        .table-avancement-groupes td.col-hours {
            text-align: right;
        }
        .table-avancement-groupes td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            background: #fff;
            vertical-align: middle;
        }
        .table-avancement-groupes tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .pdf-hours-value {
            display: inline-block;
            font-weight: bold;
            color: #1e40af;
            background: #eff6ff;
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #bfdbfe;
        }
        .data-table td.pdf-col-hours {
            text-align: right;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>{{ $reportName }}</h1>
        <div class="header-meta">
            Généré le {{ $date }} | Type: {{ $reportType }}
            @if($dateFrom && $dateTo)
                | Période: {{ $dateFrom }} - {{ $dateTo }}
            @endif
        </div>
    </div>

    {{-- KPI CARDS (Always show) --}}
    <div class="kpi-section">
        <div class="kpi-card avancement">
            <div class="kpi-label">Total Formateurs</div>
            <div class="kpi-value">{{ $metrics['total_formateurs'] ?? $metrics['totalFormateurs'] ?? 0 }}</div>
        </div>
        <div class="kpi-card absence">
            <div class="kpi-label">Total Groupes</div>
            <div class="kpi-value">{{ $metrics['total_groupes'] ?? $metrics['totalGroupes'] ?? 0 }}</div>
        </div>
        <div class="kpi-card emploi">
            <div class="kpi-label">Modules Actifs</div>
            <div class="kpi-value">{{ $metrics['modules_actifs'] ?? $metrics['modulesActifs'] ?? 0 }}</div>
        </div>
        <div class="kpi-card avancement">
            <div class="kpi-label">Taux d'Absence</div>
            <div class="kpi-value">{{ $metrics['tauxAbsence'] ?? 0 }}%</div>
        </div>
    </div>

    {{-- SECTION AVANCEMENT --}}
    @if($sections['avancement'] ?? false)
    <div class="section">
        <h2 class="section-title">📊 Avancement par Groupe</h2>
        
        @if(!empty($groupes))
        <table class="table-avancement-groupes">
            <thead>
                <tr>
                    <th style="width:26%;">Groupe</th>
                    <th class="col-hours" style="width:16%;">Total heures</th>
                    <th style="width:14%;">Présentiel</th>
                    <th style="width:14%;">Distance</th>
                    <th style="width:16%;">Statut</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupes as $groupe)
                @php
                    $totalHeuresGroupe = isset($groupe['total_heures']) ? (float) $groupe['total_heures'] : 0.0;
                    if ($totalHeuresGroupe <= 0 && !empty($groupe['modules']) && is_array($groupe['modules'])) {
                        $totalHeuresGroupe = (float) collect($groupe['modules'])->sum(fn ($m) => (float) ($m['volumeHoraire'] ?? 0));
                    }
                @endphp
                <tr>
                    <td><strong>{{ $groupe['nomGroupe'] ?? $groupe['name'] ?? 'N/A' }}</strong></td>
                    <td class="col-hours"><span class="pdf-hours-value">{{ rtrim(rtrim(number_format($totalHeuresGroupe, 2, ',', ' '), '0'), ',') }}h</span></td>
                    <td>{{ $groupe['avancement_presentiel'] ?? 0 }}%</td>
                    <td>{{ $groupe['avancement_distanciel'] ?? 0 }}%</td>
                    <td>
                        @php
                            $adv = $groupe['advancement'] ?? $groupe['progress'] ?? 0;
                        @endphp
                        @if($adv >= 75)
                            <span class="badge badge-green">Terminé</span>
                        @elseif($adv >= 40)
                            <span class="badge badge-blue">En cours</span>
                        @else
                            <span class="badge badge-red">Retard</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Aucune donnée d'avancement disponible</div>
        @endif
    </div>
    @endif

    {{-- SECTION ABSENCES --}}
    @if($sections['absence'] ?? false)
    <div class="section">
        <h2 class="section-title">⚠️ Absences Détaillées</h2>
        
        @if(!empty($absences))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:20%;">Date</th>
                    <th style="width:20%;">Formateur/Groupe</th>
                    <th style="width:20%;">Type</th>
                    <th style="width:30%;">Motif</th>
                    <th style="width:10%;">Durée</th>
                </tr>
            </thead>
            <tbody>
                @foreach($absences as $absence)
                <tr>
                    <td>{{ isset($absence['dateAbsence']) ? \Carbon\Carbon::parse($absence['dateAbsence'])->format('d/m/Y') : (isset($absence['date']) ? \Carbon\Carbon::parse($absence['date'])->format('d/m/Y') : 'N/A') }}</td>
                    <td>
                        @if(isset($absence['formateur_id']) && is_array($absence['formateur'] ?? null))
                            {{ ($absence['formateur']['nom'] ?? '') }} {{ ($absence['formateur']['prenom'] ?? '') }}
                        @elseif(isset($absence['groupe_id']) && is_array($absence['groupe'] ?? null))
                            {{ ($absence['groupe']['nomGroupe'] ?? 'Groupe') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if(isset($absence['formateur_id']))
                            <span class="badge badge-blue">Formateur</span>
                        @else
                            <span class="badge badge-purple">Groupe</span>
                        @endif
                    </td>
                    <td>{{ $absence['motif'] ?? $absence['raison'] ?? 'Non spécifié' }}</td>
                    <td>1 jour</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Aucune absence enregistrée</div>
        @endif
    </div>
    @endif

    {{-- SECTION EMPLOI --}}
    @if($sections['emploi'] ?? false)
    <div class="section">
        <h2 class="section-title">📅 Charge de Travail - Formateurs</h2>
        
        @if(!empty($formateurs))
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:40%;">Formateur</th>
                    <th style="width:20%;">Nombre de Séances</th>
                    <th class="pdf-col-hours" style="width:20%;">Total Heures</th>
                    <th style="width:20%;">Charge</th>
                </tr>
            </thead>
            <tbody>
                @foreach($formateurs as $formateur)
                <tr>
                    <td><strong>{{ $formateur['nom'] ?? 'N/A' }} {{ $formateur['prenom'] ?? '' }}</strong></td>
                    <td>{{ intval($formateur['emplois_count'] ?? $formateur['seances'] ?? 0) }}</td>
                    @php
                        $heuresFormateur = $formateur['heures'] ?? $formateur['charge_travail'] ?? null;
                        if ($heuresFormateur === null || $heuresFormateur === '') {
                            $heuresFormateur = (float) ((intval($formateur['emplois_count'] ?? $formateur['seances'] ?? 0)) * 2);
                        } else {
                            $heuresFormateur = (float) $heuresFormateur;
                        }
                    @endphp
                    <td class="pdf-col-hours"><span class="pdf-hours-value">{{ rtrim(rtrim(number_format($heuresFormateur, 2, ',', ' '), '0'), ',') }}h</span></td>
                    <td>
                        @php
                            $seances = intval($formateur['emplois_count'] ?? $formateur['seances'] ?? 0);
                            if($seances > 20) $charge = 'Élevée';
                            elseif($seances > 10) $charge = 'Modérée';
                            else $charge = 'Faible';
                        @endphp
                        <span class="badge {{ $seances > 20 ? 'badge-red' : ($seances > 10 ? 'badge-blue' : 'badge-green') }}">{{ $charge }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="no-data">Aucune donnée de charge disponible</div>
        @endif
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <strong>Rapport Analytique Complet</strong> | EDTFlow - {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
