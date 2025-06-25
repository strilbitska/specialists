
<?php
require_once 'config/database.php';

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
        FOREIGN KEY (specialist_id) REFERENCES specialists(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    echo "✅ Таблица отзывов создана успешно!<br>";
    
    // Добавляем тестовые отзывы для фотографа (ID = 1)
    $reviews_sql = "INSERT INTO reviews (specialist_id, client_name, rating, review_text, is_approved) VALUES
        (1, 'Марія Петренко', 5, 'Олександра - чудовий фотограф! Весільна фотосесія пройшла на найвищому рівні. Фото просто неймовірні!', 1),
        (1, 'Андрій Коваленко', 5, 'Професійний підхід, креативні ідеї та якісний результат. Рекомендую всім!', 1),
        (1, 'Оксана Іваненко', 4, 'Дуже задоволена портретною фотосесією. Олександра знає свою справу!', 1),
        (1, 'Дмитро Сидоренко', 5, 'Комерційна зйомка для нашої компанії пройшла відмінно. Всі фото високоякісні!', 1)";
    
    $pdo->exec($reviews_sql);
    echo "✅ Тестовые отзывы добавлены!<br>";
    
    // Обновляем рейтинг специалиста
    $update_rating_sql = "UPDATE specialists SET 
        rating = (SELECT AVG(rating) FROM reviews WHERE specialist_id = 1 AND is_approved = 1),
        reviews_count = (SELECT COUNT(*) FROM reviews WHERE specialist_id = 1 AND is_approved = 1)
        WHERE id = 1";
    
    $pdo->exec($update_rating_sql);
    echo "✅ Рейтинг специалиста обновлен!";
    
} catch (Exception $e) {
    echo "❌ Ошибка: " . $e->getMessage();
}
?>