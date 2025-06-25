<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Подключение к БД через правильный путь
require_once 'config/database.php';

try {
    $specialist_id = $_GET['specialist_id'] ?? 1;

    // Получаем одобренные отзывы из БД
    $sql = "SELECT id, client_name, rating, review_text, created_at as date
            FROM reviews
            WHERE specialist_id = :specialist_id AND is_approved = 1
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['specialist_id' => $specialist_id]);
    $reviews = $stmt->fetchAll();

    // Получаем статистику
    $stats_sql = "SELECT
        COUNT(*) as total_reviews,
        ROUND(AVG(rating), 1) as avg_rating
        FROM reviews
        WHERE specialist_id = :specialist_id AND is_approved = 1";

    $stmt = $pdo->prepare($stats_sql);
    $stmt->execute(['specialist_id' => $specialist_id]);
    $stats = $stmt->fetch();

    // Если нет отзывов, возвращаем пустые данные
    if (!$stats['total_reviews']) {
        $stats = [
            'total_reviews' => 0,
            'avg_rating' => 0
        ];
    }

    echo json_encode([
        'success' => true,
        'reviews' => $reviews,
        'stats' => $stats
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Помилка завантаження відгуків: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>