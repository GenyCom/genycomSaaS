<?php

namespace App\Console\Commands;

use App\Exceptions\ExceptionMailReporter;
use Illuminate\Console\Command;

/**
 * Commande de test pour vérifier que le mécanisme de monitoring
 * fonctionne correctement de bout en bout :
 *   Exception → Reporter → Queue → Mail
 *
 * Usage :
 *   php artisan monitoring:test
 *   php artisan monitoring:test --sync   (envoi synchrone pour debug)
 */
class TestExceptionMonitoring extends Command
{
    protected $signature = 'monitoring:test {--sync : Envoyer le mail de manière synchrone (sans passer par la queue)}';

    protected $description = 'Déclenche une exception de test pour vérifier le système de monitoring par email';

    public function handle(): int
    {
        $this->components->info('🔔 Test du système de monitoring GenyCom...');
        $this->newLine();

        // Afficher la config
        $this->components->twoColumnDetail('Destinataire', config('genycom.monitoring.recipient_email', 'non configuré'));
        $this->components->twoColumnDetail('Cooldown', config('genycom.monitoring.cooldown_minutes', 15) . ' minutes');
        $this->components->twoColumnDetail('Queue', config('genycom.monitoring.queue_name', 'monitoring'));
        $this->components->twoColumnDetail('Activé', config('genycom.monitoring.enabled', true) ? '✅ Oui' : '❌ Non');
        $this->components->twoColumnDetail('MAIL_MAILER', config('mail.default', 'non configuré'));
        $this->newLine();

        if ($this->option('sync')) {
            $this->components->warn('⚡ Mode SYNCHRONE — le mail sera envoyé immédiatement.');
            $this->newLine();

            try {
                $testException = new \RuntimeException(
                    'Ceci est un test du système de monitoring GenyCom. Si vous recevez cet email, le mécanisme fonctionne correctement.',
                    500
                );

                // Construire les données et envoyer directement
                $errorData = [
                    'exception_class' => get_class($testException),
                    'code'            => $testException->getCode(),
                    'severity'        => 'TEST',
                    'message'         => $testException->getMessage(),
                    'file'            => $testException->getFile(),
                    'line'            => $testException->getLine(),
                    'trace'           => $testException->getTraceAsString(),
                    'tenant_id'       => null,
                    'tenant_name'     => '🧪 TEST MONITORING',
                    'tenant_db'       => null,
                    'user_id'         => null,
                    'user_name'       => 'Console Artisan',
                    'user_email'      => null,
                    'url'             => 'artisan monitoring:test',
                    'method'          => 'CLI',
                    'ip'              => '127.0.0.1',
                    'user_agent'      => 'Artisan CLI / PHP ' . PHP_VERSION,
                    'input'           => '(commande de test)',
                    'environment'     => app()->environment(),
                    'php_version'     => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'timestamp'       => now()->format('d/m/Y H:i:s'),
                    'memory_usage'    => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
                ];

                $recipient = config('genycom.monitoring.recipient_email', 'genycomc@gmail.com');
                \Illuminate\Support\Facades\Mail::to($recipient)
                    ->send(new \App\Mail\ExceptionOccurredMail($errorData));

                $this->components->success("✅ Mail de test envoyé avec succès à {$recipient}");

            } catch (\Throwable $e) {
                $this->components->error("❌ Échec de l'envoi : {$e->getMessage()}");
                $this->newLine();
                $this->line($e->getTraceAsString());
                return self::FAILURE;
            }

        } else {
            $this->components->info('📨 Mode QUEUE — l\'exception sera dispatchée sur la queue "monitoring".');
            $this->newLine();

            try {
                $testException = new \RuntimeException(
                    '[TEST] Vérification du pipeline monitoring GenyCom — ' . now()->format('d/m/Y H:i:s'),
                    500
                );

                ExceptionMailReporter::report($testException);

                $this->components->success('✅ Exception de test dispatchée sur la queue.');
                $this->components->info('💡 Lancez le worker avec : php artisan queue:work --queue=monitoring');

            } catch (\Throwable $e) {
                $this->components->error("❌ Échec du dispatch : {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        $this->newLine();
        return self::SUCCESS;
    }
}
