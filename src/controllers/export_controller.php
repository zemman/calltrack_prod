<?php
class ExportController {
    
    public static function csv() {
        $type = $_GET['type'] ?? 'tickets';
        
        if ($type === 'incidents') {
            self::exportIncidentsCsv();
        } else {
            self::exportTicketsCsv();
        }
    }
    
    private static function exportIncidentsCsv() {
        // Фильтры
        $where = array('1=1');
        $params = array();
        
        if (!empty($_GET['from'])) {
            $where[] = 'started_at >= ?';
            $params[] = $_GET['from'];
        }
        if (!empty($_GET['to'])) {
            $where[] = 'started_at <= ?';
            $params[] = $_GET['to'];
        }
        if (!empty($_GET['category_id'])) {
            $where[] = 'category_id = ?';
            $params[] = (int)$_GET['category_id'];
        }
        if (!empty($_GET['status'])) {
            $where[] = 'status = ?';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['q'])) {
            $where[] = '(title LIKE ? OR description LIKE ?)';
            $search = '%' . $_GET['q'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql = "SELECT i.*, ic.name as category_name 
                FROM incidents i 
                LEFT JOIN incident_categories ic ON i.category_id = ic.id 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY i.status ASC, i.started_at DESC";
        
        $rows = Database::fetchAll($sql, $params);
        
        $filename = 'incidents_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM для корректного отображения кириллицы в Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Заголовки
        fputcsv($output, array(
            'ID', 'Заголовок', 'Описание', 'Статус', 'Дата создания', 
            'Дата начала', 'Дата завершения', 'Номер INC в SO', 
            'Потенциальное влияние', 'Категория', 'Оповещение в бот', 'Обновлено'
        ), ';');
        
        // Данные
        foreach ($rows as $row) {
            fputcsv($output, array(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['status'] === 'open' ? 'Открыт' : 'Закрыт',
                $row['created_at'],
                $row['started_at'],
                $row['finished_at'] ?: '',
                $row['inc_number_so'] ?: '',
                $row['potential_impact'] ?: '',
                $row['category_name'] ?: '',
                $row['bot_notification'] ?: '',
                $row['updated_at']
            ), ';');
        }
        
        fclose($output);
        exit;
    }
    
    private static function exportTicketsCsv() {
        // Существующая логика экспорта тикетов
        // ... (код из существующего контроллера)
    }
}

ExportController::csv();
