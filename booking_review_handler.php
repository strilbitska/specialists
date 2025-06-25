<?php
require_once 'config/database.php';

$error = '';
$success = '';
$booking = null;
$token = $_GET['token'] ?? '';

// Перевіряємо токен і отримуємо дані бронювання
if ($token) {
    $sql = "SELECT b.*, s.name as specialist_name 
            FROM bookings b 
            LEFT JOIN specialists s ON b.specialist_id = s.id 
            WHERE b.review_token = :token";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['token' => $token]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        $error = 'Невірне посилання або відгук вже залишено';
    }
    
    // Перевіряємо чи вже залишений відгук
    if ($booking) {
        $review_check_sql = "SELECT id FROM reviews WHERE booking_id = :booking_id";
        $stmt = $pdo->prepare($review_check_sql);
        $stmt->execute(['booking_id' => $booking['id']]);
        if ($stmt->fetch()) {
            $error = 'Ви вже залишили відгук для цього бронювання';
        }
    }
}

// Обробка відправки відгуку
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking && !$error) {
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = trim($_POST['review_text'] ?? '');
    
    if ($rating < 1 || $rating > 5) {
        $error = 'Оберіть рейтинг від 1 до 5 зірок';
    } elseif (strlen($review_text) < 10) {
        $error = 'Відгук повинен містити мінімум 10 символів';
    } else {
        try {
            $insert_sql = "INSERT INTO reviews (booking_id, specialist_id, client_name, client_email, rating, review_text, created_at) 
                          VALUES (:booking_id, :specialist_id, :client_name, :client_email, :rating, :review_text, NOW())";
            
            $stmt = $pdo->prepare($insert_sql);
            $result = $stmt->execute([
                'booking_id' => $booking['id'],
                'specialist_id' => $booking['specialist_id'],
                'client_name' => $booking['client_name'],
                'client_email' => $booking['client_email'],
                'rating' => $rating,
                'review_text' => $review_text
            ]);
            
            if ($result) {
                $success = 'Дякуємо за ваш відгук! Він буде опублікований на сайті.';
                
                // Оновлюємо статус бронювання
                $update_sql = "UPDATE bookings SET status = 'completed' WHERE id = :id";
                $stmt = $pdo->prepare($update_sql);
                $stmt->execute(['id' => $booking['id']]);
                
                $booking = null; // Приховуємо форму
            } else {
                $error = 'Помилка збереження відгуку';
            }
        } catch (Exception $e) {
            $error = 'Помилка: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Залишити відгук - Підбір спеціалістів</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .star-rating { font-size: 2rem; color: #ddd; cursor: pointer; }
        .star-rating .star { transition: color 0.2s; }
        .star-rating .star:hover,
        .star-rating .star.active { color: #ffc107; }
        .review-card { max-width: 600px; margin: 50px auto; }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="review-card">
            <div class="card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h2><i class="fas fa-star"></i> Залишити відгук</h2>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check-circle"></i> Дякуємо!</h4>
                            <p><?= htmlspecialchars($success) ?></p>
                            <a href="photographer1.html" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Повернутися на сайт
                            </a>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger">
                            <h4><i class="fas fa-exclamation-triangle"></i> Помилка</h4>
                            <p><?= htmlspecialchars($error) ?></p>
                            <a href="photographer1.html" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Повернутися на сайт
                            </a>
                        </div>
                    <?php elseif ($booking): ?>
                        <div class="mb-4">
                            <h4>Оцініть консультацію</h4>
                            <div class="alert alert-info">
                                <strong>Бронювання #<?= $booking['id'] ?></strong><br>
                                Спеціаліст: <?= htmlspecialchars($booking['specialist_name'] ?? 'Олександра Савчук') ?><br>
                                Послуга: <?= htmlspecialchars($booking['service_type']) ?><br>
                                Дата: <?= date('d.m.Y', strtotime($booking['booking_date'])) ?>
                            </div>
                        </div>

                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label"><strong>Оцінка (обов'язково):</strong></label>
                                <div class="star-rating" id="starRating">
                                    <span class="star" data-rating="1">⭐</span>
                                    <span class="star" data-rating="2">⭐</span>
                                    <span class="star" data-rating="3">⭐</span>
                                    <span class="star" data-rating="4">⭐</span>
                                    <span class="star" data-rating="5">⭐</span>
                                </div>
                                <input type="hidden" name="rating" id="rating" value="0">
                                <small class="text-muted">Клікніть на зірки для оцінки</small>
                            </div>

                            <div class="mb-4">
                                <label for="review_text" class="form-label"><strong>Ваш відгук (обов'язково):</strong></label>
                                <textarea class="form-control" id="review_text" name="review_text" rows="5" 
                                         placeholder="Поділіться враженнями про консультацію. Що вам сподобалось? Чи рекомендуєте цього спеціаліста?" 
                                         required minlength="10"></textarea>
                                <small class="text-muted">Мінімум 10 символів</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-paper-plane"></i> Відправити відгук
                                </button>
                                <a href="photographer1.html" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Повернутися без відгуку
                                </a>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <h4><i class="fas fa-question-circle"></i> Невірне посилання</h4>
                            <p>Посилання для залишення відгуку невірне або застаріле.</p>
                            <a href="photographer1.html" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Повернутися на сайт
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Обробка рейтингу зірок
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    
                    // Оновлюємо візуальний стан зірок
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');
                    stars.forEach((s, index) => {
                        if (index < rating) {
                            s.style.color = '#ffc107';
                        } else {
                            s.style.color = '#ddd';
                        }
                    });
                });
            });
            
            // Відновлюємо колір при відведенні миші
            document.getElementById('starRating').addEventListener('mouseleave', function() {
                const currentRating = ratingInput.value;
                stars.forEach((s, index) => {
                    if (index < currentRating) {
                        s.style.color = '#ffc107';
                    } else {
                        s.style.color = '#ddd';
                    }
                });
            });
        });
    </script>
</body>
</html>