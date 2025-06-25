
<?php
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .db-card { background: #f8f9fa; margin: 15px 0; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff; }
    .success { border-left-color: #28a745; background: #d4edda; }
    .error { border-left-color: #dc3545; background: #f8d7da; }
    .warning { border-left-color: #ffc107; background: #fff3cd; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px 12px; border: 1px solid #ddd; text-align: left; }
    th { background: #e9ecef; font-weight: bold; }
    .status { font-weight: bold; padding: 5px 10px; border-radius: 15px; }
    .status-ok { background: #28a745; color: white; }
    .status-error { background: #dc3545; color: white; }
    .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
    .stat-card { background: #e9ecef; padding: 15px; border-radius: 8px; text-align: center; }
</style>";

echo "<div class='container'>";
echo "<h1>🔍 Діагностика баз даних сайту</h1>";

// Конфігурації для тестування
$database_configs = [
    [
        'name' => 'Поточна база (specialists_db)',
        'host' => 'localhost',
        'port' => 3307,
        'dbname' => 'specialists_db',
        'username' => 'root',
        'password' => '',
        'description' => 'Основна база даних сайту згідно з конфігурацією'
    ],
    [
        'name' => 'Альтернативна база (specialists_portal)',
        'host' => 'localhost',
        'port' => 3307,
        'dbname' => 'specialists_portal',
        'username' => 'root',
        'password' => '',
        'description' => 'Перевірка наявності альтернативної бази'
    ],
    [
        'name' => 'Стандартний XAMPP MySQL',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'specialists_db',
        'username' => 'root',
        'password' => '',
        'description' => 'Стандартний порт XAMPP'
    ],
    [
        'name' => 'Стандартний XAMPP (specialists_portal)',
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'specialists_portal',
        'username' => 'root',
        'password' => '',
        'description' => 'Альтернативна база на стандартному порті'
    ]
];

$working_databases = [];
$all_databases_list = [];

foreach ($database_configs as $config) {
    echo "<div class='db-card";
    
    try {
        // Спробуємо підключитися до бази
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo " success'>";
        echo "<h2>✅ {$config['name']}</h2>";
        echo "<p class='status status-ok'>ПРАЦЮЄ</p>";
        echo "<p><strong>Опис:</strong> {$config['description']}</p>";
        
        // Зберігаємо робочу конфігурацію
        $working_databases[] = $config;
        
        // Отримуємо інформацію про базу
        echo "<h4>📊 Інформація про базу:</h4>";
        
        // Версія MySQL
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch();
        echo "<p><strong>Версія MySQL:</strong> {$version['version']}</p>";
        
        // Список таблиць
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p><strong>Таблиці в базі ({count($tables)}):</strong></p>";
        if ($tables) {
            echo "<ul>";
            foreach ($tables as $table) {
                // Отримуємо кількість записів у таблиці
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as count FROM `$table`");
                    $count = $stmt->fetch();
                    echo "<li><strong>$table</strong> - {$count['count']} записів</li>";
                } catch (Exception $e) {
                    echo "<li><strong>$table</strong> - помилка підрахунку</li>";
                }
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>⚠️ Таблиць не знайдено</p>";
        }
        
        // Перевірка основних таблиць сайту
        $required_tables = ['specialists', 'reviews', 'bookings'];
        $existing_tables = array_intersect($required_tables, $tables);
        $missing_tables = array_diff($required_tables, $tables);
        
        if ($existing_tables) {
            echo "<p style='color: green;'><strong>✅ Знайдені таблиці сайту:</strong> " . implode(', ', $existing_tables) . "</p>";
        }
        
        if ($missing_tables) {
            echo "<p style='color: orange;'><strong>⚠️ Відсутні таблиці:</strong> " . implode(', ', $missing_tables) . "</p>";
        }
        
        // Конфігурація підключення
        echo "<h4>⚙️ Конфігурація підключення:</h4>";
        echo "<table>";
        echo "<tr><th>Параметр</th><th>Значення</th></tr>";
        echo "<tr><td>Host</td><td>{$config['host']}</td></tr>";
        echo "<tr><td>Port</td><td>{$config['port']}</td></tr>";
        echo "<tr><td>Database</td><td>{$config['dbname']}</td></tr>";
        echo "<tr><td>Username</td><td>{$config['username']}</td></tr>";
        echo "<tr><td>Password</td><td>" . (empty($config['password']) ? '(порожній)' : '***') . "</td></tr>";
        echo "</table>";
        
    } catch (PDOException $e) {
        echo " error'>";
        echo "<h2>❌ {$config['name']}</h2>";
        echo "<p class='status status-error'>НЕ ПРАЦЮЄ</p>";
        echo "<p><strong>Опис:</strong> {$config['description']}</p>";
        echo "<p><strong>Помилка:</strong> " . $e->getMessage() . "</p>";
        
        // Спробуємо підключитися без вказання бази даних
        try {
            $dsn_without_db = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
            $pdo_test = new PDO($dsn_without_db, $config['username'], $config['password']);
            
            echo "<p style='color: orange;'>⚠️ MySQL сервер доступний, але база даних '{$config['dbname']}' не існує</p>";
            
            // Показуємо доступні бази
            $stmt = $pdo_test->query("SHOW DATABASES");
            $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $all_databases_list = array_merge($all_databases_list, $databases);
            
        } catch (PDOException $e2) {
            echo "<p style='color: red;'>❌ MySQL сервер недоступний на порті {$config['port']}</p>";
        }
    }
    
    echo "</div>";
}

// Показуємо всі доступні бази даних
if ($all_databases_list) {
    $unique_databases = array_unique($all_databases_list);
    echo "<div class='db-card warning'>";
    echo "<h2>📋 Всі доступні бази даних</h2>";
    echo "<ul>";
    foreach ($unique_databases as $db) {
        echo "<li>$db</li>";
    }
    echo "</ul>";
    echo "</div>";
}

// Підсумок
echo "<div class='db-card'>";
echo "<h2>📈 Підсумок діагностики</h2>";

echo "<div class='stats'>";
echo "<div class='stat-card'>";
echo "<h3>" . count($working_databases) . "</h3>";
echo "<p>Робочих підключень</p>";
echo "</div>";

echo "<div class='stat-card'>";
echo "<h3>" . (count($database_configs) - count($working_databases)) . "</h3>";
echo "<p>Неробочих підключень</p>";
echo "</div>";

if ($working_databases) {
    $main_config = $working_databases[0];
    echo "<div class='stat-card'>";
    echo "<h3>{$main_config['dbname']}</h3>";
    echo "<p>Основна база</p>";
    echo "</div>";
    
    echo "<div class='stat-card'>";
    echo "<h3>{$main_config['port']}</h3>";
    echo "<p>Робочий порт</p>";
    echo "</div>";
}
echo "</div>";

if ($working_databases) {
    echo "<h3>✅ Рекомендовані налаштування для database.php:</h3>";
    $main_config = $working_databases[0];
    
    echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;'>";
    echo htmlspecialchars("<?php
// Конфігурація бази даних
\$host = '{$main_config['host']}';
\$port = '{$main_config['port']}';
\$dbname = '{$main_config['dbname']}';
\$username = '{$main_config['username']}';
\$password = '{$main_config['password']}';

try {
    \$pdo = new PDO(\"mysql:host=\$host;port=\$port;dbname=\$dbname;charset=utf8mb4\", \$username, \$password);
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException \$e) {
    error_log(\"Помилка підключення до БД: \" . \$e->getMessage());
    die(\"Помилка підключення до бази даних. Перевірте налаштування.\");
}
?>");
    echo "</pre>";
    
    echo "<h3>🔧 Для створення бази specialists_portal (якщо потрібно):</h3>";
    echo "<p>Запустіть наступний SQL запит:</p>";
    echo "<pre style='background: #fff3cd; padding: 15px; border-radius: 5px;'>";
    echo "CREATE DATABASE IF NOT EXISTS specialists_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    echo "</pre>";
    
} else {
    echo "<h3 style='color: red;'>❌ Жодна база даних не працює!</h3>";
    echo "<p>Перевірте:</p>";
    echo "<ul>";
    echo "<li>Чи запущено MySQL в XAMPP Control Panel</li>";
    echo "<li>Чи правильний порт (3306 або 3307)</li>";
    echo "<li>Чи правильні логін і пароль</li>";
    echo "</ul>";
}

echo "</div>";
echo "</div>";
?>