<?php
// app.php - Point d'entrée unique pour Google App Engine
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Router les requêtes
if ($path === '/' || $path === '/index.php') {
    require __DIR__ . '/index.php';
} elseif (strpos($path, '/restaurant.php') === 0) {
    require __DIR__ . '/restaurant.php';
} elseif (strpos($path, '/assets/') === 0) {
    // Servir les fichiers statiques directement
    $file_path = __DIR__ . $path;
    if (file_exists($file_path)) {
        $mime_types = [
            '.css' => 'text/css',
            '.js' => 'application/javascript',
            '.png' => 'image/png',
            '.jpg' => 'image/jpeg',
            '.jpeg' => 'image/jpeg',
            '.gif' => 'image/gif',
            '.svg' => 'image/svg+xml',
            '.ico' => 'image/x-icon',
        ];
        
        $ext = strtolower(strrchr($path, '.'));
        if (isset($mime_types[$ext])) {
            header('Content-Type: ' . $mime_types[$ext]);
        }
        
        readfile($file_path);
    } else {
        http_response_code(404);
        echo 'Fichier non trouvé';
    }
} else {
    // Page non trouvée
    http_response_code(404);
    echo 'Page non trouvée';
}
?>
