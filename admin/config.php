<?php
/**
 * Admin panel configuration.
 *
 * Local overrides (credentials, paths) belong in config.local.php, which is
 * gitignored.  Copy config.example.php to config.local.php and fill in your
 * values.  If config.local.php is present it is loaded in place of the
 * fallback defaults below.
 *
 * To generate a bcrypt hash for your chosen password, run:
 *   php -r "echo password_hash('your_new_password', PASSWORD_DEFAULT);"
 */

$localConfig = __DIR__ . '/config.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
} else {
    // -----------------------------------------------------------------------
    // Fallback defaults — override these values in config.local.php instead
    // of editing this file directly.
    // -----------------------------------------------------------------------

    /** Bcrypt hash of the admin password. */
    define('ADMIN_PASSWORD_HASH', '$2a$12$h7eNLfXsUwvQKM2oXD4p4evfjlJ47LcCbPbZuFg26JR5iiUy0e3wi');

    /** Absolute path to the SQLite3 database file. */
    define('DB_PATH', __DIR__ . '/../data/jugadores.db');

    /** PHP session name used by the admin panel. */
    define('SESSION_NAME', 'tablesports_admin');
}
