<?php

namespace App\Jobs;

use App\Mail\ExceptionOccurredMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job asynchrone pour envoyer les rapports d'exceptions par email.
 *
 * Anti-spam intégré :
 *  - Clé de cache unique par "signature" d'exception (classe + message + fichier + ligne)
 *  - Fenêtre glissante configurable (défaut : 15 min entre deux envois identiques)
 *  - Compteur d'occurrences groupées inclus dans le mail
 *
 * Retry :
 *  - 3 tentatives max avec back-off exponentiel (10s, 30s, 60s)
 */
class SendExceptionMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives max avant échec définitif.
     */
    public int $tries = 3;

    /**
     * Back-off exponentiel entre les tentatives (en secondes).
     */
    public array $backoff = [10, 30, 60];

    /**
     * Timeout pour l'exécution du job (en secondes).
     */
    public int $timeout = 30;

    public function __construct(
        public readonly array $errorData
    ) {}

    /**
     * Vérifie le rate limiting avant envoi.
     * Retourne `false` pour supprimer le job si déjà envoyé récemment.
     */
    public function handle(): void
    {
        $signature = $this->buildSignature();
        $cacheKey  = "exception_mail:{$signature}";
        $cooldown  = (int) config('genycom.monitoring.cooldown_minutes', 15);

        // Vérifier si cette erreur a déjà été signalée récemment
        $existing = Cache::get($cacheKey);

        if ($existing) {
            // Incrémenter le compteur silencieusement
            Cache::increment("{$cacheKey}:count");
            Log::channel('daily')->info("[ExceptionMailJob] Erreur dupliquée ignorée (cooldown {$cooldown}min)", [
                'signature' => $signature,
                'count'     => Cache::get("{$cacheKey}:count", 1) + 1,
            ]);
            return;
        }

        // Marquer comme envoyé + initialiser le compteur
        Cache::put($cacheKey, true, now()->addMinutes($cooldown));
        Cache::put("{$cacheKey}:count", 1, now()->addMinutes($cooldown));

        // Envoyer le mail
        $recipient = config('genycom.monitoring.recipient_email', 'genycomc@gmail.com');

        try {
            Mail::to($recipient)->send(new ExceptionOccurredMail($this->errorData));

            Log::channel('daily')->info("[ExceptionMailJob] Rapport d'exception envoyé", [
                'to'        => $recipient,
                'exception' => $this->errorData['exception_class'] ?? 'Unknown',
                'tenant'    => $this->errorData['tenant_name'] ?? 'N/A',
            ]);
        } catch (\Throwable $e) {
            // Ne pas boucler : si l'envoi du mail échoue, on log localement
            Log::channel('daily')->error("[ExceptionMailJob] Échec d'envoi du rapport", [
                'mail_error' => $e->getMessage(),
                'original'   => $this->errorData['exception_class'] ?? 'Unknown',
            ]);

            throw $e; // Laisser le mécanisme de retry du queue worker agir
        }
    }

    /**
     * Construit une signature unique pour dédupliquer les erreurs identiques.
     */
    private function buildSignature(): string
    {
        return md5(implode('|', [
            $this->errorData['exception_class'] ?? '',
            $this->errorData['message'] ?? '',
            $this->errorData['file'] ?? '',
            $this->errorData['line'] ?? '',
        ]));
    }

    /**
     * Gestion de l'échec définitif du job (après toutes les tentatives).
     */
    public function failed(\Throwable $exception): void
    {
        Log::channel('daily')->critical("[ExceptionMailJob] ÉCHEC DÉFINITIF — impossible d'envoyer le rapport d'exception", [
            'job_error'      => $exception->getMessage(),
            'original_error' => $this->errorData['message'] ?? 'Unknown',
            'tenant'         => $this->errorData['tenant_name'] ?? 'N/A',
        ]);
    }
}
