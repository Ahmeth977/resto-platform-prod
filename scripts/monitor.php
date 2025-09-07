<?php
// scripts/monitor.php
require_once __DIR__ . '/../includes/config.php';

class SystemMonitor {
    public function checkAll() {
        return [
            'database' => $this->checkDatabase(),
            'disk' => $this->checkDisk(),
            'services' => $this->checkServices(),
            'backups' => $this->checkBackups()
        ];
    }
    
    private function checkDatabase() {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS
            );
            
            // Vérification des tables importantes
            $tables = ['users', 'restaurants', 'orders', 'products'];
            $tableStatus = [];
            
            foreach ($tables as $table) {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
                $tableStatus[$table] = $stmt->fetch()['count'] > 0;
            }
            
            return ['status' => 'OK', 'tables' => $tableStatus];
            
        } catch (PDOException $e) {
            return ['status' => 'ERROR', 'error' => $e->getMessage()];
        }
    }
    
    private function checkDisk() {
        $path = __DIR__ . '/../';
        $free = disk_free_space($path);
        $total = disk_total_space($path);
        $percent = ($total - $free) / $total * 100;
        
        return [
            'free' => $this->formatBytes($free),
            'used' => $this->formatBytes($total - $free),
            'total' => $this->formatBytes($total),
            'percent' => round($percent, 2),
            'status' => $percent > 90 ? 'WARNING' : 'OK'
        ];
    }
    
    private function checkServices() {
        $services = [
            'mysql' => $this->checkPort(DB_HOST, 3306),
            'http' => $this->checkUrl('http://localhost')
        ];
        
        return $services;
    }
    
    private function checkBackups() {
        $backupDir = __DIR__ . '/../backups/';
        $backups = glob($backupDir . 'backup_*.sql*');
        
        $latestBackup = count($backups) > 0 ? 
            filemtime($backups[count($backups) - 1]) : null;
        
        $hoursSinceBackup = $latestBackup ? 
            (time() - $latestBackup) / 3600 : null;
        
        return [
            'count' => count($backups),
            'latest' => $latestBackup ? date('Y-m-d H:i:s', $latestBackup) : null,
            'hours_since_last' => round($hoursSinceBackup, 1),
            'status' => $hoursSinceBackup > 24 ? 'WARNING' : 'OK'
        ];
    }
    
    private function checkPort($host, $port) {
        $timeout = 5;
        $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }
    
    private function checkUrl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode >= 200 && $httpCode < 400;
    }
    
    private function formatBytes($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

// Exécution
if (php_sapi_name() === 'cli') {
    $monitor = new SystemMonitor();
    $results = $monitor->checkAll();
    
    echo "=== SYSTEM MONITOR ===\n";
    echo "Date: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($results as $category => $data) {
        echo strtoupper($category) . ":\n";
        print_r($data);
        echo "\n";
    }
}