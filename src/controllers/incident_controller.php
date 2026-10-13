<?php
class IncidentController {
    
    public static function list() {
        global $incident_categories;
        
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
                ORDER BY i.status ASC, i.started_at DESC 
                LIMIT 100";
        
        $rows = Database::fetchAll($sql, $params);
        
        include 'views/incidents/list.php';
    }
    
    public static function create() {
        global $incident_categories;
        include 'views/incidents/create.php';
    }
    
    public static function edit() {
        global $incident_categories;
        
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            Utils::flash('flash_error', 'Инцидент не найден');
            header('Location: index.php?page=incidents');
            exit;
        }
        
        $incident = Database::fetchOne("SELECT * FROM incidents WHERE id = ?", array($id));
        if (!$incident) {
            Utils::flash('flash_error', 'Инцидент не найден');
            header('Location: index.php?page=incidents');
            exit;
        }
        
        include 'views/incidents/edit.php';
    }
    
    public static function save() {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'open';
        $started_at = $_POST['started_at'] ?? '';
        $finished_at = $_POST['finished_at'] ?? '';
        $inc_number_so = trim($_POST['inc_number_so'] ?? '');
        $potential_impact = trim($_POST['potential_impact'] ?? '');
        $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $bot_notification = trim($_POST['bot_notification'] ?? '');
        $status = !empty($finished_at) ? 'closed' : 'open';
        
        // Валидация
        if (empty($title)) {
            Utils::flash('flash_error', 'Заголовок обязателен');
            header('Location: ' . ($id ? "index.php?page=incident_edit&id=$id" : 'index.php?page=incident_new'));
            exit;
        }
        
        if (empty($description)) {
            Utils::flash('flash_error', 'Описание обязательно');
            header('Location: ' . ($id ? "index.php?page=incident_edit&id=$id" : 'index.php?page=incident_new'));
            exit;
        }
        
        if (empty($started_at)) {
            Utils::flash('flash_error', 'Дата начала обязательна');
            header('Location: ' . ($id ? "index.php?page=incident_edit&id=$id" : 'index.php?page=incident_new'));
            exit;
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($id) {
            // Обновление
            $oldIncident = Database::fetchOne("SELECT * FROM incidents WHERE id = ?", array($id));
            $sql = "UPDATE incidents SET 
                    title = ?, description = ?, status = ?, started_at = ?, 
                    finished_at = ?, inc_number_so = ?, potential_impact = ?, 
                    category_id = ?, bot_notification = ?, updated_at = ?
                    WHERE id = ?";
            $params = array(
                $title, $description, $status, $started_at, 
                $finished_at ?: null, $inc_number_so ?: null, 
                $potential_impact ?: null, $category_id, 
                $bot_notification ?: null, $now, $id
            );
            
            Database::execute($sql, $params);
            if ($status === 'closed' && (!$oldIncident || $oldIncident['status'] !== 'closed' || empty($oldIncident['finished_at']))) {
                Mailer::sendIncidentNotification($id, 'closed', isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : '');
            }
            Utils::flash('flash_success', 'Инцидент обновлен');
        } else {
            // Создание
            $sql = "INSERT INTO incidents 
                    (title, description, status, created_at, started_at, finished_at, 
                     inc_number_so, potential_impact, category_id, bot_notification, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array(
                $title, $description, $status, $now, $started_at,
                $finished_at ?: null, $inc_number_so ?: null,
                $potential_impact ?: null, $category_id,
                $bot_notification ?: null, $now
            );
            
            Database::execute($sql, $params);
            $newId = Database::lastInsertId();
            Mailer::sendIncidentNotification($newId, $status === 'closed' ? 'closed' : 'created', isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : '');
            Utils::flash('flash_success', 'Инцидент создан');
        }
        
        header('Location: index.php?page=incidents');
        exit;
    }
}

// Вызов соответствующего метода
$method = $_GET['action'] ?? 'list';
if (method_exists('IncidentController', $method)) {
    IncidentController::$method();
} else {
    IncidentController::list();
}
