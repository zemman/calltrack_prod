<?php
class Utils {
    public static function h($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function flash($key) {
        if (isset($_SESSION[$key])) {
            $msg = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $msg;
        }
        return null;
    }

    public static function toCsv($rows, $headers) {
        $f = fopen('php://temp', 'w+');
        // BOM for Excel UTF-8
        fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($f, $headers, ',');
        foreach ($rows as $row) {
            $line = array();
            foreach ($headers as $h) {
                $line[] = isset($row[$h]) ? $row[$h] : '';
            }
            fputcsv($f, $line, ',');
        }
        rewind($f);
        $csv = stream_get_contents($f);
        fclose($f);
        return $csv;
    }
}

