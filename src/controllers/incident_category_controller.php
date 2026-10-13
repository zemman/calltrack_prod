<?php
class IncidentCategoryController {
    
    public static function list() {
        $categories = Database::fetchAll("SELECT * FROM incident_categories ORDER BY name");
        include 'views/incidents/categories.php';
    }
    
    public static function save() {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            Utils::flash('flash_error', 'Название категории обязательно');
            header('Location: index.php?page=incident_categories');
            exit;
        }
        
        $now = date('Y-m-d H:i:s');
        
        if ($id) {
            // Обновление
            $sql = "UPDATE incident_categories SET name = ?, updated_at = ? WHERE id = ?";
            Database::execute($sql, array($name, $now, $id));
            Utils::flash('flash_success', 'Категория обновлена');
        } else {
            // Создание
            $sql = "INSERT INTO incident_categories (name, created_at, updated_at) VALUES (?, ?, ?)";
            Database::execute($sql, array($name, $now, $now));
            Utils::flash('flash_success', 'Категория создана');
        }
        
        header('Location: index.php?page=incident_categories');
        exit;
    }
    
    public static function delete() {
        $id = (int)($_POST['id'] ?? 0);
        
        if (!$id) {
            Utils::flash('flash_error', 'Категория не найдена');
            header('Location: index.php?page=incident_categories');
            exit;
        }
        
        // Проверяем, используется ли категория
        $count = Database::fetchValue("SELECT COUNT(*) FROM incidents WHERE category_id = ?", array($id));
        if ($count > 0) {
            Utils::flash('flash_error', 'Нельзя удалить категорию, которая используется в инцидентах');
            header('Location: index.php?page=incident_categories');
            exit;
        }
        
        Database::execute("DELETE FROM incident_categories WHERE id = ?", array($id));
        Utils::flash('flash_success', 'Категория удалена');
        
        header('Location: index.php?page=incident_categories');
        exit;
    }
}

// Вызов соответствующего метода
$method = $_GET['action'] ?? 'list';
if (method_exists('IncidentCategoryController', $method)) {
    IncidentCategoryController::$method();
} else {
    IncidentCategoryController::list();
}
