<?php
// Bootstrap: sessions, autoload, config, database

if (session_id() === '') { session_start(); }

// Autoloader (very simple)
spl_autoload_register(function($class) {
    $base = __DIR__;
    $map = array(
        'Database' => $base . '/lib/Database.php',
        'Auth' => $base . '/lib/Auth.php',
        'Utils' => $base . '/lib/Utils.php',
        'Audit' => $base . '/lib/Audit.php',
        'Settings' => $base . '/lib/Settings.php',
        'Mailer' => $base . '/lib/Mailer.php',
    );
    if (isset($map[$class]) && file_exists($map[$class])) {
        require_once $map[$class];
    }
});

$configFile = dirname(__DIR__) . '/config/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// Database init only if config exists
if (class_exists('Database') && defined('DB_DSN')) {
    Database::init(DB_DSN, DB_USER, DB_PASS);
}

