<?php
// Для відправки через Gmail SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Завантажте PHPMailer: composer require phpmailer/phpmailer

function sendEmailViaSMTP($to, $subject, $htmlMessage) {
    $mail = new PHPMailer(true);

    try {
        // Налаштування SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'specialists.finder89@gmail.com';  // Ваш Gmail
        $mail->Password   = 'ваш_app_password';                // App Password
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
        return true;
    } catch (Exception $e) {
        error_log("Email помилка: {$mail->ErrorInfo}");
        return false;
    }
}
?>