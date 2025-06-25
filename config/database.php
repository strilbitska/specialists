
<?php
// Конфигурация базы данных
$host = 'localhost';
$port = '3307';  // Используем порт 3307!
$dbname = 'specialists_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Для отладки (уберите в продакшене)
    // echo "Подключение к БД успешно!";
} catch(PDOException $e) {
    error_log("Ошибка подключения к БД: " . $e->getMessage());
    die("Ошибка подключения к базе данных. Проверьте настройки.");
}
?>