<?php
/**
 * Admin panel configuration.
 *
 * To change the admin password, generate a new hash with:
 *   php -r "echo password_hash('your_new_password', PASSWORD_DEFAULT);"
 * and replace the value of ADMIN_PASSWORD_HASH below.
 *
 * Default password: TableSports2025!
 */

define('ADMIN_PASSWORD_HASH', '$2y$10$4SzDB8Mgw19ePvQR6TtdeuUmfRoliilpGpOJH0btweqdlAmzTBlJ2');

/** Absolute path to the SQLite3 database file. */
define('DB_PATH', __DIR__ . '/../data/jugadores.db');

/** PHP session name used by the admin panel. */
define('SESSION_NAME', 'tablesports_admin');
