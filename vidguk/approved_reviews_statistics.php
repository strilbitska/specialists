<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once 'config/database.php';

try {
    $specialist_id = $_GET['specialist_id'] ?? 1;

    // Отримуємо всі схвалені відгуки (нові відгуки автоматично схвалені)
    $sql = "SELECT id, client_name, rating, review_text, created_at as date
            FROM reviews
            WHERE specialist_id = :specialist_id AND is_approved = 1
            ORDER BY created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['specialist_id' => $specialist_id]);
    $reviews = $stmt->fetchAll();

    // Отримуємо статистику
    $stats_sql = "SELECT
        COUNT(*) as total_reviews,
        ROUND(AVG(rating), 1) as avg_rating,
        COUNT(CASE WHEN rating = 5 THEN 1 END) as five_stars,
        COUNT(CASE WHEN rating = 4 THEN 1 END) as four_stars,
        COUNT(CASE WHEN rating = 3 THEN 1 END) as three_stars,
        COUNT(CASE WHEN rating = 2 THEN 1 END) as two_stars,
        COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
        FROM reviews
        WHERE specialist_id = :specialist_id AND is_approved = 1";

    $stmt = $pdo->prepare($stats_sql);
    $stmt->execute(['specialist_id' => $specialist_id]);
    $stats = $stmt->fetch();

    // Якщо немає відгуків
    if (!$stats['total_reviews']) {
        $stats = [
            'total_reviews' => 0,
            'avg_rating' => 0,
            'five_stars' => 0,
            'four_stars' => 0,
            'three_stars' => 0,
            'two_stars' => 0,
            'one_star' => 0
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