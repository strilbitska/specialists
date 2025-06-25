<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Только POST запросы'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST; // Fallback для обычных форм
    }
    
    $required_fields = ['name', 'category', 'specialization', 'experience', 'rate_per_hour'];
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            echo json_encode(['success' => false, 'error' => "Поле $field обязательно"], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    
    $sql = "INSERT INTO specialists (name, category, specialization, experience, rate_per_hour, description, skills, contact_email, contact_phone) 
            VALUES (:name, :category, :specialization, :experience, :rate_per_hour, :description, :skills, :contact_email, :contact_phone)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'name' => $data['name'],
        'category' => $data['category'],
        'specialization' => $data['specialization'],
        'experience' => (int)$data['experience'],
        'rate_per_hour' => (float)$data['rate_per_hour'],
        'description' => $data['description'] ?? '',
        'skills' => $data['skills'] ?? '',
        'contact_email' => $data['contact_email'] ?? '',
        'contact_phone' => $data['contact_phone'] ?? ''
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Специалист успешно добавлен!',
        'id' => $pdo->lastInsertId()
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>