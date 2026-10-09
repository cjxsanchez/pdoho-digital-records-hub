<?php
class BackupController {
    public static function manage($pdo) {
        $action = $_GET['action'] ?? 'view';

        if ($action === 'db_dump') {
            self::backupDB($pdo);
        } elseif ($action === 'files_zip') {
            self::backupFiles();
        }
    }

    private static function backupDB($pdo) {
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sqlScript = "";
        
        foreach ($tables as $table) {
            $result = $pdo->query("SELECT * FROM $table");
            $num_fields = $result->columnCount();
            $sqlScript .= "DROP TABLE IF EXISTS $table;";
            $createTable = $pdo->query("SHOW CREATE TABLE $table")->fetch(PDO::FETCH_NUM);
            $sqlScript .= "\n\n" . $createTable[1] . ";\n\n";
            
            for ($i = 0; $i < $num_fields; $i++) {
                while ($row = $result->fetch(PDO::FETCH_NUM)) {
                    $sqlScript .= "INSERT INTO $table VALUES(";
                    for ($j = 0; $j < $num_fields; $j++) {
                        $row[$j] = addslashes($row[$j]);
                        $row[$j] = preg_replace("/\n/", "\\n", $row[$j]);
                        if (isset($row[$j])) { $sqlScript .= '"' . $row[$j] . '"'; } else { $sqlScript .= '""'; }
                        if ($j < ($num_fields - 1)) { $sqlScript .= ','; }
                    }
                    $sqlScript .= ");\n";
                }
            }
            $sqlScript .= "\n\n\n";
        }

        $backup_name = "db_backup_" . date("Y-m-d_H-i-s") . ".sql";
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $backup_name . "\"");
        echo $sqlScript;
        exit;
    }

    private static function backupFiles() {
        $rootPath = realpath('../public/uploads');
        $zip = new ZipArchive();
        $filename = 'files_backup_' . date("Y-m-d_H-i-s") . '.zip';
        $zipPath = '../public/backups/files/' . $filename;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("Cannot create zip");
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
        
        header('Content-Type: application/zip');
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        exit;
    }
}
?>