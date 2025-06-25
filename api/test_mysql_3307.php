<?php
echo "<h2>Тест подключения MySQL на порту 3307</h2>";

// Тест подключения с портом 3307
try {
    $pdo_test = new PDO("mysql:host=localhost;port=3307;charset=utf8mb4", 'root', '');
    $pdo_test->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Підключення до MySQL (порт 3307) успішно!</p>";
    
    // Показываем все базы данных
    $stmt = $pdo_test->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p><strong>Доступні бази даних:</strong></p>";
    echo "<ul>";
    foreach ($databases as $db) {
        echo "<li>$db</li>";
    }
    echo "</ul>";
    
    // Создаем базу данных
    $pdo_test->exec("CREATE DATABASE IF NOT EXISTS specialists_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ База даних specialists_db створена або вже існує!</p>";
    
    // Подключаемся к созданной базе
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=specialists_db;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Підключення до бази specialists_db успішно!</p>";
    
    // Создаем таблицу specialists
    $create_table_sql = "
    CREATE TABLE IF NOT EXISTS specialists (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100) NOT NULL,
        specialization VARCHAR(255) NOT NULL,
        experience INT NOT NULL,
        rate_per_hour DECIMAL(10,2) NOT NULL,
        description TEXT,
        skills TEXT,
        contact_email VARCHAR(255),
        contact_phone VARCHAR(50),
        avatar_url VARCHAR(500),
        rating DECIMAL(3,2) DEFAULT 0.00,
        reviews_count INT DEFAULT 0,
        is_available BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($create_table_sql);
    echo "<p style='color: green;'>✅ Таблиця specialists створена!</p>";
    
    // Проверяем количество записей
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM specialists");
    $result = $stmt->fetch();
    echo "<p>Кількість спеціалістів у базі: " . $result['count'] . "</p>";
    
    // Если нет записей, добавляем тестовые данные
    if ($result['count'] == 0) {
        $insert_sql = "INSERT INTO specialists (name, category, specialization, experience, rate_per_hour, description, skills, contact_email, contact_phone, rating, reviews_count, is_available) VALUES
        ('Олександр Петренко', 'photographer', 'Фотограф', 5, 800.00, 'Професійний фотограф з 5-річним досвідом.', 'Adobe Photoshop, Adobe Lightroom', 'alex.photo@email.com', '+380501234567', 4.8, 15, 1),
        ('Марія Коваленко', 'designer', 'UI/UX дизайнер', 3, 1200.00, 'Креативний UI/UX дизайнер.', 'Figma, Adobe XD, Sketch', 'maria.design@email.com', '+380509876543', 4.9, 22, 1),
        ('Дмитро Іваненко', 'developer', 'Python розробник', 4, 1500.00, 'Full-stack розробник з експертизою в Python.', 'Python, Django, Flask, PostgreSQL', 'dmitro.dev@email.com', '+380505555555', 4.7, 18, 1)";
        
        $pdo->exec($insert_sql);
        echo "<p style='color: green;'>✅ Тестові дані додано!</p>";
    }
    
    // Показываем специалистов
    $stmt = $pdo->query("SELECT name, category, experience, rate_per_hour FROM specialists LIMIT 5");
    $specialists = $stmt->fetchAll();
    
    if ($specialists) {
        echo "<h3>Спеціалісти в базі:</h3>";
        echo "<ul>";
        foreach ($specialists as $specialist) {
            echo "<li><strong>{$specialist['name']}</strong> - {$specialist['category']} - {$specialist['experience']} років - {$specialist['rate_per_hour']} грн/год</li>";
        }
        echo "</ul>";
    }
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Помилка: " . $e->getMessage() . "</p>";
}
?>