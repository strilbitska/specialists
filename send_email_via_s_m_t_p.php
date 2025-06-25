<?php
// Для відправки через Gmail SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Завантажте PHPMailer: composer require phpmailer/phpmailer
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Главная функция отправки email
 */
function sendEmailViaSMTP($to, $subject, $htmlMessage) {
    $mail = new PHPMailer(true);

    try {
        // Налаштування SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'specialists.finder89@gmail.com';
        $mail->Password   = 'ursm nyzg ioqb mfop';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Відправник і отримувач
        $mail->setFrom('specialists.finder89@gmail.com', 'Підбір спеціалістів');
        $mail->addAddress($to);

        // Контент
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $htmlMessage;

        $mail->send();
        error_log("✅ Email успешно отправлен: $to");
        return true;

    } catch (Exception $e) {
        error_log("❌ Email ошибка: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Функция для отправки письма подтверждения бронирования
 */
function sendBookingConfirmationEmail($to, $name, $booking_id, $booking_data) {
    $subject = " Підтвердження бронювання #{$booking_id} - Підбір спеціалістів";
    $htmlMessage = generateBookingEmailHTML($name, $booking_id, $booking_data);

    return sendEmailViaSMTP($to, $subject, $htmlMessage);
}

/**
 * Генерация HTML шаблона письма
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
                <h1> Дякуємо за бронювання!</h1>
                <p>Ваша консультація успішно заброньована</p>
            </div>

            <div class='content'>
                <h2>Вітаємо, {$name}!</h2>
                <p>Ми отримали ваше бронювання та підтверджуємо деталі:</p>

                <div class='booking-details'>
                    <h3> Деталі бронювання</h3>
                    <p><strong>Номер:</strong> #{$booking_id}</p>
                    <p><strong>Спеціаліст:</strong> Олександра Савчук</p>
                    <p><strong>Тип:</strong> {$data['type']}</p>
                    <p><strong>Дата:</strong> {$date}</p>
                    <p><strong>Час:</strong> {$data['time']}</p>
                    <p><strong>Телефон:</strong> {$data['phone']}</p>
                </div>

                <div class='highlight'>
                    <h4> Що далі?</h4>
                    <p>• Наш спеціаліст зв'яжеться з вами найближчим часом</p>
                </div>

                <div style='text-align: center;'>
                    <h4>⭐ Залишити відгук після консультації</h4>
                    <a href='{$review_url}' class='btn'> Залишити відгук</a>
                </div>

                <div class='footer'>
                    <p>З повагою, Команда «Підбір спеціалістів»</p>
                    <p> specialists.finder89@gmail.com</p>
                </div>
            </div>
        </div>
    </body>
    </html>";
}

/**
 * Генерация URL для отзыва
 */
function generateReviewUrl($token) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    return $protocol . $host . '/booking_review_handler.php?token=' . $token;
}
?>