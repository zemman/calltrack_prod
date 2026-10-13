<?php
require_once '../src/bootstrap.php';

// Проверяем, есть ли уже категории
$count = Database::fetchValue("SELECT COUNT(*) FROM incident_categories");
if ($count > 0) {
    echo "Категории инцидентов уже существуют.\n";
    exit;
}

// Создаем базовые категории
$categories = array(
    'Техническая проблема',
    'Проблема с оборудованием',
    'Проблема с сетью',
    'Проблема с ПО',
    'Проблема с данными',
    'Проблема с безопасностью',
    'Проблема с производительностью',
    'Проблема с доступностью',
    'Другое'
);

$now = date('Y-m-d H:i:s');

foreach ($categories as $category) {
    Database::execute(
        "INSERT INTO incident_categories (name, created_at, updated_at) VALUES (?, ?, ?)",
        array($category, $now, $now)
    );
    echo "Создана категория: $category\n";
}

echo "Инициализация категорий инцидентов завершена.\n";
