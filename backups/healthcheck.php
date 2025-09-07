<?php
// healthcheck.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Fonction pour vérifier la connexion à la base de données
function check_database_connection() {
    try {
        require_once __DIR__ . '/includes/config.php';
        
        // Tentative de connexion
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Test de requête simple
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        return [
            'connected' => true,
            'status' => 'OK',
            'database' => DB_NAME,
            'test_query' => $result['test'] === 1
        ];
        
    } catch (PDOException $e) {
        return [
            'connected' => false,
            'status' => 'ERROR',
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}

// Fonction pour vérifier l'espace disque
function check_disk_space() {
    $free = disk_free_space(__DIR__);
    $total = disk_total_space(__DIR__);
    $used = $total - $free;
    
    return [
        'free' => round($free / 1024 / 1024, 2) . ' MB',
        'used' => round($used / 1024 / 1024, 2) . ' MB',
        'total' => round($total / 1024 / 1024, 2) . ' MB',
        'percentage' => round(($used / $total) * 100, 2) . '%'
    ];
}

// Fonction pour vérifier les services essentiels
function check_essential_services() {
    $services = [];
    
    // Vérification MySQL
    $services['mysql'] = @fsockopen(DB_HOST, 3306) !== false;
    
    // Vérification HTTP
    $services['http'] = function_exists('apache_get_version') || 
                       (function_exists('php_sapi_name') && php_sapi_name() !== 'cli');
    
    // Vérification des répertoires importants
    $essential_dirs = [
        'admin/uploads' => is_writable(__DIR__ . '/admin/uploads'),
        'includes' => is_readable(__DIR__ . '/includes'),
        'assets' => is_readable(__DIR__ . '/assets')
    ];
    
    $services['directories'] = $essential_dirs;
    
    return $services;
}

// Collecte des informations de santé
$healthData = [
    'status' => 'OK',
    'timestamp' => time(),
    'datetime' => date('Y-m-d H:i:s'),
    'server' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
        'hostname' => gethostname()
    ],
    'database' => check_database_connection(),
    'disk_space' => check_disk_space(),
    'services' => check_essential_services(),
    'memory' => [
        'usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
        'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB'
    ],
    'uptime' => function_exists('sys_getloadavg') ? sys_getloadavg() : 'N/A'
];

// Vérification globale
if (!$healthData['database']['connected']) {
    $healthData['status'] = 'ERROR';
    http_response_code(500);
}

// Output en JSON
echo json_encode($healthData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Log pour le monitoring
if ($healthData['status'] === 'ERROR') {
    error_log('HEALTHCHECK ERROR: ' . json_encode($healthData));
}