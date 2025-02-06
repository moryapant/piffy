<?php

return [
    'project_id' => env('VITE_FIREBASE_PROJECT_ID', 'fappify-f4cb7'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
    
    // These values are used by the Firebase Admin SDK
    'credentials' => [
        'file' => env('FIREBASE_CREDENTIALS_FILE', base_path('firebase-credentials.json')),
        // Individual credentials (used if credentials file doesn't exist)
        'type' => 'service_account',
        'project_id' => env('VITE_FIREBASE_PROJECT_ID', 'fappify-f4cb7'),
        'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
        'private_key' => env('FIREBASE_PRIVATE_KEY'),
        'client_email' => env('FIREBASE_CLIENT_EMAIL'),
        'client_id' => env('FIREBASE_CLIENT_ID'),
        'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
        'token_uri' => 'https://oauth2.googleapis.com/token',
        'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
        'client_x509_cert_url' => env('FIREBASE_CLIENT_CERT_URL'),
    ],
    
    // These values are used by the Firebase JS SDK
    'web' => [
        'api_key' => env('VITE_FIREBASE_API_KEY'),
        'auth_domain' => env('VITE_FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('VITE_FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('VITE_FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('VITE_FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('VITE_FIREBASE_APP_ID'),
    ],
];
