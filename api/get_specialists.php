<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Подключение с портом 3307
try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=specialists_db;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка подключения: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $category = $_GET['category'] ?? '';
    
    $sql = "SELECT * FROM specialists WHERE is_available = 1";
    $params = [];
    
    if (!empty($category)) {
        $sql .= " AND category = :category";
        $params['category'] = $category;
    }
    
    $sql .= " ORDER BY rating DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $specialists = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $specialists
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>