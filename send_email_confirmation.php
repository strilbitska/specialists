<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';
require_once 'send_email_via_s_m_t_p.php'; // Подключаем SMTP

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Некоректні дані');
    }

    // Валидация полей
    $required_fields = ['specialist_id', 'date', 'time', 'type', 'name', 'email', 'phone'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Поле '$field' обов'язкове");
        }
    }

    // Создаем таблицы если их нет
    createTablesIfNotExist($pdo);

    // Проверяем доступность даты/времени
    $check_sql = "SELECT COUNT(*) as count FROM bookings
                  WHERE specialist_id = :specialist_id
                  AND booking_date = :date
                  AND booking_time = :time
                  AND status IN ('pending', 'confirmed')";

    $stmt = $pdo->prepare($check_sql);
    $stmt->execute([
        'specialist_id' => $input['specialist_id'],
        'date' => $input['date'],
        'time' => $input['time']
    ]);

    $result = $stmt->fetch();

    if ($result['count'] > 0) {
        throw new Exception('Цей час вже зайнятий. Оберіть інший час.');
    }

    // Генерируем токен для отзыва
    $review_token = bin2hex(random_bytes(32));

    // Сохраняем бронирование
    $insert_sql = "INSERT INTO bookings (
        specialist_id, client_name, client_email, client_phone,
        service_type, booking_date, booking_time, message, status,
        review_token, created_at
    ) VALUES (
        :specialist_id, :name, :email, :phone,
        :type, :date, :time, :message, 'pending',
        :review_token, NOW()
    )";

    $stmt = $pdo->prepare($insert_sql);
    $booking_result = $stmt->execute([
        'specialist_id' => $input['specialist_id'],
        'name' => $input['name'],
        'email' => $input['email'],
        'phone' => $input['phone'],
        'type' => $input['type'],
        'date' => $input['date'],
        'time' => $input['time'],
        'message' => $input['message'] ?? '',
        'review_token' => $review_token
    ]);

    if (!$booking_result) {
        throw new Exception('Помилка збереження бронювання');
    }

    $booking_id = $pdo->lastInsertId();

    // Отправляем email через SMTP
    $booking_data_for_email = [
        'type' => $input['type'],
        'date' => $input['date'],
        'time' => $input['time'],
        'phone' => $input['phone'],
        'review_token' => $review_token
    ];

    $email_sent = sendBookingConfirmationEmail(
        $input['email'],
        $input['name'],
        $booking_id,
        $booking_data_for_email
    );

    echo json_encode([
        'success' => true,
        'message' => 'Бронювання успішно створено!',
        'booking_id' => $booking_id,
        'email_sent' => $email_sent,
        'debug_info' => [
            'review_token' => $review_token,
            'review_url' => generateReviewUrl($review_token)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    error_log("ПОМИЛКА: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

function createTablesIfNotExist($pdo) {
    $bookings_sql = "
    CREATE TABLE IF NOT EXISTS bookings (
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
        review_token VARCHAR(64),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $pdo->exec($bookings_sql);
}
?>