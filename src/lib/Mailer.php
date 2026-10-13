<?php
class Mailer {
    public static function sendIncidentNotification($incidentId, $eventType, $actorName) {
        $incident = Database::fetchOne('SELECT i.*, ic.name AS category_name
            FROM incidents i
            LEFT JOIN incident_categories ic ON ic.id = i.category_id
            WHERE i.id = ?', array($incidentId));
        if (!$incident) return false;

        $settings = Settings::all();
        $recipients = self::parseRecipients(isset($settings['incident_recipients']) ? $settings['incident_recipients'] : '');
        if (!$recipients) return false;

        $host = trim(isset($settings['smtp_host']) ? $settings['smtp_host'] : '');
        $port = (int)(isset($settings['smtp_port']) ? $settings['smtp_port'] : 0);
        $username = trim(isset($settings['smtp_username']) ? $settings['smtp_username'] : '');
        $password = isset($settings['smtp_password']) ? $settings['smtp_password'] : '';
        $encryption = strtolower(trim(isset($settings['smtp_encryption']) ? $settings['smtp_encryption'] : 'tls'));
        $fromName = trim(isset($settings['smtp_from_name']) ? $settings['smtp_from_name'] : 'CallTrack System');
        $fromEmail = $username;

        if ($host === '' || !$port || $fromEmail === '') {
            self::log('incident_' . $eventType, $incidentId, $recipients, 'error', 'SMTP settings are incomplete.');
            return false;
        }

        $subject = '[CallTrack] Инцидент #' . (int)$incident['id'] . ': ' . $incident['title'];
        $body = self::buildIncidentBody($incident, $eventType, $actorName);

        try {
            if (self::sendViaPHPMailer($settings, $recipients, $subject, $body)) {
                self::log('incident_' . $eventType, $incidentId, $recipients, 'success', null);
                return true;
            }

            self::sendViaSmtp($host, $port, $username, $password, $encryption, $fromEmail, $fromName, $recipients, $subject, $body);
            self::log('incident_' . $eventType, $incidentId, $recipients, 'success', null);
            return true;
        } catch (Exception $e) {
            self::log('incident_' . $eventType, $incidentId, $recipients, 'error', $e->getMessage());
            return false;
        }
    }

    private static function buildIncidentBody($incident, $eventType, $actorName) {
        $statusText = $incident['status'] === 'closed' ? 'Закрыт' : 'Открыт';
        $eventText = $eventType === 'closed' ? 'Инцидент закрыт' : 'Создан новый инцидент';
        $link = self::incidentUrl($incident['id']);
        $lines = array(
            $eventText,
            '',
            'ID: #' . (int)$incident['id'],
            'Заголовок: ' . $incident['title'],
            'Статус: ' . $statusText,
            'Кто создал/изменил: ' . ($actorName ? $actorName : 'Неизвестно'),
            'Категория: ' . (isset($incident['category_name']) && $incident['category_name'] ? $incident['category_name'] : '-'),
            'Дата начала: ' . $incident['started_at'],
            'Дата завершения: ' . ($incident['finished_at'] ? $incident['finished_at'] : '-'),
            '',
            'Описание:',
            $incident['description'],
            '',
            'Ссылка: ' . $link
        );
        return implode("\n", $lines);
    }

    private static function incidentUrl($id) {
        if (empty($_SERVER['HTTP_HOST'])) return 'index.php?page=incident_edit&id=' . (int)$id;
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $basePath = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
        $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
        return $scheme . '://' . $_SERVER['HTTP_HOST'] . $basePath . '/index.php?page=incident_edit&id=' . (int)$id;
    }

    private static function parseRecipients($value) {
        $parts = preg_split('/[\s,;]+/', (string)$value);
        $recipients = array();
        foreach ($parts as $email) {
            $email = trim($email);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = $email;
            }
        }
        return array_values(array_unique($recipients));
    }

