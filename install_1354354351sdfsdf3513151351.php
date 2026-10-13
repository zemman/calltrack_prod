<?php
// Simple installer: collects DB creds, creates schema, writes config/config.php, creates admin user
error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 1);
date_default_timezone_set('UTC');

$root = __DIR__;
$configDir = $root . '/config';
if (!is_dir($configDir)) { mkdir($configDir, 0777, true); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host']);
    $dbName = trim($_POST['db_name']);
    $dbUser = trim($_POST['db_user']);
    $dbPass = trim($_POST['db_pass']);
    $adminName = trim($_POST['admin_name']);
    $adminEmail = trim($_POST['admin_email']);
    $adminPass = trim($_POST['admin_pass']);

    try {
        $dsn = 'mysql:host=' . $dbHost . ';dbname=' . $dbName . ';charset=utf8';
        $pdo = new PDO($dsn, $dbUser, $dbPass, array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
        ));

        // Create tables
        $sql = file_get_contents($root . '/schema.sql');
        $pdo->exec($sql);

        // Create admin user (PHP5 compat)
        if (function_exists('password_hash')) {
            $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        } else {
            $salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 0, 22);
            $hash = crypt($adminPass, '$2y$10$' . $salt);
        }
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, "admin", NOW(), NOW())');
        $stmt->execute(array($adminName, $adminEmail, $hash));

        // Write config
        $config = "<?php\n".
                  "define('DB_DSN', '" . addslashes($dsn) . "');\n".
                  "define('DB_USER', '" . addslashes($dbUser) . "');\n".
                  "define('DB_PASS', '" . addslashes($dbPass) . "');\n";
        file_put_contents($configDir . '/config.php', $config);

        echo '<p>Установка завершена. <a href="public/index.php">Перейти в систему</a></p>';
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Установка CallTrack</title>
    <style>body{font-family:Arial;margin:24px;}label{display:block;margin-top:8px;}input{width:320px;padding:6px;}</style>
    </head>
<body>
    <h1>Установка CallTrack</h1>
    <?php if (isset($error)) { echo '<p style="color:red">'.htmlspecialchars($error, ENT_QUOTES, 'UTF-8').'</p>'; } ?>
    <form method="post">
        <fieldset>
            <legend>База данных MySQL</legend>
            <label>Хост<input name="db_host" value="localhost" required></label>
            <label>Имя БД<input name="db_name" required></label>
            <label>Пользователь<input name="db_user" required></label>
            <label>Пароль<input type="password" name="db_pass"></label>
        </fieldset>
        <fieldset>
            <legend>Администратор</legend>
            <label>Имя<input name="admin_name" required></label>
            <label>Email<input type="email" name="admin_email" required></label>
            <label>Пароль<input type="password" name="admin_pass" required></label>
        </fieldset>
        <button type="submit">Установить</button>
    </form>
</body>
</html>

