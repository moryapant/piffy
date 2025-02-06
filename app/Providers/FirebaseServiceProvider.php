<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    /**
     * Register Firebase services.
     */
    public function register(): void
    {
        $this->app->singleton(Auth::class, function ($app) {
            try {
                $projectId = config('firebase.project_id');
                $databaseUrl = config('firebase.database_url');
                $credentialsFile = config('firebase.credentials.file');

                if (!file_exists($credentialsFile)) {
                    throw new \RuntimeException('Firebase credentials file not found at: ' . $credentialsFile);
                }

                Log::info('Initializing Firebase', [
                    'project_id' => $projectId,
                    'database_url' => $databaseUrl,
                    'credentials_file' => $credentialsFile
                ]);

                $factory = (new Factory())
                    ->withServiceAccount($credentialsFile)
                    ->withProjectId($projectId)
                    ->withDatabaseUri($databaseUrl);

                return $factory->createAuth();
            } catch (\Exception $e) {
                Log::error('Firebase initialization failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Bootstrap Firebase services.
     */
    public function boot(): void
    {
    }
}
                    // Use individual credential environment variables
                    $credentials = [
                        'type' => Config::get('firebase.credentials.type'),
                        'project_id' => $projectId,
                        'private_key_id' => Config::get('firebase.credentials.private_key_id'),
                        'private_key' => str_replace('\\n', '\n', Config::get('firebase.credentials.private_key')),
                        'client_email' => Config::get('firebase.credentials.client_email'),
                        'client_id' => Config::get('firebase.credentials.client_id'),
                        'auth_uri' => Config::get('firebase.credentials.auth_uri'),
                        'token_uri' => Config::get('firebase.credentials.token_uri'),
                        'auth_provider_x509_cert_url' => Config::get('firebase.credentials.auth_provider_x509_cert_url'),
                        'client_x509_cert_url' => Config::get('firebase.credentials.client_x509_cert_url')
                    ];

                    // Validate required credentials
                    $requiredFields = ['private_key', 'client_email'];
                    foreach ($requiredFields as $field) {
                        if (empty($credentials[$field])) {
                            throw new \RuntimeException("Missing required Firebase credential: {$field}");
                        }
                    }

                    $factory = $factory->withServiceAccount($credentials);
                }

                return $factory->createAuth();
            } catch (\Exception $e) {
                Log::error('Firebase initialization failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    /**
     * Bootstrap Firebase services.
     */
    public function boot(): void
    {
    }
}
