<?php
/**
 * Configuration générale de l'application
 */

// Configuration de l'application
define('APP_NAME', 'Système de Gestion Financière');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development | production

// Configuration de sécurité
define('SESSION_LIFETIME', 1800); // 30 minutes en secondes
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Configuration des tokens
define('TOKEN_EXPIRY', 3600); // 1 heure
define('PASSWORD_RESET_EXPIRY', 3600); // 1 heure

// Configuration CORS
define('ALLOWED_ORIGINS', [
    'http://localhost:3000',
    'http://localhost:8080',
    'http://127.0.0.1:3000',
    'http://127.0.0.1:8080'
]);

// Configuration des logs
define('LOG_PATH', __DIR__ . '/../logs/');
define('LOG_ERRORS', true);

// Configuration email (à configurer selon votre serveur SMTP)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM_EMAIL', 'noreply@gestion-financiere.com');
define('SMTP_FROM_NAME', 'Gestion Financière');

// Configuration des fichiers
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5 MB

// Timezone
date_default_timezone_set('Africa/Abidjan');

// Gestion des erreurs en développement
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration des sessions
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', APP_ENV === 'production' ? 1 : 0);
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

// Code secret pour l'enregistrement des administrateurs (à changer en production)
define('ADMIN_REGISTRATION_CODE', 'ADMIN2024SECRET');