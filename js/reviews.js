const specialists = {
    "photographer2.html": [
        { name: "Олександра Савчук", value: "savchuk" },
        { name: "Іван Петренко", value: "petrenko" }
    ],
    "designer.html": [
        { name: "Марія Дизайнер", value: "maria" },
        { name: "Олег Креативний", value: "oleh" }
    ],
    "developer2.html": [
        { name: "Олег Розробник", value: "olehdev" },
        { name: "Світлана Кодер", value: "svitlana" }
    ],
    "economist.html": [
        { name: "Вікторія Економіст", value: "viktoria" }
    ],
    "hr.html": [
        { name: "Ірина HR", value: "iryna" }
    ],
    "business-analyst.html": [
        { name: "Дмитро Бізнес-аналітик", value: "dmytro" }
    ],
    "videographer.html": [
        { name: "Андрій Відеограф", value: "andriy" }
    ],
    "smm.html": [
        { name: "Катерина SMM", value: "kateryna" }
    ],
    "data-analyst.html": [
        { name: "Олексій Data Analyst", value: "oleksii" }
    ],
    "devOps.html": [
        { name: "Максим DevOps", value: "maksym" }
    ],
    "3d-designer.html": [
        { name: "Валерія 3D Дизайнер", value: "valeria" }
    ]
};

// Пример отзывов (можно расширять)
const reviews = [
    {
        category: "photographer2.html",
        specialist: "savchuk",
        author: "Анна",
        text: "Дуже задоволена фотосесією! Професійний підхід.",
        date: "2024-12-01"
    },
    {
        category: "photographer2.html",
        specialist: "petrenko",
        author: "Ігор",
        text: "Фото вийшли чудові, дякую!",
        date: "2025-01-15"
    },
    {
        category: "designer.html",
        specialist: "maria",
        author: "Олена",
        text: "Дизайн сайту перевершив очікування!",
        date: "2025-02-10"
    },
    {
        category: "developer2.html",
        specialist: "olehdev",
        author: "Василь",
        text: "Швидко та якісно реалізовано проект.",
        date: "2025-03-05"
    },
    {
        category: "hr.html",
        specialist: "iryna",
        author: "Марко",
        text: "Допомогла знайти чудову команду.",
        date: "2025-03-20"
    }
    // ...добавьте еще отзывы по необходимости
];


// DOM элементы
const categoryFilter = document.getElementById('category-filter');
const specialistFilter = document.getElementById('specialist-filter');
const reviewsContainer = document.querySelector('.reviews-container');

// Обновить список специалистов при выборе категории
categoryFilter.addEventListener('change', function() {
    const category = this.value;
    specialistFilter.innerHTML = '<option value="">Всі спеціалісти</option>';
    if (specialists[category]) {
        specialists[category].forEach(spec => {
            const option = document.createElement('option');
            option.value = spec.value;
            option.textContent = spec.name;
            specialistFilter.appendChild(option);
        });
    }
    renderReviews();
});

// Фильтрация по специалисту
specialistFilter.addEventListener('change', renderReviews);

// Первичный рендер
renderReviews();

function renderReviews() {
    const category = categoryFilter.value;
    const specialist = specialistFilter.value;
    let filtered = reviews;

    if (category) {
        filtered = filtered.filter(r => r.category === category);
    }
    if (specialist) {
        filtered = filtered.filter(r => r.specialist === specialist);
    }

    reviewsContainer.innerHTML = '';
    if (filtered.length === 0) {
        reviewsContainer.innerHTML = '<div class="text-muted">Відгуків не знайдено.</div>';
        return;
    }

    filtered.forEach(r => {
        const card = document.createElement('div');
        card.className = 'review-card mb-3 p-3 border rounded bg-light';
        card.innerHTML = `
            <div class="fw-bold">${r.author}</div>
            <div class="text-muted small mb-2">${formatDate(r.date)}</div>
            <div>${r.text}</div>
        `;
        reviewsContainer.appendChild(card);
    });
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('uk-UA');
}