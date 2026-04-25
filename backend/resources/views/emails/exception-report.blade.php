<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #F1F5F9; padding: 24px; color: #1E293B; }

        .container { max-width: 680px; margin: 0 auto; background: #FFFFFF; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }

        /* ─── Header ─── */
        .header { background: linear-gradient(135deg, #DC2626, #991B1B); padding: 28px 32px; color: #fff; }
        .header-badge { display: inline-block; background: rgba(255,255,255,0.2); padding: 4px 14px; border-radius: 100px; font-size: 11px; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 10px; }
        .header h1 { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
        .header p { font-size: 13px; opacity: 0.85; }

        /* ─── Metadata Grid ─── */
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1px; background: #E2E8F0; border-bottom: 1px solid #E2E8F0; }
        .meta-item { background: #F8FAFC; padding: 16px 20px; }
        .meta-item:nth-child(odd) { border-right: 1px solid #E2E8F0; }
        .meta-label { font-size: 10px; font-weight: 800; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px; }
        .meta-value { font-size: 14px; font-weight: 700; color: #1E293B; word-break: break-all; }
        .meta-value.accent { color: #DC2626; }
        .meta-value.mono { font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; font-size: 12px; }

        /* ─── Section ─── */
        .section { padding: 24px 32px; border-bottom: 1px solid #F1F5F9; }
        .section-title { font-size: 11px; font-weight: 800; color: #64748B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .section-title::before { content: ''; display: inline-block; width: 4px; height: 14px; background: #DC2626; border-radius: 2px; }

        /* ─── Message Box ─── */
        .message-box { background: #FEF2F2; border: 1.5px solid #FECACA; border-radius: 10px; padding: 16px 20px; font-size: 14px; font-weight: 600; color: #991B1B; line-height: 1.6; word-break: break-word; }

        /* ─── Stack Trace ─── */
        .trace-box { background: #0F172A; color: #E2E8F0; border-radius: 10px; padding: 20px; font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace; font-size: 11px; line-height: 1.7; white-space: pre-wrap; word-break: break-all; max-height: 500px; overflow-y: auto; }
        .trace-box .line-highlight { color: #F87171; font-weight: 700; }

        /* ─── Request Info ─── */
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 8px 0; font-size: 13px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
        .info-table td:first-child { font-weight: 700; color: #64748B; width: 140px; font-size: 11px; text-transform: uppercase; }
        .info-table td:last-child { color: #1E293B; font-family: 'JetBrains Mono', 'Consolas', monospace; font-size: 12px; word-break: break-all; }

        /* ─── Footer ─── */
        .footer { padding: 20px 32px; background: #F8FAFC; text-align: center; }
        .footer p { font-size: 11px; color: #94A3B8; }
        .footer .brand { font-weight: 800; color: #64748B; }
        .footer .timestamp { font-family: 'JetBrains Mono', 'Consolas', monospace; font-size: 10px; color: #CBD5E1; margin-top: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-badge">⚠ Exception Report</div>
            <h1>{{ class_basename($error['exception_class'] ?? 'Exception') }}</h1>
            <p>Erreur détectée sur le tenant <strong>{{ $error['tenant_name'] ?? 'Inconnu' }}</strong></p>
        </div>

        <!-- Metadata -->
        <div class="meta-grid">
            <div class="meta-item">
                <div class="meta-label">Tenant / Client</div>
                <div class="meta-value">{{ $error['tenant_name'] ?? 'N/A' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Tenant ID</div>
                <div class="meta-value mono">{{ $error['tenant_id'] ?? '—' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Environnement</div>
                <div class="meta-value">{{ $error['environment'] ?? 'production' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Sévérité</div>
                <div class="meta-value accent">{{ $error['severity'] ?? 'ERROR' }}</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Utilisateur</div>
                <div class="meta-value">{{ $error['user_name'] ?? 'Non authentifié' }} (#{{ $error['user_id'] ?? '—' }})</div>
            </div>
            <div class="meta-item">
                <div class="meta-label">Horodatage</div>
                <div class="meta-value mono">{{ $error['timestamp'] ?? now()->toDateTimeString() }}</div>
            </div>
        </div>

        <!-- Error Message -->
        <div class="section">
            <div class="section-title">Message d'Erreur</div>
            <div class="message-box">{{ $error['message'] ?? 'Aucun message disponible.' }}</div>
        </div>

        <!-- Location -->
        <div class="section">
            <div class="section-title">Localisation</div>
            <table class="info-table">
                <tr>
                    <td>Exception</td>
                    <td>{{ $error['exception_class'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Fichier</td>
                    <td>{{ $error['file'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Ligne</td>
                    <td>{{ $error['line'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Code Erreur</td>
                    <td>{{ $error['code'] ?? '0' }}</td>
                </tr>
            </table>
        </div>

        <!-- Request Context -->
        @if(!empty($error['url']) || !empty($error['method']))
        <div class="section">
            <div class="section-title">Contexte de la Requête</div>
            <table class="info-table">
                <tr>
                    <td>URL</td>
                    <td>{{ $error['method'] ?? 'GET' }} {{ $error['url'] ?? 'N/A' }}</td>
                </tr>
                @if(!empty($error['ip']))
                <tr>
                    <td>Adresse IP</td>
                    <td>{{ $error['ip'] }}</td>
                </tr>
                @endif
                @if(!empty($error['user_agent']))
                <tr>
                    <td>User Agent</td>
                    <td style="font-size: 10px;">{{ Str::limit($error['user_agent'], 120) }}</td>
                </tr>
                @endif
                @if(!empty($error['input']))
                <tr>
                    <td>Payload</td>
                    <td style="font-size: 10px;">{{ $error['input'] }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endif

        <!-- Stack Trace -->
        @if(!empty($error['trace']))
        <div class="section">
            <div class="section-title">Stack Trace (Complet)</div>
            <div class="trace-box">{{ $error['trace'] }}</div>
        </div>
        @endif

        <!-- Previous Exception -->
        @if(!empty($error['previous']))
        <div class="section">
            <div class="section-title">Exception Précédente (Cause Racine)</div>
            <div class="message-box" style="background: #FFF7ED; border-color: #FED7AA; color: #9A3412;">
                <strong>{{ $error['previous']['class'] ?? '' }}</strong><br>
                {{ $error['previous']['message'] ?? '' }}<br>
                <small style="color: #C2410C;">{{ $error['previous']['file'] ?? '' }}:{{ $error['previous']['line'] ?? '' }}</small>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p><span class="brand">GenyCom SaaS</span> — Système de Monitoring Automatique</p>
            <p class="timestamp">Rapport généré le {{ $error['timestamp'] ?? now()->format('d/m/Y à H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
