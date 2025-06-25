
// Модуль бронирования для всех специалистов
class BookingManager {
    constructor() {
        this.init();
    }

    init() {
        console.log('📋 Модуль бронирования инициализирован');
        this.createBookingSection();
        this.createBookingModal();
        this.setupEventListeners();
    }

    // Создание секции бронирования
    createBookingSection() {
        const bookingHTML = `
            <div class="booking-section bg-light p-4 rounded mt-5">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h3><i class="fas fa-calendar-alt text-primary"></i> Забронювати консультацію</h3>
                        <p class="mb-0 text-muted">Оберіть зручний час для консультації та обговорення деталей проекту</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-primary btn-lg w-100" onclick="bookingManager.showBookingModal()">
                            <i class="fas fa-calendar"></i> Забронювати
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Вставляем секцию перед футером
        const footer = document.querySelector('footer');
        if (footer) {
            footer.insertAdjacentHTML('beforebegin', bookingHTML);
        } else {
            // Если футера нет, добавляем в конец основного контента
            const content = document.querySelector('.content, .container, main') || document.body;
            content.insertAdjacentHTML('beforeend', bookingHTML);
        }
    }

    // Создание модального окна бронирования
    createBookingModal() {
        const modalHTML = `
            <!-- Модальне вікно бронювання -->
            <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="bookingModalLabel">
                                <i class="fas fa-calendar-alt"></i> Забронювати консультацію
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Закрити"></button>
                        </div>
                        <div class="modal-body">
                            <form id="bookingForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user text-muted"></i> Ім'я *
                                        </label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            <i class="fas fa-envelope text-muted"></i> Email *
                                        </label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">
                                            <i class="fas fa-phone text-muted"></i> Телефон *
                                        </label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="service" class="form-label">
                                            <i class="fas fa-briefcase text-muted"></i> Послуга *
                                        </label>
                                        <select class="form-select" id="service" name="service" required>
                                            <option value="">Виберіть послугу</option>
                                            ${this.getServiceOptions()}
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date" class="form-label">
                                            <i class="fas fa-calendar text-muted"></i> Дата *
                                        </label>
                                        <input type="date" class="form-control" id="date" name="date" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="time" class="form-label">
                                            <i class="fas fa-clock text-muted"></i> Час *
                                        </label>
                                        <select class="form-select" id="time" name="time" required>
                                            <option value="">Виберіть час</option>
                                            <option value="09:00">09:00</option>
                                            <option value="10:00">10:00</option>
                                            <option value="11:00">11:00</option>
                                            <option value="12:00">12:00</option>
                                            <option value="13:00">13:00</option>
                                            <option value="14:00">14:00</option>
                                            <option value="15:00">15:00</option>
                                            <option value="16:00">16:00</option>
                                            <option value="17:00">17:00</option>
                                            <option value="18:00">18:00</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label">
                                            <i class="fas fa-comment text-muted"></i> Повідомлення (необов'язково)
                                        </label>
                                        <textarea class="form-control" id="message" name="message" rows="3" 
                                                placeholder="Опишіть деталі проекту або особливі побажання..."></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Скасувати
                            </button>
                            <button type="button" class="btn btn-primary" onclick="bookingManager.confirmBooking()">
                                <i class="fas fa-check"></i> Підтвердити
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Добавляем модальное окно в конец body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    // Получение вариантов услуг в зависимости от специалиста
    getServiceOptions() {
        const url = window.location.pathname;

        if (url.includes('photographer')) {
            return `
                <option value="wedding">Весільна фотосесія</option>
                <option value="portrait">Портретна фотосесія</option>
                <option value="commercial">Комерційна зйомка</option>
                <option value="event">Подієва фотосесія</option>
            `;
        } else if (url.includes('designer')) {
            return `
                <option value="web-design">Веб-дизайн</option>
                <option value="mobile-app">Дизайн мобільного додатку</option>
                <option value="branding">Брендинг</option>
                <option value="ui-ux">UI/UX консультація</option>
            `;
        } else if (url.includes('developer')) {
            return `
                <option value="web-development">Веб-розробка</option>
                <option value="api-development">Розробка API</option>
                <option value="automation">Автоматизація</option>
                <option value="consultation">Технічна консультація</option>
            `;
        } else if (url.includes('economist')) {
            return `
                <option value="financial-analysis">Фінансовий аналіз</option>
                <option value="business-plan">Бізнес-план</option>
                <option value="investment">Інвестиційна консультація</option>
                <option value="audit">Аудит</option>
            `;
        } else if (url.includes('hr')) {
            return `
                <option value="recruitment">Підбір персоналу</option>
                <option value="hr-audit">HR-аудит</option>
                <option value="training">Навчання персоналу</option>
                <option value="consultation">HR-консультація</option>
            `;
        }

        // Универсальные опции для остальных специалистов
        return `
            <option value="consultation">Консультація</option>
            <option value="project">Виконання проекту</option>
            <option value="audit">Аудит</option>
            <option value="training">Навчання</option>
        `;
    }

    // Настройка обработчиков событий
    setupEventListeners() {
        document.addEventListener('DOMContentLoaded', () => {
            // Устанавливаем минимальную дату (сегодня)
            const dateInput = document.getElementById('date');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.min = today;
            }
        });
    }

    // Показать модальное окно
    showBookingModal() {
        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
        modal.show();
    }

    // Подтверждение бронирования
    confirmBooking() {
        const form = document.getElementById('bookingForm');
        const formData = new FormData(form);

        const bookingData = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            service: formData.get('service'),
            date: formData.get('date'),
            time: formData.get('time'),
            message: formData.get('message'),
            specialist_id: this.getSpecialistId(),
            specialist_name: this.getSpecialistName()
        };

        if (!this.validateBookingForm(bookingData)) {
            return;
        }

        this.processBooking(bookingData);
    }