    private static function sendViaPHPMailer($settings, $recipients, $subject, $body) {
        $autoload = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
        if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return false;
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = trim($settings['smtp_host']);
        $mail->Port = (int)$settings['smtp_port'];
        $mail->SMTPAuth = trim(isset($settings['smtp_username']) ? $settings['smtp_username'] : '') !== '';
        $mail->Username = trim(isset($settings['smtp_username']) ? $settings['smtp_username'] : '');
        $mail->Password = isset($settings['smtp_password']) ? $settings['smtp_password'] : '';
        $enc = strtolower(trim(isset($settings['smtp_encryption']) ? $settings['smtp_encryption'] : 'tls'));
        if ($enc === 'ssl') $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        if ($enc === 'tls') $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = 'UTF-8';
        $mail->SMTPOptions = array('ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ));
        $from = trim(isset($settings['smtp_username']) ? $settings['smtp_username'] : '');
        $mail->setFrom($from, trim(isset($settings['smtp_from_name']) ? $settings['smtp_from_name'] : 'CallTrack System'));
        foreach ($recipients as $recipient) $mail->addAddress($recipient);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    }

    private static function sendViaSmtp($host, $port, $username, $password, $encryption, $fromEmail, $fromName, $recipients, $subject, $body) {
        $context = stream_context_create(array('ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )));
        $transport = $encryption === 'ssl' ? 'ssl://' : 'tcp://';
        $socket = stream_socket_client($transport . $host . ':' . $port, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) throw new Exception('SMTP connection failed: ' . $errstr);
        stream_set_timeout($socket, 20);

        self::expect($socket, 220);
        self::command($socket, 'EHLO calltrack.local', 250);
        if ($encryption === 'tls') {
            self::command($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new Exception('STARTTLS failed.');
            }
            self::command($socket, 'EHLO calltrack.local', 250);
        }
        if ($username !== '') {
            self::command($socket, 'AUTH LOGIN', 334);
            self::command($socket, base64_encode($username), 334);
            self::command($socket, base64_encode($password), 235);
        }
        self::command($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
        foreach ($recipients as $recipient) {
            self::command($socket, 'RCPT TO:<' . $recipient . '>', array(250, 251));
        }
        self::command($socket, 'DATA', 354);
        $message = self::message($fromEmail, $fromName, $recipients, $subject, $body);
        fwrite($socket, str_replace("\n.", "\n..", $message) . "\r\n.\r\n");
        self::expect($socket, 250);
        self::command($socket, 'QUIT', 221);
        fclose($socket);
    }

    private static function message($fromEmail, $fromName, $recipients, $subject, $body) {
        $headers = array(
            'Date: ' . date('r'),
            'From: ' . self::mime($fromName) . ' <' . $fromEmail . '>',
            'To: ' . implode(', ', $recipients),
            'Subject: ' . self::mime($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit'
        );
        return implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $body);
    }

    private static function mime($text) {
        return '=?UTF-8?B?' . base64_encode($text) . '?=';
    }

    private static function command($socket, $command, $expected) {
        fwrite($socket, $command . "\r\n");
        return self::expect($socket, $expected);
    }

    private static function expect($socket, $expected) {
        $expected = is_array($expected) ? $expected : array($expected);
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (strlen($line) >= 4 && substr($line, 3, 1) === ' ') break;
        }
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expected)) {
            throw new Exception('SMTP error: ' . trim($response));
        }
        return $response;
    }

    private static function log($type, $relatedId, $recipients, $status, $errorMessage) {
        Database::execute('CREATE TABLE IF NOT EXISTS email_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(64) NOT NULL,
            related_id INT DEFAULT NULL,
            recipients TEXT NOT NULL,
            status ENUM("success","error") NOT NULL,
            error_message TEXT DEFAULT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_type_related (type, related_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8');
        Database::execute('INSERT INTO email_logs (type, related_id, recipients, status, error_message, created_at) VALUES (?, ?, ?, ?, ?, ?)',
            array($type, $relatedId, implode(', ', $recipients), $status, $errorMessage, date('Y-m-d H:i:s')));
    }
}
