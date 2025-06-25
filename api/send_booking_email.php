<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Настройки email
define('ADMIN_EMAIL', 'specialists.finder89@gmail.com'); // Замените на реальный email админа
define('SITE_NAME', 'Підбір спеціалістів');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Метод не підтримується');
    }

    // Получаем данные
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Некоректні дані');
    }

    $bookingData = [
        'name' => $input['name'] ?? '',
        'email' => $input['email'] ?? '',
        'phone' => $input['phone'] ?? '',
        'service' => $input['service'] ?? '',
        'date' => $input['date'] ?? '',
        'time' => $input['time'] ?? '',
        'message' => $input['message'] ?? '',
        'specialist_name' => $input['specialist_name'] ?? '',
        'specialist_id' => $input['specialist_id'] ?? 1
    ];

    // Валидация основных полей
    if (empty($bookingData['name']) || empty($bookingData['email']) || 
        empty($bookingData['phone']) || empty($bookingData['service']) ||
        empty($bookingData['date']) || empty($bookingData['time'])) {
        throw new Exception('Заповніть всі обов\'язкові поля');
    }

    // Сохраняем в базу данных
    $bookingId = saveBookingToDatabase($bookingData);
    
    // Отправляем email администратору
    $emailSent = sendAdminNotification($bookingData, $bookingId);
    
    // Отправляем подтверждение клиенту
    $clientEmailSent = sendClientConfirmation($bookingData, $bookingId);

    echo json_encode([
        'success' => true,
        'message' => 'Бронювання успішно оформлено!',
        'booking_id' => $bookingId,
        'admin_email_sent' => $emailSent,
        'client_email_sent' => $clientEmailSent
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Функция сохранения в базу данных
function saveBookingToDatabase($data) {
    try {
        require_once '../config/database.php';
        
        $sql = "INSERT INTO bookings (specialist_id, client_name, client_email, client_phone, 
                service_type, booking_date, booking_time, message, status, created_at) 
                VALUES (:specialist_id, :name, :email, :phone, :service, :date, :time, :message, 'pending', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'specialist_id' => $data['specialist_id'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'service' => $data['service'],
            'date' => $data['date'],
            'time' => $data['time'],
            'message' => $data['message']
        ]);
        
        return $pdo->lastInsertId();
        
    } catch (Exception $e) {
        error_log('Помилка збереження бронювання: ' . $e->getMessage());
        throw new Exception('Помилка збереження даних');
    }
}

// Функция отправки уведомления администратору
function sendAdminNotification($data, $bookingId) {
    $subject = "🆕 Нове бронювання #$bookingId - " . SITE_NAME;
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #007bff; color