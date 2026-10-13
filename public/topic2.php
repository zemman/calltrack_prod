<?php
// Ajax endpoint to load topic2 by topic1
require_once dirname(__DIR__) . '/src/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
if (!Auth::canOperate()) { http_response_code(403); echo '[]'; exit; }
$topic1Id = isset($_GET['topic1_id']) ? (int)$_GET['topic1_id'] : 0;
if ($topic1Id <= 0) { echo '[]'; exit; }
$rows = Database::fetchAll('SELECT id, name FROM topic2 WHERE topic1_id = ? ORDER BY name', array($topic1Id));
echo json_encode($rows);