    // Валидация формы
    validateBookingForm(data) {
        const errors = [];

        if (!data.name || data.name.trim().length < 2) {
            errors.push('Введіть коректне ім\'я (мінімум 2 символи)');
        }

        if (!data.email || !this.isValidEmail(data.email)) {
            errors.push('Введіть коректний email');
        }

        if (!data.phone || data.phone.length < 10) {
            errors.push('Введіть коректний номер телефону');
        }

        if (!data.service) {
            errors.push('Виберіть послугу');
        }

        if (!data.date) {
            errors.push('Виберіть дату');
        }

        if (!data.time) {
            errors.push('Виберіть час');
        }

        if (errors.length > 0) {
            this.showValidationErrors(errors);
            return false;
        }

        return true;
    }

    // Проверка email
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Показать ошибки валидации
    showValidationErrors(errors) {
        const errorHtml = errors.map(error => `<li>${error}</li>`).join('');
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h6><i class="fas fa-exclamation-triangle"></i> Помилки валідації:</h6>
                <ul class="mb-0">${errorHtml}</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        const form = document.getElementById('bookingForm');
        const existingAlert = form.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        form.insertAdjacentHTML('afterbegin', alertHtml);
    }


// Обработка бронирования
    async processBooking(bookingData) {
        const submitButton = document.querySelector('#bookingModal .btn-primary');
        const originalText = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Відправляємо...';

        try {
            // Отправляем данные на сервер
            const response = await fetch('api/send_booking_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(bookingData)
            });

            const result = await response.json();

            if (result.success) {
                this.showBookingSuccess(bookingData, result.booking_id);

                // Сброс формы и закрытие модального окна
                document.getElementById('bookingForm').reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
                modal.hide();
            } else {
                throw new Error(result.error || 'Помилка відправки заявки');
            }

        } catch (error) {
            console.error('Помилка бронювання:', error);
            this.showBookingError(error.message);
        } finally {
            // Восстанавливаем кнопку
            submitButton.disabled = false;
            submitButton.innerHTML = originalText;
        }
    }

