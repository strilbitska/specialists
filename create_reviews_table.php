<?php
require_once 'config/database.php';

echo "<h2>Создание таблицы отзывов</h2>";

try {
    // Создаем таблицу отзывов
    $sql = "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        specialist_id INT NOT NULL,
        client_name VARCHAR(255) NOT NULL,
        client_email VARCHAR(255),
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT NOT NULL,
        is_approved BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_specialist_id (specialist_id),
        INDEX idx_approved (is_approved)
    )";
    
    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Таблица reviews создана успешно!</p>";
    
    // Проверяем, есть ли уже отзывы
    $check_sql = "SELECT COUNT(*) as count FROM reviews WHERE specialist_id = 1";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result['count'] == 0) {
        // Добавляем тестовые отзывы для фотографа (ID = 1)
        $reviews_sql = "INSERT INTO reviews (specialist_id, client_name, rating, review_text, is_approved) VALUES
            (1, 'Марія Петренко', 5, 'Олександра - чудовий фотограф! Весільна фотосесія пройшла на найвищому рівні. Фото просто неймовірні!', 1),
            (1, 'Андрій Коваленко', 5, 'Професійний підхід, креативні ідеї та якісний результат. Рекомендую всім!', 1),
            (1, 'Оксана Іваненко', 4, 'Дуже задоволена портретною фотосесією. Олександра знає свою справу!', 1),
            (1, 'Дмитро Сидоренко', 5, 'Комерційна зйомка для нашої компанії пройшла відмінно. Всі фото високоякісні!', 1),
            (1, 'Софія Гриценко', 5, 'Індивідуальна фотосесія пройшла чудово! Олександра дуже креативна та професійна.', 1),
            (1, 'Максим Волков', 4, 'Якісні фото для бізнес-портфоліо. Швидко та професійно!', 1)";
        
        $pdo->exec($reviews_sql);
        echo "<p style='color: green;'>✅ Тестовые отзывы добавлены! (6 отзывов)</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Отзывы уже существуют в базе ({$result['count']} шт.)</p>";
    }
    
    // Обновляем рейтинг специалиста
    $update_rating_sql = "UPDATE specialists SET 
        rating = (SELECT ROUND(AVG(rating), 1) FROM reviews WHERE specialist_id = 1 AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE specialist_id = 1 AND is_approved = 1)
        WHERE id = 1";
    
    $pdo->exec($update_rating_sql);
    echo "<p style='color: green;'>✅ Рейтинг специалиста обновлен!</p>";
    
    // Показываем добавленные отзывы
    $show_sql = "SELECT client_name, rating, review_text, created_at FROM reviews WHERE specialist_id = 1 AND is_approved = 1 ORDER BY created_at DESC";
    $stmt = $pdo->prepare($show_sql);
    $stmt->execute();
    $reviews = $stmt->fetchAll();
    
    echo "<h3>Отзывы в базе данных:</h3>";
    foreach ($reviews as $review) {
        $stars = str_repeat('⭐', $review['rating']);
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>{$review['client_name']}</strong> {$stars}<br>";
        echo "<em>{$review['review_text']}</em><br>";
        echo "<small>Дата: {$review['created_at']}</small>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>