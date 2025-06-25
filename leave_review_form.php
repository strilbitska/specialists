<?php
// leave_review.php
header('Content-Type: text/html; charset=utf-8');

// Получаем токен из URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('❌ Токен не найден или недействителен');
}

// Здесь должна быть проверка токена в базе данных
// Пока что просто показываем форму

?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Залишити відгук</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">✍️ Залишити відгук</h3>
                    </div>
                    <div class="card-body">
                        <form id="reviewForm">
                            <div class="mb-3">
                                <label for="clientName" class="form-label">Ваше ім'я *</label>
                                <input type="text" class="form-control" id="clientName" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="rating" class="form-label">Оцінка *</label>
                                <select class="form-select" id="rating" required>
                                    <option value="">Оберіть оцінку</option>
                                    <option value="5">⭐⭐⭐⭐⭐ (5 зірок)</option>
                                    <option value="4">⭐⭐⭐⭐ (4 зірки)</option>
                                    <option value="3">⭐⭐⭐ (3 зірки)</option>
                                    <option value="2">⭐⭐ (2 зірки)</option>
                                    <option value="1">⭐ (1 зірка)</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reviewText" class="form-label">Ваш відгук *</label>
                                <textarea class="form-control" id="reviewText" rows="4" required
                                         placeholder="Поділіться своїм досвідом роботи зі спеціалістом..."></textarea>
                            </div>
                            
                            <input type="hidden" id="token" value="<?php echo htmlspecialchars($token); ?>">
                            
                            <button type="submit" class="btn btn-primary w-100">
                                📝 Надіслати відгук
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                token: document.getElementById('token').value,
                client_name: document.getElementById('clientName').value,
                rating: document.getElementById('rating').value,
                review_text: document.getElementById('reviewText').value
            };
            
            // Здесь отправка данных на сервер
            console.log('Данные отзыва:', formData);
            
            alert('✅ Дякуємо за відгук! Він буде опублікований після модерації.');
        });
    </script>
</body>
</html>