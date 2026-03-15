<?php
/**
 * Admin panel configuration — EXAMPLE FILE.
 *
 * Copy this file to config.local.php and fill in your values.
 * config.local.php is gitignored and will never be committed.
 *
 * To generate a bcrypt hash for your chosen password, run:
 *   php -r "echo password_hash('your_password_here', PASSWORD_DEFAULT);"
 */

/** Bcrypt hash of the admin password.
 * This is the hash of the placeholder password 'changeme'.
 * Replace with a hash of your real password before deploying:
 *   php -r "echo password_hash('your_password_here', PASSWORD_DEFAULT);"
 */
define('ADMIN_PASSWORD_HASH', '$2y$10$govEytsLbFeTI8O5yj2e7Oup4SI9EZmLSKZdLS0Ea4QUdSVVPwjgu');

/** Absolute path to the SQLite3 database file. */
define('DB_PATH', __DIR__ . '/../data/jugadores.db');

/** PHP session name used by the admin panel. */
define('SESSION_NAME', 'tablesports_admin');
