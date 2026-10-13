<?php
class Database {
    private static $pdo;

    public static function init($dsn, $user, $pass) {
        if (self::$pdo) return;
        $options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        );
        self::$pdo = new PDO($dsn, $user, $pass, $options);
    }

    public static function pdo() {
        return self::$pdo;
    }

    public static function fetchAll($sql, $params = array()) {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetchOne($sql, $params = array()) {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public static function fetchValue($sql, $params = array()) {
        $row = self::fetchOne($sql, $params);
        if (!$row) return null;
        $values = array_values($row);
        return $values ? $values[0] : null;
    }

    public static function execute($sql, $params = array()) {
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function lastInsertId() {
        return self::$pdo->lastInsertId();
    }
}

