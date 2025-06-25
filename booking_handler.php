
<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config/database.php';

// Логування для відладки
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("=== BOOKING HANDLER STARTED ===");

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Отримані дані: " . print_r($input, true));
    
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

    // Створюємо таблиці якщо їх немає
    createTablesIfNotExist($pdo);

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
    error_log("Бронювання збережено з ID: $booking_id");

    // **ВАЖЛИВО**: В localhost режимі не відправляємо реальний email
    $email_sent = false;
    $email_method = 'none';

    // Перевіряємо чи це localhost
    $is_localhost = (
        $_SERVER['HTTP_HOST'] === 'localhost' || 
        $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
        strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0
    );

    if ($is_localhost) {
        // На localhost тільки логуємо email
        $email_sent = logEmailInsteadOfSending($input, $booking_id, $review_token);
        $email_method = 'logged';
        error_log("LOCALHOST: Email залогований замість відправки");
    } else {
        // На реальному сервері відправляємо email
        $email_sent = sendRealEmail($input, $booking_id, $review_token);
        $email_method = 'sent';
    }

    echo json_encode([
        'success' => true,
        'message' => 'Бронювання успішно створено!',
        'booking_id' => $booking_id,
        'email_sent' => $email_sent,
        'email_method' => $email_method,
        'is_localhost' => $is_localhost,
        'debug_info' => [
            'review_token' => $review_token,
            'review_url' => getReviewUrl($review_token)
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    error_log("ПОМИЛКА бронювання: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

// Функція створення таблиць
function createTablesIfNotExist($pdo) {
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
    error_log("Таблиці створені/перевірені");
}

// Логування email замість відправки (для localhost)
function logEmailInsteadOfSending($data, $booking_id, $review_token) {
    $review_url = getReviewUrl($review_token);
    
    $email_content = "
    ==========================================
    📧 EMAIL ДЛЯ ВІДПРАВКИ (LOCALHOST MODE)
    ==========================================
    
    Кому: {$data['email']}
    Тема: 🎉 Підтвердження бронювання #{$booking_id} - Підбір спеціалістів
    
    Вітаємо, {$data['name']}!
    
    📋 Деталі бронювання:
    - Номер: #{$booking_id}
    - Тип: {$data['type']}
    - Дата: {$data['date']}
    - Час: {$data['time']}
    - Телефон: {$data['phone']}
    
    🔗 Посилання для відгуку:
    {$review_url}
    
    ==========================================
    ";
    
    error_log($email_content);
    
    // Зберігаємо в файл для перегляду
    file_put_contents('email_log.txt', $email_content . "\n\n", FILE_APPEND);
    
    return true;
}

// Реальна відправка email (для продакшена)
function sendRealEmail($data, $booking_id, $review_token) {
    $to = $data['email'];
    $subject = "🎉 Підтвердження бронювання #{$booking_id} - Підбір спеціалістів";
    $review_url = getReviewUrl($review_token);
    
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
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Дякуємо за бронювання!</h1>
            </div>
            <div class='content'>
                <h2>Вітаємо, {$data['name']}!</h2>
                
                <div class='booking-details'>
                    <h3>📋 Деталі бронювання</h3>
                    <p><strong>Номер:</strong> #{$booking_id}</p>
                    <p><strong>Тип:</strong> {$data['type']}</p>
                    <p><strong>Дата:</strong> {$data['date']}</p>
                    <p><strong>Час:</strong> {$data['time']}</p>
                    <p><strong>Телефон:</strong> {$data['phone']}</p>
                </div>
                
                <p>Після консультації ви зможете залишити відгук:</p>
                <a href='{$review_url}' class='btn'>📝 Залишити відгук</a>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: Підбір спеціалістів <noreply@specialists.com>',
        'Reply-To: specialists.finder89@gmail.com'
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}

// Генерація URL для відгуку
function getReviewUrl($token) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/leave_review.php?token=' . $token;
}
?>