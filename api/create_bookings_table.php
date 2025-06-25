<?php
require_once 'config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        specialist_id INT NOT NULL,
        client_name VARCHAR(255) NOT NULL,
        client_email VARCHAR(255) NOT NULL,
        client_phone VARCHAR(50) NOT NULL,
        service_type VARCHAR(255) NOT NULL,
        booking_date DATE NOT NULL,
        booking_time TIME NOT NULL,
        message TEXT,
        status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    echo "✅ Таблиця бронювань успішно створена!";
    
} catch (Exception $e) {
    echo "❌ Помилка: " . $e->getMessage();
}
?>