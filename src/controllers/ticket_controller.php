<?php
class TicketController {
    
    public static function list() {
        global $topic1, $topic2, $stores, $users;
        
        // Фильтры
        $where = array('1=1');
        $params = array();
        
        if (!empty($_GET['from'])) {
            $where[] = 'occurred_at >= ?';
            $params[] = $_GET['from'];
        }
        if (!empty($_GET['to'])) {
            $where[] = 'occurred_at <= ?';
            $params[] = $_GET['to'];
        }
        if (!empty($_GET['topic1_id'])) {
            $where[] = 'topic1_id = ?';
            $params[] = (int)$_GET['topic1_id'];
        }
        if (!empty($_GET['store_id'])) {
            $where[] = 'store_id = ?';
            $params[] = (int)$_GET['store_id'];
        }
        if (!empty($_GET['employee_id'])) {
            $where[] = 'employee_id = ?';
            $params[] = (int)$_GET['employee_id'];
        }
        if (!empty($_GET['status'])) {
            $where[] = 'status = ?';
            $params[] = $_GET['status'];
        }
        if (!empty($_GET['justified'])) {
            $where[] = 'justified = ?';
            $params[] = (int)$_GET['justified'];
        }
        if (!empty($_GET['q'])) {
            $where[] = 'description LIKE ?';
            $params[] = '%' . $_GET['q'] . '%';
        }
        
        $sql = "SELECT t.*, t1.name as topic1_name, t2.name as topic2_name, 
                       s.name as store_name, u.name as employee_name
                FROM tickets t 
                LEFT JOIN topic1 t1 ON t.topic1_id = t1.id 
                LEFT JOIN topic2 t2 ON t.topic2_id = t2.id 
                LEFT JOIN stores s ON t.store_id = s.id 
                LEFT JOIN users u ON t.employee_id = u.id 
                WHERE " . implode(' AND ', $where) . " 
                ORDER BY t.occurred_at DESC 
                LIMIT 100";
        
        $rows = Database::fetchAll($sql, $params);
        
        include 'views/tickets/list.php';
    }
    
    public static function create() {
        global $topic1, $stores;
        include 'views/tickets/create_call.php';
    }
    
    public static function edit() {
        global $topic1, $topic2, $stores, $users;
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            Utils::flash('flash_error', 'Обращение не найдено');
            header('Location: index.php?page=tickets');
            exit;
        }
        
        $ticket = Database::fetchOne("SELECT * FROM tickets WHERE id = ?", array($id));
        if (!$ticket) {
            Utils::flash('flash_error', 'Обращение не найдено');
            header('Location: index.php?page=tickets');
            exit;
        }
        
        include 'views/tickets/edit.php';
    }
    
    public static function save() {
        $id = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] ?? 'call';
        $topic1_id = !empty($_POST['topic1_id']) ? (int)$_POST['topic1_id'] : null;
        $topic2_id = !empty($_POST['topic2_id']) ? (int)$_POST['topic2_id'] : null;
        $store_id = !empty($_POST['store_id']) ? (int)$_POST['store_id'] : null;
        $employee_id = !empty($_POST['employee_id']) ? (int)$_POST['employee_id'] : null;
        $occurred_at = $_POST['occurred_at'] ?? '';
        $justified = isset($_POST['justified']) ? 1 : 0;
        $justify_comment = trim($_POST['justify_comment'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'open';
        
        // Валидация
        if (empty($occurred_at)) {
            Utils::flash('flash_error', 'Дата обращения обязательна');
            header('Location: ' . ($id ? "index.php?page=ticket_edit&id=$id" : 'index.php?page=ticket_new_call'));
            exit;
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($id) {
            // Обновление
            $sql = "UPDATE tickets SET 
                    type = ?, topic1_id = ?, topic2_id = ?, store_id = ?, employee_id = ?, 
                    occurred_at = ?, justified = ?, justify_comment = ?, description = ?, 
                    status = ?, updated_at = ?
                    WHERE id = ?";
            $params = array(
                $type, $topic1_id, $topic2_id, $store_id, $employee_id,
                $occurred_at, $justified, $justify_comment ?: null, $description,
                $status, $now, $id
            );
            
            Database::execute($sql, $params);
            Utils::flash('flash_success', 'Обращение обновлено');
        } else {
            // Создание
            $status = 'closed';
            $sql = "INSERT INTO tickets 
                    (type, topic1_id, topic2_id, store_id, employee_id, occurred_at, 
                     justified, justify_comment, description, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array(
                $type, $topic1_id, $topic2_id, $store_id, $employee_id,
                $occurred_at, $justified, $justify_comment ?: null, $description,
                $status, $now, $now
            );
            
            Database::execute($sql, $params);
            Utils::flash('flash_success', 'Обращение создано');
        }
        
        header('Location: index.php?page=tickets');
        exit;
    }
}

// Вызов соответствующего метода
$method = $_GET['action'] ?? 'list';
if (method_exists('TicketController', $method)) {
    TicketController::$method();
} else {
    TicketController::list();
}
