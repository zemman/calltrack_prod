<?php
class Auth {
    public static function login($email, $password) {
        $user = Database::fetchOne('SELECT * FROM users WHERE email = ?', array($email));
        if (!$user) return false;
        if (!self::verifyPassword($password, $user['password_hash'])) return false;
        $_SESSION['user'] = array('id' => (int)$user['id'], 'role' => $user['role'], 'name' => $user['name'], 'email' => $user['email']);
        return true;
    }

    public static function logout() {
        unset($_SESSION['user']);
    }

    public static function isAuthenticated() {
        return isset($_SESSION['user']);
    }

    public static function id() {
        return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    }

    public static function isAdmin() {
        return self::isAuthenticated() && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    }

    public static function canOperate() {
        return self::isAuthenticated();
    }

    public static function hashPassword($password) {
        // PHP 5 compatible bcrypt via crypt if password_compat not available
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_BCRYPT);
        }
        $salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 0, 22);
        $options = '$2y$10$' . $salt;
        return crypt($password, $options);
    }

    public static function verifyPassword($password, $hash) {
        if (function_exists('password_verify')) {
            return password_verify($password, $hash);
        }
        return crypt($password, $hash) === $hash;
    }
}

