<?php

define('PDR_FILE_SYSTEM_APPLICATION_PATH', dirname(__DIR__) . '/');

require PDR_FILE_SYSTEM_APPLICATION_PATH . 'vendor/autoload.php';

mb_internal_encoding('UTF-8');

/*
 * database_wrapper::handle_exceptions() ruft im Fehlerfall gettext() auf.
 * Falls die Extension nicht geladen ist, soll ein bewusst provozierter
 * Migrationsfehler nicht an einem Call-to-undefined-function scheitern,
 * sondern an der erwarteten DatabaseMigrationException.
 */
if (!function_exists('gettext')) {
    function gettext(string $message): string {
        return $message;
    }
}

$testDbPortFile = __DIR__ . '/bin/.test-db-port';
if (!is_file($testDbPortFile)) {
    fwrite(STDERR, "Kein laufender Test-DB-Container gefunden.\n");
    fwrite(STDERR, "Bitte zuerst starten: bash tests/bin/test-db.sh up\n");
    fwrite(STDERR, "(oder direkt: bash tests/run-integration-tests.sh)\n");
    exit(1);
}

define('PDR_TEST_DB_HOST', '127.0.0.1');
define('PDR_TEST_DB_PORT', (int) trim(file_get_contents($testDbPortFile)));
define('PDR_TEST_DB_ROOT_USER', 'root');
define('PDR_TEST_DB_ROOT_PASSWORD', 'phpunit_root_pw');
define('PDR_TEST_DB_NAME', 'pdr_migration_test');

// --- config/config.php sichern, durch Testkonfiguration ersetzen ---

$configPath = PDR_FILE_SYSTEM_APPLICATION_PATH . 'config/config.php';
$configBackupPath = $configPath . '.phpunit-backup';
$configExistedBefore = is_file($configPath);

if ($configExistedBefore) {
    rename($configPath, $configBackupPath);
}

register_shutdown_function(function () use ($configPath, $configBackupPath, $configExistedBefore) {
    if ($configExistedBefore) {
        rename($configBackupPath, $configPath);
    } elseif (is_file($configPath)) {
        unlink($configPath);
    }
});

file_put_contents($configPath, '<?php' . PHP_EOL
    . '$config[\'database_management_system\'] = \'mysql\';' . PHP_EOL
    . '$config[\'database_host\'] = \'' . PDR_TEST_DB_HOST . '\';' . PHP_EOL
    . '$config[\'database_port\'] = ' . PDR_TEST_DB_PORT . ';' . PHP_EOL
    . '$config[\'database_name\'] = \'' . PDR_TEST_DB_NAME . '\';' . PHP_EOL
    . '$config[\'database_user\'] = \'' . PDR_TEST_DB_ROOT_USER . '\';' . PHP_EOL
    . '$config[\'database_password\'] = \'' . PDR_TEST_DB_ROOT_PASSWORD . '\';' . PHP_EOL
    . '$config[\'display_errors\'] = 1;' . PHP_EOL
    . '$config[\'log_errors\'] = 1;' . PHP_EOL
    . '$config[\'error_log\'] = \'' . addslashes(PDR_FILE_SYSTEM_APPLICATION_PATH) . 'tests/phpunit-error.log\';' . PHP_EOL
    . '$config[\'error_reporting\'] = E_ALL;' . PHP_EOL
    . '$config[\'LC_TIME\'] = \'de_DE\';' . PHP_EOL
    . '$config[\'timezone\'] = \'Europe/Berlin\';' . PHP_EOL
    . '$config[\'mb_internal_encoding\'] = \'UTF-8\';' . PHP_EOL);
