<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Mission Assignée</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333333;
        }
        .container {
            max-width: 620px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .header {
            background-color: #1a3c6e;
            padding: 28px 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            letter-spacing: 0.5px;
        }
        .header p {
            color: #a8c0e0;
            margin: 6px 0 0;
            font-size: 13px;
        }
        .body {
            padding: 32px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #1a3c6e;
            margin: 24px 0 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #e8edf5;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }
        .detail-table tr td {
            padding: 9px 12px;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-table tr td:first-child {
            color: #666666;
            width: 40%;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-haute   { background-color: #fee2e2; color: #b91c1c; }
        .badge-normale { background-color: #fef9c3; color: #854d0e; }
        .badge-basse   { background-color: #dcfce7; color: #15803d; }
        .badge-status  { background-color: #dbeafe; color: #1d4ed8; }
        .description-box {
            background-color: #f8fafc;
            border-left: 4px solid #1a3c6e;
            padding: 12px 16px;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 6px;
        }
        .cta {
            text-align: center;
            margin: 32px 0 8px;
        }
        .cta a {
            background-color: #1a3c6e;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            background-color: #f8fafc;
            text-align: center;
            padding: 20px 32px;
            font-size: 12px;
            color: #999999;
            border-top: 1px solid #e8edf5;
        }
        .footer strong {
            color: #1a3c6e;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <h1>Nouvelle Mission Assignée</h1>
            <p>Société Tunisienne de l'Électricité et du Gaz</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">
                Bonjour <strong>{{ $affectation->technicien->prenom }} {{ $affectation->technicien->nom }}</strong>,
            </p>
            <p style="font-size:14px; color:#555; line-height:1.6;">
                Une nouvelle mission vous a été assignée par
                <strong>{{ $affectation->assignedBy->name }}</strong>
                le <strong>{{ $affectation->assigned_at->format('d/m/Y à H:i') }}</strong>.
                Veuillez consulter les détails ci-dessous.
            </p>

            <!-- Mission Info -->
            <div class="section-title">Détails de la mission</div>
            <table class="detail-table">
                <tr>
                    <td>Type de mission</td>
                    <td>{{ $affectation->mission->type_mission }}</td>
                </tr>
                <tr>
                    <td>Priorité</td>
                    <td>
                        @php $p = strtolower($affectation->mission->priorite); @endphp
                        <span class="badge badge-{{ $p }}">
                            {{ ucfirst($affectation->mission->priorite) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>Statut</td>
                    <td>
                        <span class="badge badge-status">{{ $affectation->mission->statut }}</span>
                    </td>
                </tr>
                @if ($affectation->mission->due_at)
                <tr>
                    <td>Échéance</td>
                    <td>{{ $affectation->mission->due_at->format('d/m/Y') }}</td>
                </tr>
                @endif
            </table>

            @if ($affectation->mission->description)
            <div class="section-title">Description</div>
            <div class="description-box">{{ $affectation->mission->description }}</div>
            @endif

            <!-- Reference Point -->
            @if ($affectation->mission->referencePoint)
            <div class="section-title">Point de référence</div>
            <table class="detail-table">
                <tr>
                    <td>Référence</td>
                    <td><strong>{{ $affectation->mission->referencePoint->reference }}</strong></td>
                </tr>
                @if ($affectation->mission->referencePoint->adresse)
                <tr>
                    <td>Adresse</td>
                    <td>{{ $affectation->mission->referencePoint->adresse }}</td>
                </tr>
                @endif
                @if ($affectation->mission->referencePoint->gouvernorat)
                <tr>
                    <td>Gouvernorat</td>
                    <td>{{ $affectation->mission->referencePoint->gouvernorat }}</td>
                </tr>
                @endif
                @if ($affectation->mission->referencePoint->delegation)
                <tr>
                    <td>Délégation</td>
                    <td>{{ $affectation->mission->referencePoint->delegation }}</td>
                </tr>
                @endif
                <tr>
                    <td>Coordonnées GPS</td>
                    <td>
                        {{ $affectation->mission->referencePoint->latitude }},
                        {{ $affectation->mission->referencePoint->longitude }}
                    </td>
                </tr>
            </table>
            @endif

            <!-- CTA button -->
            <div class="cta">
                <a href="{{ route('missions.show', $affectation->mission) }}">
                    Voir la mission
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <strong>STEG Géolocalisation</strong><br>
            Cet email a été envoyé automatiquement, merci de ne pas y répondre.
        </div>
    </div>
</body>
</html>
