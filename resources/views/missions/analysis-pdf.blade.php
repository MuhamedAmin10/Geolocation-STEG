<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Analyse de travail technicien</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #0f172a;
        }

        h1 {
            font-size: 20px;
            margin: 0;
        }

        .muted {
            color: #64748b;
        }

        .meta {
            margin-top: 8px;
            margin-bottom: 14px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .grid th,
        .grid td {
            border: 1px solid #dbe3ef;
            padding: 8px;
            text-align: left;
        }

        .grid th {
            background: #f8fafc;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
            margin-bottom: 14px;
        }

        .kpi-cell {
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            padding: 10px;
            background: #ffffff;
        }

        .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 20px;
            font-weight: bold;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <h1>Analyse de travail technicien</h1>
    <div class="meta muted">
        <div>Genere le: {{ now()->format('Y-m-d H:i') }}</div>
        <div>
            Periode:
            @if ($rangeStart && $rangeEnd)
                {{ $rangeStart->format('Y-m-d') }} au {{ $rangeEnd->format('Y-m-d') }}
            @else
                Toutes les donnees
            @endif
        </div>
    </div>

    <table class="kpi-table">
        <tr>
            <td class="kpi-cell">
                <div class="kpi-label">Missions</div>
                <div class="kpi-value">{{ $total }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-label">Terminees</div>
                <div class="kpi-value">{{ $completed }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-label">En cours</div>
                <div class="kpi-value">{{ $inProgress }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-label">Bloquees</div>
                <div class="kpi-value">{{ $blocked }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-label">Annulees</div>
                <div class="kpi-value">{{ $cancelled }}</div>
            </td>
        </tr>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th>Taux validation</th>
                <th>Respect delais</th>
                <th>Taux bloque</th>
                <th>Haute priorite resolue</th>
                <th>Temps moyen (h)</th>
                <th>Terminees ce mois</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $validatedRate }}%</td>
                <td>{{ $onTimeRate }}%</td>
                <td>{{ $blockedRate }}%</td>
                <td>{{ $highPriorityResolutionRate }}%</td>
                <td>{{ $avgResolutionHours }}</td>
                <td>{{ $currentMonthCompleted }}</td>
            </tr>
        </tbody>
    </table>

    <table class="grid">
        <thead>
            <tr>
                <th>Mission</th>
                <th>Reference</th>
                <th>Statut</th>
                <th>Echeance</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recentMissions as $mission)
                <tr>
                    <td>#{{ $mission->id }}</td>
                    <td>{{ $mission->referencePoint?->reference ?? '—' }}</td>
                    <td>{{ $mission->statut }}</td>
                    <td>{{ $mission->due_at?->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Aucune mission trouvee pour cette periode.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Rapport genere automatiquement depuis la plateforme STEG Geolocalisation.
    </div>
</body>
</html>
