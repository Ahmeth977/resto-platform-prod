<?php
// scripts/backup.php
require_once __DIR__ . '/../includes/config.php';

class DatabaseBackup {
    private $db;
    private $backupPath;
    
    public function __construct() {
        $this->backupPath = __DIR__ . '/../backups/';
        $this->ensureDirectoryExists();
    }
    
    private function ensureDirectoryExists() {
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    public function backupDatabase() {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupPath . $filename;
            
            // Commande mysqldump (adaptez selon votre configuration)
            $command = sprintf(
                'mysqldump --host=%s --user=%s --password=%s %s > %s',
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg(DB_NAME),
                escapeshellarg($filepath)
            );
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                $this->compressBackup($filepath);
                $this->cleanOldBackups();
                return ['success' => true, 'file' => $filename];
            } else {
                return ['success' => false, 'error' => 'Erreur mysqldump'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    private function compressBackup($filepath) {
        // Compression optionnelle
        if (extension_loaded('zip')) {
            $zip = new ZipArchive();
            $zipFile = $filepath . '.zip';
            
            if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                $zip->addFile($filepath, basename($filepath));
                $zip->close();
                unlink($filepath); // Supprime le fichier SQL original
            }
        }
    }
    
    private function cleanOldBackups($keepDays = 7) {
        $files = glob($this->backupPath . 'backup_*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 60 * 60 * 24 * $keepDays) {
                    unlink($file);
                }
            }
        }
    }
    
    public function backupUploads() {
        $uploadsDir = __DIR__ . '/../admin/uploads/';
        $backupDir = $this->backupPath . 'uploads_' . date('Y-m-d') . '/';
        
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $this->copyDirectory($uploadsDir, $backupDir);
        return ['success' => true, 'directory' => $backupDir];
    }
    
    private function copyDirectory($source, $destination) {
        $dir = opendir($source);
        @mkdir($destination);
        
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($source . '/' . $file)) {
                    $this->copyDirectory($source . '/' . $file, $destination . '/' . $file);
                } else {
                    copy($source . '/' . $file, $destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}

// Exécution manuelle du backup
if (php_sapi_name() === 'cli') {
    $backup = new DatabaseBackup();
    $result = $backup->backupDatabase();
    print_r($result);
    
    // Backup des uploads une fois par semaine
    if (date('w') == 0) { // Dimanche
        $uploadsResult = $backup->backupUploads();
        print_r($uploadsResult);
    }
}