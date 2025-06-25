<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';

// Email налаштування
define('SITE_NAME', 'Підбір спеціалістів');
define('SITE_URL', 'http://localhost:8000'); // Змініть на ваш домен
define('ADMIN_EMAIL', 'specialists.finder89@gmail.com');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        throw new Exception('Некоректні дані');
    }

    // Валідація обов'язкових полів
    $required_fields = ['specialist_id', 'date', 'time', 'type', 'name', 'email', 'phone'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("Поле '$field' обов'язкове");
        }
    }

    // Створюємо необхідні таблиці
    createTables($pdo);

    // Перевіряємо доступність дати/часу
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

    // Генеруємо унікальний токен для відгуку
    $review_token = bin2hex(random_bytes(32));

    // Зберігаємо бронювання
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

    // Отримуємо дані спеціаліста
    $specialist_sql = "SELECT name FROM specialists WHERE id = :id";
    $stmt = $pdo->prepare($specialist_sql);
    $stmt->execute(['id' => $input['specialist_id']]);
    $specialist = $stmt->fetch();
    $specialist_name = $specialist['name'] ?? 'Олександра Савчук';

    // Відправляємо email клієнту
    $emailSent = sendClientConfirmationEmail([
        'booking_id' => $booking_id,
        'name' => $input['name'],
        'email' => $input['email'],
        'phone' => $input['phone'],
        'type' => $input['type'],
        'date' => $input['date'],
        'time' => $input['time'],
        'message' => $input['message'] ?? '',
        'specialist_name' => $specialist_name,
        'review_token' => $review_token
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Бронювання успішно створено!',
        'booking_id' => $booking_id,
        'email_sent' => $emailSent
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    error_log("Помилка бронювання: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Функція створення таблиць
function createTables($pdo) {
    // Таблиця бронювань
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

    // Таблиця відгуків
    $reviews_sql = "
    CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        booking_id INT,
        specialist_id INT NOT NULL,
        client_name VARCHAR(255) NOT NULL,
        client_email VARCHAR(255) NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        review_text TEXT NOT NULL,
        is_approved BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
    )";

    $pdo->exec($bookings_sql);
    $pdo->exec($reviews_sql);
}

// Функція відправки email клієнту
function sendClientConfirmationEmail($data) {
    $to = $data['email'];
    $subject = "🎉 Підтвердження бронювання #" . $data['booking_id'] . " - " . SITE_NAME;

    $review_url = SITE_URL . "/leave_review.php?token=" . $data['review_token'];

    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007bff; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .booking-details { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #007bff; }
            .btn { display: inline-block; padding: 12px 25px; background: #28a745; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
            .btn-review { background: #ffc107; color: #212529; }
            .footer { text-align: center; margin-top: 30px; color: #6c757d; font-size: 14px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Дякуємо за бронювання!</h1>
                <p>Ваша консультація успішно заброньована</p>
            </div>

            <div class='content'>
                <h2>Вітаємо, {$data['name']}!</h2>
                <p>Ми отримали ваше бронювання та підтверджуємо деталі консультації:</p>

                <div class='booking-details'>
                    <h3>📋 Деталі бронювання</h3>
                    <p><strong>Номер бронювання:</strong> #{$data['booking_id']}</p>
                    <p><strong>Спеціаліст:</strong> {$data['specialist_name']}</p>
                    <p><strong>Тип послуги:</strong> {$data['type']}</p>
                    <p><strong>Дата:</strong> " . date('d.m.Y', strtotime($data['date'])) . "</p>
                    <p><strong>Час:</strong> {$data['time']}</p>
                    <p><strong>Ваш телефон:</strong> {$data['phone']}</p>
                    " . ($data['message'] ? "<p><strong>Додаткові побажання:</strong> {$data['message']}</p>" : "") . "
                </div>

                <div style='background: #d4edda; padding: 20px; border-radius: 10px; border-left: 4px solid #28a745;'>
                    <h4>📞 Що далі?</h4>
                    <p>• Наш спеціаліст зв'яжеться з вами найближчим часом для підтвердження деталей</p>
                    <p>• Ви отримаете SMS-нагадування за день до консультації</p>
                    <p>• Якщо потрібно перенести або скасувати зустріч - зв'яжіться з нами заздалегідь</p>
                </div>

                <div style='background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107; margin-top: 20px;'>
                    <h4>⭐ Залиште відгук після консультації</h4>
                    <p>Після проведення консультації ви зможете поділитися своїми враженнями та допомогти іншим клієнтам:</p>
                    <a href='{$review_url}' class='btn btn-review'>📝 Залишити відгук</a>
                    <p style='font-size: 12px; margin-top: 10px;'>* Посилання буде активне після завершення консультації</p>
                </div>

                <div class='footer'>
                    <p>З питаннями звертайтеся:</p>
                    <p>📧 Email: " . ADMIN_EMAIL . "</p>
                    <p>📱 Телефон: +380 50 123 45 67</p>
                    <hr>
                    <p>© 2024 " . SITE_NAME . ". Дякуємо за довіру!</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <noreply@specialists.com>',
        'Reply-To: ' . ADMIN_EMAIL,
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}
?>