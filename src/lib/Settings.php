<?php
class Settings {
    private static $ensured = false;

    public static function ensureTable() {
        if (self::$ensured) return;
        Database::execute('CREATE TABLE IF NOT EXISTS settings (
            setting_key VARCHAR(128) NOT NULL PRIMARY KEY,
            setting_value TEXT,
            updated_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        self::$ensured = true;
    }

    public static function all() {
        self::ensureTable();
        $rows = Database::fetchAll('SELECT setting_key, setting_value FROM settings');
        $settings = array();
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }

    public static function get($key, $default = '') {
        self::ensureTable();
        $value = Database::fetchValue('SELECT setting_value FROM settings WHERE setting_key = ?', array($key));
        return $value === null ? $default : $value;
    }

    public static function set($key, $value) {
        self::ensureTable();
        $exists = Database::fetchValue('SELECT COUNT(*) FROM settings WHERE setting_key = ?', array($key));
        if ($exists) {
            Database::execute('UPDATE settings SET setting_value = ?, updated_at = ? WHERE setting_key = ?', array($value, date('Y-m-d H:i:s'), $key));
        } else {
            Database::execute('INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, ?)', array($key, $value, date('Y-m-d H:i:s')));
        }
    }

    public static function setMany($values) {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }
}
