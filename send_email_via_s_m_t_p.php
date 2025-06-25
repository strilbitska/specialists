<?php
// Для відправки через Gmail SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Завантажте PHPMailer: composer require phpmailer/phpmailer
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Адаптованая функция для нашего бронирования
 */
function sendEmailViaSMTP($to, $name, $booking_id, $booking_data) {
    // Проверяем localhost режим
    $isLocalhost = (
        $_SERVER['HTTP_HOST'] === 'localhost' ||
        $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
        strpos($_SERVER['HTTP_HOST'], 'localhost:') === 0
    );

    if ($isLocalhost) {
        // В localhost режиме только логируем
        return logEmailForLocalhost($to, $name, $booking_id, $booking_data);
    }

    // Реальная отправка
    return sendRealSMTPEmail($to, $name, $booking_id, $booking_data);
}

/**
 * Реальная SMTP отправка (базируется на вашем коде)
 */
function sendRealSMTPEmail($to, $name, $booking_id, $booking_data) {
    $mail = new PHPMailer(true);

    try {
        // Налаштування SMTP (из вашего файла)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'specialists.finder89@gmail.com';
        $mail->Password   = 'ваш_app_password';  // 🔥 ЗАМЕНИТЕ!
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Відправник і отримувач
        $mail->setFrom('specialists.finder89@gmail.com', 'Підбір спеціалістів');
        $mail->addAddress($to, $name);

        // Контент (улучшенный)
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "🎉 Підтвердження бронювання #{$booking_id} - Підбір спеціалістів";
        $mail->Body = generateBookingEmailHTML($name, $booking_id, $booking_data);

        $mail->send();
        error_log("✅ Email успішно відправлено: {$to}");
        return true;

    } catch (Exception $e) {
        error_log("❌ Email помилка: {$mail->ErrorInfo}");
        // Fallback - логируем email
        logEmailForLocalhost($to, $name, $booking_id, $booking_data);
        return false;
    }
}

/**
 * Генерация HTML для письма бронирования
 */
function generateBookingEmailHTML($name, $booking_id, $data) {
    $review_url = generateReviewUrl($data['review_token']);
    $date = date('d.m.Y', strtotime($data['date']));

    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007bff; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
            .booking-details { background: white; padding: 25px; border-radius: 10px; margin: 20px 0; border-left: 5px solid #007bff; }
            .btn { display: inline-block; padding: 15px 30px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; margin: 15px 0; font-weight: bold; }
            .highlight { background: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745; margin: 20px 0; }
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
                <h2>Вітаємо, {$name}!</h2>
                <p>Ми отримали ваше бронювання та підтверджуємо деталі:</p>

                <div class='booking-details'>
                    <h3>📋 Деталі бронювання</h3>
                    <p><strong>Номер:</strong> #{$booking_id}</p>
                    <p><strong>Спеціаліст:</strong> Олександра Савчук</p>
                    <p><strong>Тип:</strong> {$data['type']}</p>
                    <p><strong>Дата:</strong> {$date}</p>
                    <p><strong>Час:</strong> {$data['time']}</p>
                    <p><strong>Телефон:</strong> {$data['phone']}</p>
                </div>

                <div class='highlight'>
                    <h4>📞 Що далі?</h4>
                    <p>• Наш спеціаліст зв'яжеться з вами найближчим часом</p>
                    <p>• Ви отримаете нагадування за день до консультації</p>
                </div>

                <div style='text-align: center;'>
                    <h4>⭐ Залишити відгук після консультації</h4>
                    <a href='{$review_url}' class='btn'>📝 Залишити відгук</a>
                </div>

                <div class='footer'>
                    <p>З повагою, Команда «Підбір спеціалістів»</p>
                    <p>📧 specialists.finder89@gmail.com</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Логирование для localhost/fallback
 */
function logEmailForLocalhost($to, $name, $booking_id, $data) {
    $review_url = generateReviewUrl($data['review_token']);

    $email_content = "
    ==========================================
    📧 EMAIL ДЛЯ ВІДПРАВКИ (LOCALHOST MODE)
    ==========================================

    Кому: {$to}
    Тема: 🎉 Підтвердження бронювання #{$booking_id}

    Вітаємо, {$name}!

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
    file_put_contents('email_log.txt', $email_content . "\n\n", FILE_APPEND);

    return true;
}

/**
 * Генерация URL для отзыва
 */
function generateReviewUrl($token) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/leave_review.php?token=' . $token;
}
?>