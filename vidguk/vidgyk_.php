<?php
header('Content-Type: application/json');

// Подключение к БД
$db = new PDO(
    'mysql:host=localhost;dbname=your_database',
    'username',
    'password',
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);

// Получение параметров
$category = isset($_GET['category']) ? $_GET['category'] : null;
$specialistId = isset($_GET['specialist_id']) ? $_GET['specialist_id'] : null;

// Формирование запроса
$query = "SELECT r.*, s.category 
          FROM reviews r 
          JOIN specialists s ON r.specialist_id = s.id 
          WHERE r.verified = 1";

$params = array();

if ($category) {
    $query .= " AND s.category = ?";
    $params[] = $category;
}

if ($specialistId) {
    $query .= " AND r.specialist_id = ?";
    $params[] = $specialistId;
}

$query .= " ORDER BY r.date DESC";

// Выполнение запроса
$stmt = $db->prepare($query);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($reviews);
?>