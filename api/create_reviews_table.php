<?php
require_once '../config/database.php';
 $drop_sql = "DROP TABLE IF EXISTS reviews";
    $pdo->exec($drop_sql);
    echo "<p style='color: blue;'>ℹ️ Старая таблица reviews удалена</p>";

echo "<h2>Создание таблицы отзывов</h2>";

try {
    // Создаем таблицу отзывов
    $sql = "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT,
        specialist_id INT NOT NULL,
        client_name VARCHAR(255) NOT NULL,
        client_email VARCHAR(255),
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT NOT NULL,
        is_approved BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_specialist_id (specialist_id),
        INDEX idx_booking_id (booking_id),
        INDEX idx_approved (is_approved),
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
        FOREIGN KEY (specialist_id) REFERENCES specialists(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "<p style='color: green;'>✅ Таблица reviews создана успешно!</p>";

    // Проверяем, есть ли уже отзывы
    $check_sql = "SELECT COUNT(*) as count FROM reviews WHERE specialist_id = 1";
    $stmt = $pdo->prepare($check_sql);
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result['count'] == 0) {
        // Добавляем тестовые отзывы для фотографа (ID = 1)
        $reviews_sql = "INSERT INTO reviews (specialist_id, booking_id, client_name, rating, review_text, is_approved) VALUES
            (1, NULL, 'Марія Петренко', 5, 'Олександра - чудовий фотограф! Весільна фотосесія пройшла на найвищому рівні. Фото просто неймовірні!', 1),
            (1, NULL, 'Андрій Коваленко', 5, 'Професійний підхід, креативні ідеї та якісний результат. Рекомендую всім!', 1),
            (1, NULL, 'Оксана Іваненко', 4, 'Дуже задоволена портретною фотосесією. Олександра знає свою справу!', 1),
            (1, NULL, 'Дмитро Сидоренко', 5, 'Комерційна зйомка для нашої компанії пройшла відмінно. Всі фото високоякісні!', 1),
            (1, NULL, 'Софія Гриценко', 5, 'Індивідуальна фотосесія пройшла чудово! Олександра дуже креативна та професійна.', 1),
            (1, NULL, 'Максим Волков', 4, 'Якісні фото для бізнес-портфоліо. Швидко та професійно!', 1)";

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

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>