// Показать ошибку бронирования
    showBookingError(errorMessage) {
        const errorHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
             style="z-index: 9999; max-width: 500px;" role="alert">
            <h5><i class="fas fa-exclamation-triangle"></i> Помилка!</h5>
            <p class="mb-0">${errorMessage}</p>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

        document.body.insertAdjacentHTML('afterbegin', errorHtml);

        setTimeout(() => {
            const alert = document.querySelector('.alert-danger');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }

    // Оновлена функція підтвердження бронювання з кращою обробкою помилок
    async function confirmBooking() {
    const name = document.getElementById('userName').value.trim();
    const email = document.getElementById('userEmail').value.trim();
    const phone = document.getElementById('userPhone').value.trim();
    const message = document.getElementById('userMessage').value.trim();

    // Валідація
    if (!name || !email || !phone) {
        Swal.fire({
            icon: 'warning',
            title: 'Заповніть всі поля',
            text: 'Будь ласка, заповніть всі обов\'язкові поля',
            confirmButtonText: 'Зрозуміло'
        });
        return;
    }

    // Перевірка email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        Swal.fire({
            icon: 'warning',
            title: 'Некоректний email',
            text: 'Введіть правильний email адрес',
            confirmButtonText: 'Зрозуміло'
        });
        return;
    }

    const bookingData = {
        specialist_id: 1, // ID фотографа
        date: document.getElementById('bookingDate').value,
        time: document.getElementById('bookingTime').value,
        type: document.getElementById('consultationType').value,
        name: name,
        email: email,
        phone: phone,
        message: message
    };

    try {
        // Показуємо індикатор завантаження
        Swal.fire({
            title: 'Обробка бронювання...',
            html: 'Зачекайте, зберігаємо ваше бронювання',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        console.log('Відправляємо дані бронювання:', bookingData);

        const response = await fetch('booking_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData)
        });

        console.log('Статус відповіді:', response.status);

        if (!response.ok) {
            throw new Error(`HTTP помилка! статус: ${response.status}`);
        }

        const result = await response.json();
        console.log('Результат:', result);

        if (result.success) {
            bookingModal.hide();

            // Успішне повідомлення з деталями
            Swal.fire({
                icon: 'success',
                title: '🎉 Консультацію заброньовано!',
                html: `
                    <div style="text-align: left; padding: 20px;">
                        <p><strong>Дякуємо, ${name}!</strong></p>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; margin: 15px 0;">
                            <h4>📋 Деталі бронювання:</h4>
                            <p><strong>Тип:</strong> ${bookingData.type}</p>
                            <p><strong>Дата:</strong> ${new Date(bookingData.date).toLocaleDateString('uk-UA')}</p>
                            <p><strong>Час:</strong> ${bookingData.time}</p>
                            <p><strong>Номер бронювання:</strong> #${result.booking_id}</p>
                        </div>
                        <div style="background: #d4edda; padding: 15px; border-radius: 10px; color: #155724;">
                            <p><strong>📞 Ми зв'яжемося з вами найближчим часом!</strong></p>
                            <p>Перевірте ваш телефон та email для підтвердження деталей.</p>
                        </div>
                    </div>
                `,
                confirmButtonText: 'Чудово!',
                width: 600
            });

            // Очищуємо форми
            document.getElementById('bookingForm').reset();
            document.getElementById('userDataForm').reset();

        } else {
            throw new Error(result.message || result.error || 'Невідома помилка');
        }

    } catch (error) {
        console.error('Помилка бронювання:', error);

        let errorMessage = 'Не вдалося створити бронювання. Спробуйте пізніше.';

        if (error.message.includes('зайнятий')) {
            errorMessage = 'Цей час вже зайнятий. Оберіть інший час.';
        } else if (error.message.includes('HTTP помилка')) {
            errorMessage = 'Помилка з\'єднання з сервером. Перевірте інтернет підключення.';
        } else if (error.message.includes('Помилка збереження')) {
            errorMessage = 'Помилка збереження в базі даних. Зверніться до адміністратора.';
        }

        Swal.fire({
            icon: 'error',
            title: 'Помилка бронювання',
            text: errorMessage,
            footer: 'Якщо проблема повторюється, зв\'яжіться з нами: +380 50 123 45 67',
            confirmButtonText: 'Спробувати ще раз'
        });
    }
}

// Додаткова функція для перевірки з'єднання
    async function testConnection() {
    try {
        const response = await fetch('booking_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({test: true})
        });
        console.log('Тест з\'єднання:', response.status);
    } catch (error) {
        console.error('Помилка тестування:', error);
    }
}

// Викликаємо тест при завантаженні сторінки (для відладки)
    document.addEventListener('DOMContentLoaded', function() {
    console.log('Сторінка завантажена, тестуємо з\'єднання...');
     testConnection(); // Розкоментуйте для тестування
});

// // Обновите функцию успешного бронирования
//     showBookingSuccess(bookingData, bookingId) {
//         const successHtml = `
//         <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3"
//              style="z-index: 9999; max-width: 500px;" role="alert">
//             <h5><i class="fas fa-check-circle"></i> Заявка відправлена успішно!</h5>
//             <p class="mb-1">
//                 <strong>Номер бронювання: #${bookingId}</strong><br>
//                 ${bookingData.specialist_name} зв'яжеться з вами найближчим часом.
//             </p>
//             <small class="text-muted">
//                 Послуга: ${bookingData.service} | Дата: ${bookingData.date} ${bookingData.time}
//             </small>
//             <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
//         </div>
//     `;
//
//         document.body.insertAdjacentHTML('afterbegin', successHtml);
//
//         setTimeout(() => {
//             const alert = document.querySelector('.alert-success');
//             if (alert) {
//                 alert.remove();
//             }
//         }, 7000);
//     }
//
//     // Получить ID специалиста
//     getSpecialistId() {
//         const url = window.location.pathname;
//         if (url.includes('photographer1')) return 1;
//         if (url.includes('designer1')) return 2;
//         if (url.includes('developer1')) return 3;
//         if (url.includes('economist1')) return 4;
//         if (url.includes('hr1')) return 5;
//         return 1;
//     }
//
// // Получить имя специалиста
//     getSpecialistName() {
//         const url = window.location.pathname;
//         const title = document.title;
//
//         // Спочатку намагаємося отримати з title
//         if (title.includes('—')) {
//             return title.split('—')[0].trim();
//         }
//
//         // Альтернативний спосіб з URL
//         if (url.includes('photographer1')) return 'Олександра Савчук';
//         if (url.includes('designer1')) return 'Дизайнер';
//         if (url.includes('developer1')) return 'Python розробник';
//         if (url.includes('economist1')) return 'Економіст';
//         if (url.includes('hr1')) return 'HR-менеджер';
//
//         return 'Спеціаліст';
//     }
//}