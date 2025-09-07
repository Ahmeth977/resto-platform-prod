<?php
// scripts/backup_cron.php
require_once __DIR__ . '/backup.php';

$backup = new DatabaseBackup();

// Backup quotidien de la base de données
$dbResult = $backup->backupDatabase();
error_log('Database backup: ' . json_encode($dbResult));

// Backup des uploads le dimanche
if (date('w') == 0) {
    $uploadsResult = $backup->backupUploads();
    error_log('Uploads backup: ' . json_encode($uploadsResult));
}