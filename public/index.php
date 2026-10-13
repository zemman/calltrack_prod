<?php
// Mini-CRM (PHP5-compatible) — single entry point router
// NOTE: First run /install.php to generate config and database.

error_reporting(E_ALL ^ E_STRICT);
ini_set('display_errors', 0);
date_default_timezone_set('Europe/Moscow');

// Bootstrap
$root = dirname(__DIR__);
require_once $root . '/src/bootstrap.php';

// Ensure user is authenticated except for login
$page = isset($_GET['page']) ? $_GET['page'] : 'tickets';
if (!Auth::isAuthenticated() && $page !== 'login') {
    header('Location: index.php?page=login');
    exit;
}

// Handle POST actions first
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        if (Auth::login($email, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['flash_error'] = 'Неверный логин или пароль.';
            header('Location: index.php?page=login');
            exit;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'admin_settings_save' && Auth::isAdmin()) {
        $smtpPassword = isset($_POST['smtp_password']) ? $_POST['smtp_password'] : '';
        if ($smtpPassword === '') {
            $smtpPassword = Settings::get('smtp_password', '');
        }
        Settings::setMany(array(
            'smtp_host' => trim(isset($_POST['smtp_host']) ? $_POST['smtp_host'] : ''),
            'smtp_port' => trim(isset($_POST['smtp_port']) ? $_POST['smtp_port'] : ''),
            'smtp_username' => trim(isset($_POST['smtp_username']) ? $_POST['smtp_username'] : ''),
            'smtp_password' => $smtpPassword,
            'smtp_encryption' => trim(isset($_POST['smtp_encryption']) ? $_POST['smtp_encryption'] : 'tls'),
            'smtp_from_name' => trim(isset($_POST['smtp_from_name']) ? $_POST['smtp_from_name'] : 'CallTrack System'),
            'incident_recipients' => trim(isset($_POST['incident_recipients']) ? $_POST['incident_recipients'] : '')
        ));
        $_SESSION['flash_success'] = 'SMTP-настройки сохранены.';
        header('Location: index.php?page=admin_settings');
        exit;
    }

    // Handle incident actions
    if (isset($_POST['action']) && $_POST['action'] === 'incident_save' && Auth::canOperate()) {
        $incidentId = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
        $description = trim(isset($_POST['description']) ? $_POST['description'] : '');
        $startedAt = isset($_POST['started_at']) && $_POST['started_at'] !== '' ? str_replace('T', ' ', $_POST['started_at']) . (strpos($_POST['started_at'], ':') !== false && strlen($_POST['started_at']) === 16 ? ':00' : '') : date('Y-m-d H:i:s');
        $finishedAt = isset($_POST['finished_at']) && $_POST['finished_at'] !== '' ? str_replace('T', ' ', $_POST['finished_at']) . (strpos($_POST['finished_at'], ':') !== false && strlen($_POST['finished_at']) === 16 ? ':00' : '') : null;
        $incNumberSo = trim(isset($_POST['inc_number_so']) ? $_POST['inc_number_so'] : '');
        $potentialImpact = trim(isset($_POST['potential_impact']) ? $_POST['potential_impact'] : '');
        $categoryId = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
        $botNotification = trim(isset($_POST['bot_notification']) ? $_POST['bot_notification'] : '');
        $status = $finishedAt ? 'closed' : 'open';
        
        if (empty($title) || empty($description)) {
            $_SESSION['flash_error'] = 'Заголовок и описание обязательны.';
            header('Location: index.php?page=' . ($incidentId ? 'incident_edit&id=' . $incidentId : 'incident_new'));
            exit;
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($incidentId) {
            // Update existing
            $oldIncident = Database::fetchOne('SELECT * FROM incidents WHERE id = ?', [$incidentId]);
            Database::execute('UPDATE incidents SET title=?, description=?, status=?, started_at=?, finished_at=?, inc_number_so=?, potential_impact=?, category_id=?, bot_notification=?, updated_at=? WHERE id = ?',
                [$title, $description, $status, $startedAt, $finishedAt, $incNumberSo, $potentialImpact, $categoryId, $botNotification, $now, $incidentId]);
            if ($status === 'closed' && (!$oldIncident || $oldIncident['status'] !== 'closed' || empty($oldIncident['finished_at']))) {
                Mailer::sendIncidentNotification($incidentId, 'closed', isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : '');
            }
            $_SESSION['flash_success'] = 'Инцидент обновлен.';
        } else {
            // Create new
            Database::execute('INSERT INTO incidents (title, description, status, created_at, started_at, finished_at, inc_number_so, potential_impact, category_id, bot_notification, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$title, $description, $status, $now, $startedAt, $finishedAt, $incNumberSo, $potentialImpact, $categoryId, $botNotification, $now]);
            $incidentId = Database::lastInsertId();
            Mailer::sendIncidentNotification($incidentId, $status === 'closed' ? 'closed' : 'created', isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : '');
            $_SESSION['flash_success'] = 'Инцидент создан.';
        }
        header('Location: index.php?page=incidents');
        exit;
    }
    
    // Handle incident category actions
    if (isset($_POST['action']) && $_POST['action'] === 'incident_category_save' && Auth::isAdmin()) {
        $categoryId = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
        
        if (empty($name)) {
            $_SESSION['flash_error'] = 'Название категории обязательно.';
            header('Location: index.php?page=incident_categories');
            exit;
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($categoryId) {
            Database::execute('UPDATE incident_categories SET name=?, updated_at=? WHERE id=?', [$name, $now, $categoryId]);
            $_SESSION['flash_success'] = 'Категория обновлена.';
        } else {
            Database::execute('INSERT INTO incident_categories (name, created_at, updated_at) VALUES (?, ?, ?)', [$name, $now, $now]);
            $_SESSION['flash_success'] = 'Категория создана.';
        }
        header('Location: index.php?page=incident_categories');
        exit;
    }
    
    if (isset($_POST['action']) && $_POST['action'] === 'incident_category_delete' && Auth::isAdmin()) {
        $categoryId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($categoryId) {
            // Check if category is used
            $count = Database::fetchValue('SELECT COUNT(*) FROM incidents WHERE category_id = ?', [$categoryId]);
            if ($count > 0) {
                $_SESSION['flash_error'] = 'Нельзя удалить категорию, которая используется в инцидентах.';
            } else {
                Database::execute('DELETE FROM incident_categories WHERE id = ?', [$categoryId]);
                $_SESSION['flash_success'] = 'Категория удалена.';
            }
        }
        header('Location: index.php?page=incident_categories');
        exit;
    }

    if (isset($_POST['action']) && $_POST['action'] === 'ticket_save' && Auth::canOperate()) {
        $ticketId = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $now = date('Y-m-d H:i:s');
        $occurredAt = isset($_POST['occurred_at']) && $_POST['occurred_at'] !== '' ? str_replace('T', ' ', $_POST['occurred_at']) . (strpos($_POST['occurred_at'], ':') !== false && strlen($_POST['occurred_at']) === 16 ? ':00' : '') : $now;
        $topic1Id = isset($_POST['topic1_id']) ? (int)$_POST['topic1_id'] : null;
        $topic2Id = isset($_POST['topic2_id']) && $_POST['topic2_id'] !== '' ? (int)$_POST['topic2_id'] : null;
        $storeId = isset($_POST['store_id']) && $_POST['store_id'] !== '' ? (int)$_POST['store_id'] : null;
        $status = isset($_POST['status']) ? trim($_POST['status']) : 'open';
        $justified = isset($_POST['justified']) ? 1 : 0;
        $justifyComment = isset($_POST['justify_comment']) ? trim($_POST['justify_comment']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';

        // Validate topic dependency on server
        if ($topic2Id !== null) {
            $parentId = Database::fetchValue('SELECT topic1_id FROM topic2 WHERE id = ?', [$topic2Id]);
            if (!$parentId || (int)$parentId !== (int)$topic1Id) {
                $_SESSION['flash_error'] = 'Подтема2 не соответствует выбранной подтеме1.';
                header('Location: index.php?page=' . ($ticketId ? 'ticket_edit&id=' . $ticketId : 'ticket_new_call'));
                exit;
            }
        }

        if ($ticketId) {
            // Update existing
            $old = Database::fetchOne('SELECT * FROM tickets WHERE id = ?', [$ticketId]);
            Database::execute('UPDATE tickets SET topic1_id=?, topic2_id=?, store_id=?, status=?, justified=?, justify_comment=?, description=?, occurred_at=?, updated_at=? WHERE id = ?',
                [$topic1Id, $topic2Id, $storeId, $status, $justified, $justifyComment, $description, $occurredAt, $now, $ticketId]);
            Audit::logChanges('ticket', $ticketId, $old, Database::fetchOne('SELECT * FROM tickets WHERE id = ?', [$ticketId]));
            $_SESSION['flash_success'] = 'Обращение обновлено.';
            header('Location: index.php?page=tickets');
            exit;
        } else {
            // Create new call ticket
            $status = 'closed';
            Database::execute('INSERT INTO tickets (type, topic1_id, topic2_id, store_id, employee_id, occurred_at, justified, justify_comment, description, status, created_at, updated_at) VALUES ("call", ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [$topic1Id, $topic2Id, $storeId, Auth::id(), $occurredAt, $justified, $justifyComment, $description, $status, $now, $now]);
            $newId = Database::lastInsertId();
            Audit::log('ticket', $newId, 'created', null, null);
            $_SESSION['flash_success'] = 'Звонок сохранён.';
            header('Location: index.php?page=tickets');
            exit;
        }
    }
}

// CSV export
if ($page === 'export_csv') {
    if (!Auth::canOperate()) { http_response_code(403); exit('Forbidden'); }
    
    $type = isset($_GET['type']) ? $_GET['type'] : 'tickets';
    
    if ($type === 'incidents') {
        // Export incidents
        $filters = [
            'from' => isset($_GET['from']) ? $_GET['from'] : null,
            'to' => isset($_GET['to']) ? $_GET['to'] : null,
            'category_id' => isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            'q' => isset($_GET['q']) ? trim($_GET['q']) : null,
        ];
        $params = [];
        $where = [];
        if ($filters['from']) { $where[] = 'started_at >= ?'; $params[] = str_replace('T',' ',$filters['from']); }
        if ($filters['to']) { $where[] = 'started_at <= ?'; $params[] = str_replace('T',' ',$filters['to']); }
        if ($filters['category_id']) { $where[] = 'category_id = ?'; $params[] = $filters['category_id']; }
        if ($filters['status']) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if ($filters['q']) { $where[] = '(title LIKE ? OR description LIKE ?)'; $params[] = '%'.$filters['q'].'%'; $params[] = '%'.$filters['q'].'%'; }
        
        $sql = 'SELECT i.id AS "ID", i.title AS "Заголовок", i.description AS "Описание", 
                       IF(i.status="open", "Открыт", "Закрыт") AS "Статус", i.created_at AS "Дата создания",
                       i.started_at AS "Дата начала", i.finished_at AS "Дата завершения", 
                       i.inc_number_so AS "Номер INC в SO", i.potential_impact AS "Потенциальное влияние",
                       ic.name AS "Категория", i.bot_notification AS "Оповещение в бот", i.updated_at AS "Обновлено"
                FROM incidents i
                LEFT JOIN incident_categories ic ON ic.id = i.category_id'
                . (count($where) ? (' WHERE ' . implode(' AND ', $where)) : '') . ' ORDER BY i.status ASC, i.started_at DESC';
        $rows = Database::fetchAll($sql, $params);
        $csv = Utils::toCsv($rows, array('ID','Заголовок','Описание','Статус','Дата создания','Дата начала','Дата завершения','Номер INC в SO','Потенциальное влияние','Категория','Оповещение в бот','Обновлено'));
        $filename = 'incidents_' . date('Ymd_His') . '.csv';
    } else {
        // Export tickets
        $filters = [
            'id' => isset($_GET['id']) && $_GET['id'] !== '' ? (int)$_GET['id'] : null,
            'from' => isset($_GET['from']) ? $_GET['from'] : null,
            'to' => isset($_GET['to']) ? $_GET['to'] : null,
            'topic1_id' => isset($_GET['topic1_id']) && $_GET['topic1_id'] !== '' ? (int)$_GET['topic1_id'] : null,
            'topic2_id' => isset($_GET['topic2_id']) && $_GET['topic2_id'] !== '' ? (int)$_GET['topic2_id'] : null,
            'store_id' => isset($_GET['store_id']) && $_GET['store_id'] !== '' ? (int)$_GET['store_id'] : null,
            'employee_id' => isset($_GET['employee_id']) && $_GET['employee_id'] !== '' ? (int)$_GET['employee_id'] : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            'justified' => isset($_GET['justified']) && $_GET['justified'] !== '' ? (int)$_GET['justified'] : null,
            'q' => isset($_GET['q']) ? trim($_GET['q']) : null,
        ];
        $params = [];
        $where = [];
        if ($filters['id']) { $where[] = 'id = ?'; $params[] = $filters['id']; }
        if ($filters['from']) { $where[] = 'occurred_at >= ?'; $params[] = str_replace('T',' ',$filters['from']); }
        if ($filters['to']) { $where[] = 'occurred_at <= ?'; $params[] = str_replace('T',' ',$filters['to']); }
        if ($filters['topic1_id']) { $where[] = 'topic1_id = ?'; $params[] = $filters['topic1_id']; }
        if ($filters['topic2_id']) { $where[] = 'topic2_id = ?'; $params[] = $filters['topic2_id']; }
        if ($filters['store_id']) { $where[] = 'store_id = ?'; $params[] = $filters['store_id']; }
        if ($filters['employee_id']) { $where[] = 'employee_id = ?'; $params[] = $filters['employee_id']; }
        if ($filters['status']) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if ($filters['justified'] !== null) { $where[] = 'justified = ?'; $params[] = $filters['justified']; }
        if ($filters['q']) { $where[] = '(description LIKE ? OR justify_comment LIKE ?)'; $params[] = '%'.$filters['q'].'%'; $params[] = '%'.$filters['q'].'%'; }
        $sql = 'SELECT t.id AS "ID", t.type AS "Тип", t1.name AS "Тема1", t2.name AS "Тема2", s.name AS "Магазин", u.name AS "Сотрудник", t.occurred_at AS "Дата обращения", 
                       IF(t.justified=1, "Да", "Нет") AS "Обосновано", t.justify_comment AS "Комментарий", t.description AS "Описание", t.status AS "Статус", t.created_at AS "Создано", t.updated_at AS "Обновлено"
                FROM tickets t
                LEFT JOIN topic1 t1 ON t1.id = t.topic1_id
                LEFT JOIN topic2 t2 ON t2.id = t.topic2_id
                LEFT JOIN stores s ON s.id = t.store_id
                LEFT JOIN users u ON u.id = t.employee_id'
                . (count($where) ? (' WHERE ' . implode(' AND ', $where)) : '') . ' ORDER BY t.occurred_at DESC';
        $rows = Database::fetchAll($sql, $params);
        $csv = Utils::toCsv($rows, array('ID','Тип','Тема1','Тема2','Магазин','Сотрудник','Дата обращения','Обосновано','Комментарий','Описание','Статус','Создано','Обновлено'));
        $filename = 'tickets_' . date('Ymd_His') . '.csv';
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo $csv;
    exit;
}

// Views rendering
include $root . '/views/layout/header.php';

switch ($page) {
    case 'login':
        include $root . '/views/login.php';
        break;
    case 'logout':
        Auth::logout();
        header('Location: index.php?page=login');
        break;
    case 'ticket_new_call':
        requireAuthOperator();
        $topic1 = Database::fetchAll('SELECT id, name FROM topic1 ORDER BY name');
        $stores = Database::fetchAll('SELECT id, name FROM stores ORDER BY name');
        include $root . '/views/tickets/create_call.php';
        break;
    case 'ticket_edit':
        requireAuthOperator();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $ticket = Database::fetchOne('SELECT * FROM tickets WHERE id = ?', [$id]);
        if (!$ticket) { echo '<p>Не найдено</p>'; break; }
        $topic1 = Database::fetchAll('SELECT id, name FROM topic1 ORDER BY name');
        $stores = Database::fetchAll('SELECT id, name FROM stores ORDER BY name');
        include $root . '/views/tickets/edit.php';
        break;
    case 'tickets':
        requireAuthOperator();
        // Filters
        $filters = [
            'from' => isset($_GET['from']) ? $_GET['from'] : null,
            'to' => isset($_GET['to']) ? $_GET['to'] : null,
            'topic1_id' => isset($_GET['topic1_id']) && $_GET['topic1_id'] !== '' ? (int)$_GET['topic1_id'] : null,
            'topic2_id' => isset($_GET['topic2_id']) && $_GET['topic2_id'] !== '' ? (int)$_GET['topic2_id'] : null,
            'store_id' => isset($_GET['store_id']) && $_GET['store_id'] !== '' ? (int)$_GET['store_id'] : null,
            'employee_id' => isset($_GET['employee_id']) && $_GET['employee_id'] !== '' ? (int)$_GET['employee_id'] : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            'justified' => isset($_GET['justified']) && $_GET['justified'] !== '' ? (int)$_GET['justified'] : null,
            'q' => isset($_GET['q']) ? trim($_GET['q']) : null,
        ];
        $params = [];
        $where = [];
        if ($filters['from']) { $where[] = 'occurred_at >= ?'; $params[] = $filters['from']; }
        if ($filters['to']) { $where[] = 'occurred_at <= ?'; $params[] = $filters['to']; }
        if ($filters['topic1_id']) { $where[] = 'topic1_id = ?'; $params[] = $filters['topic1_id']; }
        if ($filters['topic2_id']) { $where[] = 'topic2_id = ?'; $params[] = $filters['topic2_id']; }
        if ($filters['store_id']) { $where[] = 'store_id = ?'; $params[] = $filters['store_id']; }
        if ($filters['employee_id']) { $where[] = 'employee_id = ?'; $params[] = $filters['employee_id']; }
        if ($filters['status']) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if ($filters['justified'] !== null) { $where[] = 'justified = ?'; $params[] = $filters['justified']; }
        if ($filters['q']) { $where[] = '(description LIKE ? OR justify_comment LIKE ?)'; $params[] = '%'.$filters['q'].'%'; $params[] = '%'.$filters['q'].'%'; }
        $sql = 'SELECT t.*, u.name AS employee_name, s.name AS store_name, t1.name AS topic1_name, t2.name AS topic2_name
                FROM tickets t
                LEFT JOIN users u ON u.id = t.employee_id
                LEFT JOIN stores s ON s.id = t.store_id
                LEFT JOIN topic1 t1 ON t1.id = t.topic1_id
                LEFT JOIN topic2 t2 ON t2.id = t.topic2_id'
                . (count($where) ? (' WHERE ' . implode(' AND ', $where)) : '') . ' ORDER BY t.occurred_at DESC LIMIT 50';
        $rows = Database::fetchAll($sql, $params);
        $topic1 = Database::fetchAll('SELECT id, name FROM topic1 ORDER BY name');
        $stores = Database::fetchAll('SELECT id, name FROM stores ORDER BY name');
        $users = Database::fetchAll('SELECT id, name FROM users ORDER BY name');
        include $root . '/views/tickets/list.php';
        break;
    case 'admin_stores':
        requireAuthAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
            if ($name !== '') {
                if ($id) {
                    Database::execute('UPDATE stores SET name=? WHERE id=?', [$name, $id]);
                } else {
                    Database::execute('INSERT INTO stores (name) VALUES (?)', [$name]);
                }
            }
        }
        if (isset($_GET['delete'])) { Database::execute('DELETE FROM stores WHERE id=?', [(int)$_GET['delete']]); }
        $rows = Database::fetchAll('SELECT * FROM stores ORDER BY name');
        include $root . '/views/admin/stores.php';
        break;
    case 'admin_topic1':
        requireAuthAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
            if ($name !== '') {
                if ($id) {
                    Database::execute('UPDATE topic1 SET name=? WHERE id=?', [$name, $id]);
                } else {
                    Database::execute('INSERT INTO topic1 (name) VALUES (?)', [$name]);
                }
            }
        }
        if (isset($_GET['delete'])) { Database::execute('DELETE FROM topic1 WHERE id=?', [(int)$_GET['delete']]); }
        $rows = Database::fetchAll('SELECT * FROM topic1 ORDER BY name');
        include $root . '/views/admin/topic1.php';
        break;
    case 'admin_topic2':
        requireAuthAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
            $topic1Id = isset($_POST['topic1_id']) ? (int)$_POST['topic1_id'] : 0;
            $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
            if ($name !== '' && $topic1Id > 0) {
                if ($id) {
                    Database::execute('UPDATE topic2 SET name=?, topic1_id=? WHERE id=?', [$name, $topic1Id, $id]);
                } else {
                    Database::execute('INSERT INTO topic2 (name, topic1_id) VALUES (?, ?)', [$name, $topic1Id]);
                }
            }
        }
        if (isset($_GET['delete'])) { Database::execute('DELETE FROM topic2 WHERE id=?', [(int)$_GET['delete']]); }
        $rows = Database::fetchAll('SELECT t2.*, t1.name AS topic1_name FROM topic2 t2 LEFT JOIN topic1 t1 ON t1.id = t2.topic1_id ORDER BY t1.name, t2.name');
        $topic1 = Database::fetchAll('SELECT id, name FROM topic1 ORDER BY name');
        include $root . '/views/admin/topic2.php';
        break;
    case 'admin_users':
        requireAuthAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim(isset($_POST['name']) ? $_POST['name'] : '');
            $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
            $role = isset($_POST['role']) ? $_POST['role'] : 'operator';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            if ($email !== '' && $password !== '' && $name !== '') {
                $hash = Auth::hashPassword($password);
                Database::execute('INSERT INTO users (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())', [$name, $email, $hash, $role]);
            }
        }
        if (isset($_GET['delete'])) { Database::execute('DELETE FROM users WHERE id=?', [(int)$_GET['delete']]); }
        $rows = Database::fetchAll('SELECT id, name, email, role FROM users ORDER BY name');
        include $root . '/views/admin/users.php';
        break;
    case 'admin_settings':
        requireAuthAdmin();
        $settings = Settings::all();
        include $root . '/views/admin/settings.php';
        break;
    case 'incidents':
        requireAuthOperator();
        // Filters
        $filters = [
            'from' => isset($_GET['from']) ? $_GET['from'] : null,
            'to' => isset($_GET['to']) ? $_GET['to'] : null,
            'category_id' => isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null,
            'status' => isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : null,
            'q' => isset($_GET['q']) ? trim($_GET['q']) : null,
        ];
        $params = [];
        $where = [];
        if ($filters['from']) { $where[] = 'started_at >= ?'; $params[] = $filters['from']; }
        if ($filters['to']) { $where[] = 'started_at <= ?'; $params[] = $filters['to']; }
        if ($filters['category_id']) { $where[] = 'category_id = ?'; $params[] = $filters['category_id']; }
        if ($filters['status']) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if ($filters['q']) { $where[] = '(title LIKE ? OR description LIKE ?)'; $params[] = '%'.$filters['q'].'%'; $params[] = '%'.$filters['q'].'%'; }
        $sql = 'SELECT i.*, ic.name AS category_name
                FROM incidents i
                LEFT JOIN incident_categories ic ON ic.id = i.category_id'
                . (count($where) ? (' WHERE ' . implode(' AND ', $where)) : '') . ' ORDER BY i.status ASC, i.started_at DESC LIMIT 100';
        $rows = Database::fetchAll($sql, $params);
        $incident_categories = Database::fetchAll('SELECT id, name FROM incident_categories ORDER BY name');
        include $root . '/views/incidents/list.php';
        break;
    case 'incident_new':
        requireAuthOperator();
        $incident_categories = Database::fetchAll('SELECT id, name FROM incident_categories ORDER BY name');
        include $root . '/views/incidents/create.php';
        break;
    case 'incident_edit':
        requireAuthOperator();
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $incident = Database::fetchOne('SELECT * FROM incidents WHERE id = ?', [$id]);
        if (!$incident) { echo '<p>Инцидент не найден</p>'; break; }
        $incident_categories = Database::fetchAll('SELECT id, name FROM incident_categories ORDER BY name');
        include $root . '/views/incidents/edit.php';
        break;
    case 'incident_categories':
        requireAuthAdmin();
        $categories = Database::fetchAll('SELECT * FROM incident_categories ORDER BY name');
        include $root . '/views/incidents/categories.php';
        break;
    case 'reports':
        requireAuthOperator();
        include $root . '/views/reports/index.php';
        break;
    default:
        echo '<p>Страница не найдена</p>';
}

include $root . '/views/layout/footer.php';

function requireAuthOperator() {
    if (!Auth::canOperate()) { http_response_code(403); exit('Forbidden'); }
}
function requireAuthAdmin() {
    if (!Auth::isAdmin()) { http_response_code(403); exit('Forbidden'); }
}
