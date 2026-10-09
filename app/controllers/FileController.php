<?php
class FileController {

    // 1. DASHBOARD
    public static function index($pdo) {
        $stats = [];
        $stats['admins'] = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
        $stats['files'] = $pdo->query("SELECT COUNT(*) FROM files WHERE is_deleted=0 AND is_archived=0")->fetchColumn();
        $stats['folders'] = $pdo->query("SELECT COUNT(*) FROM folders WHERE is_archived=0")->fetchColumn();

        $stmt = $pdo->prepare("SELECT * FROM files WHERE is_deleted=0 AND is_archived=0 ORDER BY uploaded_at DESC LIMIT 5");
        $stmt->execute();
        $recent_files = $stmt->fetchAll();

        // --- DYNAMIC DATA FOR CHARTS ---
        $months = array_fill(1, 12, 0); 
        $stmt_months = $pdo->query("
            SELECT MONTH(uploaded_at) as m, COUNT(*) as c 
            FROM files 
            WHERE is_deleted = 0 AND is_archived = 0 AND YEAR(uploaded_at) = YEAR(CURDATE()) 
            GROUP BY MONTH(uploaded_at)
        ");
        while ($row = $stmt_months->fetch()) {
            $months[$row['m']] = (int)$row['c'];
        }
        $monthly_data = array_values($months);

        $stmt_folders = $pdo->query("
            SELECT f.folder_name, COUNT(fi.id) as file_count 
            FROM folders f 
            LEFT JOIN files fi ON f.id = fi.folder_id AND fi.is_deleted = 0 AND fi.is_archived = 0
            WHERE f.is_archived = 0
            GROUP BY f.id, f.folder_name
        ");
        
        $folder_labels = [];
        $folder_counts = [];
        while ($row = $stmt_folders->fetch()) {
            $folder_labels[] = $row['folder_name'];
            $folder_counts[] = (int)$row['file_count'];
        }

        require '../app/views/dashboard.php';
    }

    // --- Create Folder & Sub-Folder ---
    public static function createFolder($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");
            
            $folder_name = clean($_POST['folder_name']);
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

            // Prevent manual creation of the system folder
            if (strtolower($folder_name) === 'recovered files') {
                header("Location: index.php?route=categories&msg=error_system_folder");
                exit;
            }

            $stmt = $pdo->prepare("INSERT INTO folders (folder_name, created_by, parent_id) VALUES (?, ?, ?)");
            $stmt->execute([$folder_name, $_SESSION['admin_id'], $parent_id]);
            
            if ($parent_id) {
                header("Location: index.php?route=folder/view&id=" . $parent_id . "&msg=folder_created");
            } else {
                header("Location: index.php?route=categories&msg=folder_created");
            }
            exit;
        }
    }

    // 2. CATEGORIES
    public static function categories($pdo) {
        $search = $_GET['q'] ?? '';

        if ($search) {
            $year_search = is_numeric(trim($search)) ? (int)trim($search) : 0;
            
            $file_conditions = [
                "is_deleted = 0",
                "is_archived = 0", 
                "(MATCH(title, name, description, file_content) AGAINST (:search IN BOOLEAN MODE) OR year = :year_search)"
            ];
            $file_sql = "SELECT *, MATCH(title, name, description, file_content) AGAINST (:search IN BOOLEAN MODE) as score 
                         FROM files WHERE " . implode(' AND ', $file_conditions) . " ORDER BY title ASC LIMIT 100";
            
            $stmt = $pdo->prepare($file_sql);
            $stmt->execute([':search' => $search, ':year_search' => $year_search]);
            $files = $stmt->fetchAll();

            $folder_sql = "SELECT f.*, 
                           (SELECT COUNT(*) FROM files WHERE folder_id = f.id AND is_deleted = 0 AND is_archived = 0) as file_count 
                           FROM folders f 
                           WHERE f.folder_name LIKE :folder_search AND f.is_archived = 0
                           ORDER BY CASE WHEN f.folder_name = 'Recovered Files' THEN 1 ELSE 0 END, f.folder_name ASC";
            
            $folder_stmt = $pdo->prepare($folder_sql);
            $folder_stmt->execute([':folder_search' => '%' . $search . '%']);
            $folders = $folder_stmt->fetchAll();

            $is_search = true;
            self::log($pdo, 'search', null, "Search Query: " . $search);
        } else {
            // Keep Recovered Files at the bottom of the grid, only show root folders
            $stmt = $pdo->query("SELECT f.*, 
                                (SELECT COUNT(*) FROM files WHERE folder_id = f.id AND is_deleted = 0 AND is_archived = 0) as file_count 
                                 FROM folders f WHERE f.is_archived = 0 AND f.parent_id IS NULL
                                 ORDER BY CASE WHEN f.folder_name = 'Recovered Files' THEN 1 ELSE 0 END, f.folder_name ASC");
            $folders = $stmt->fetchAll();
            $files = []; 
            $is_search = false;
        }

        require '../app/views/categories.php';
    }

    // 3. FOLDER VIEW
    public static function folderView($pdo) {
        $folder_id = $_GET['id'] ?? 0;
        $sort_option = $_GET['sort'] ?? 'date_desc';

        $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ? AND is_archived = 0");
        $stmt->execute([$folder_id]);
        $folder = $stmt->fetch();
        if (!$folder) die("Folder not found or has been archived.");

        // Fetch sub-folders
        $stmt_sub = $pdo->prepare("SELECT * FROM folders WHERE parent_id = ? AND is_archived = 0 ORDER BY folder_name ASC");
        $stmt_sub->execute([$folder_id]);
        $sub_folders = $stmt_sub->fetchAll();

        $sql = "SELECT f.*, a.username as owner_name 
                FROM files f 
                LEFT JOIN admins a ON f.uploaded_by = a.id 
                WHERE f.folder_id = ? AND f.is_deleted = 0 AND f.is_archived = 0";
        
        switch ($sort_option) {
            case 'name_asc': $sql .= " ORDER BY f.title ASC"; break;
            case 'name_desc': $sql .= " ORDER BY f.title DESC"; break;
            case 'date_asc': $sql .= " ORDER BY f.updated_at ASC"; break;
            case 'date_desc': default: $sql .= " ORDER BY f.updated_at DESC"; break;
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$folder_id]);
        $files = $stmt->fetchAll();

        require '../app/views/folder_view.php';
    }

    // --- ARCHIVES VIEW ---
    public static function archives($pdo) {
        $stmt_folders = $pdo->query("SELECT f.*, 
                                    (SELECT COUNT(*) FROM files WHERE folder_id = f.id AND is_deleted = 0) as file_count 
                                     FROM folders f WHERE f.is_archived = 1 ORDER BY f.folder_name ASC");
        $folders = $stmt_folders->fetchAll();

        // Join folders table so the view knows if the parent folder is archived
        $stmt_files = $pdo->query("SELECT f.*, a.username as owner_name, 
                                   fol.is_archived as folder_archived, fol.folder_name
                                   FROM files f 
                                   LEFT JOIN admins a ON f.uploaded_by = a.id 
                                   LEFT JOIN folders fol ON f.folder_id = fol.id
                                   WHERE f.is_archived = 1 AND f.is_deleted = 0
                                   ORDER BY f.updated_at DESC");
        $files = $stmt_files->fetchAll();

        require '../app/views/archives.php';
    }

    // 4. UPLOAD
    public static function upload($pdo) {
        $content_length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if (empty($_POST) && empty($_FILES) && $content_length > 0) {
            $error_msg = "Upload failed: The total upload size exceeds the server limit.";
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo $error_msg; exit;
            }
            die($error_msg);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");

        $folder_id = !empty($_POST['folder_id']) ? (int)$_POST['folder_id'] : null; 

        if (isset($_POST['multiple_upload']) && $_POST['multiple_upload'] == '1') {
            $year = date('Y'); 
            $name = "Batch Upload"; 
            
            if (!empty($_FILES['files']['name'][0])) {
                if (count($_FILES['files']['name']) > 1000) {
                    $error_msg = "Upload failed: Maximum of 1000 files allowed per upload.";
                    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo $error_msg; exit;
                    }
                    die($error_msg);
                }

                foreach ($_FILES['files']['name'] as $key => $filename) {
                    $tmp_name = $_FILES['files']['tmp_name'][$key];
                    $size = $_FILES['files']['size'][$key];
                    $error = $_FILES['files']['error'][$key];
                    
                    if ($error !== UPLOAD_ERR_OK || empty($filename)) continue;
                    
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $allowed_exts = ['pdf', 'docx'];
                    if (!in_array($ext, $allowed_exts)) continue;

                    $title = pathinfo($filename, PATHINFO_FILENAME);
                    $title_clean = clean($title);
                    
                    $dir_name = $year . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $title_clean . '_' . $name);
                    $target_dir = "../public/uploads/" . $dir_name . "/";
                    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

                    $new_filename = hash('sha256', time() . $filename . $key) . '.' . $ext;
                    $target_path = $target_dir . $new_filename;

                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $content = self::processFileContent($target_path, $ext);

                        $stmt = $pdo->prepare("INSERT INTO files (folder_id, year, title, name, file_path, file_hash, file_size, file_content, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$folder_id, $year, $title_clean, $name, $target_path, hash_file('sha256', $target_path), $size, $content, $_SESSION['admin_id']]);

                        self::log($pdo, 'upload', $pdo->lastInsertId(), "Batch Uploaded: $title_clean");
                    }
                }
            }
            
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                echo "success"; exit;
            }

            if ($folder_id) {
                header("Location: index.php?route=folder/view&id=" . $folder_id . "&msg=uploaded");
            } else {
                header("Location: index.php?route=categories&msg=uploaded");
            }
            exit;
        }
    }

    // 5. DOWNLOAD
    public static function download($pdo) {
        $id = $_GET['id'];
        $force_download = isset($_GET['action']) && $_GET['action'] === 'download';
        
        $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if ($file && file_exists($file['file_path'])) {
            self::log($pdo, 'download', $id, "Accessed: {$file['title']}");
            
            $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
            $content_type = ($ext === 'docx') ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'application/pdf';
            $disposition = $force_download ? 'attachment' : 'inline';

            header('Content-Type: ' . $content_type);
            header('Content-Disposition: ' . $disposition . '; filename="' . htmlspecialchars($file['title']) . '.' . $ext . '"');
            header('Content-Length: ' . filesize($file['file_path']));
            readfile($file['file_path']);
            exit;
        }
        die("File not found.");
    }

    // --- RENAME FILE ---
    public static function renameFile($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");
            $file_id = (int)$_POST['file_id'];
            $folder_id = (int)$_POST['folder_id'];
            $new_title = clean($_POST['new_title']);

            $stmt = $pdo->prepare("UPDATE files SET title = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$new_title, $file_id]);

            self::log($pdo, 'rename', $file_id, "Renamed file to: " . $new_title);
            header("Location: index.php?route=folder/view&id=$folder_id&msg=file_renamed");
            exit;
        }
    }

    // --- ARCHIVE LOGIC ---
    public static function archiveFile($pdo) {
        $id = (int)$_GET['id'];
        $folder_id = $_GET['folder_id'] ?? null;

        $stmt = $pdo->prepare("UPDATE files SET is_archived = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$id]);
        self::log($pdo, 'archive', $id, "Archived file ID: " . $id);

        if ($folder_id) {
            header("Location: index.php?route=folder/view&id=" . (int)$folder_id . "&msg=file_archived");
        } else {
            header("Location: index.php?route=categories&msg=file_archived");
        }
        exit;
    }

    public static function archiveFolder($pdo) {
        $folder_id = (int)$_GET['id'];
        
        // Prevent archiving the system folder
        $stmt = $pdo->prepare("SELECT folder_name FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        if ($stmt->fetchColumn() === 'Recovered Files') {
            header("Location: index.php?route=categories&msg=error_system_folder"); exit;
        }

        $stmt = $pdo->prepare("UPDATE folders SET is_archived = 1 WHERE id = ?");
        $stmt->execute([$folder_id]);

        $stmtFiles = $pdo->prepare("UPDATE files SET is_archived = 1 WHERE folder_id = ? AND is_deleted = 0");
        $stmtFiles->execute([$folder_id]);

        self::log($pdo, 'archive_folder', null, "Archived folder ID: " . $folder_id);
        header("Location: index.php?route=categories&msg=folder_archived");
        exit;
    }

    // --- SMART RESTORE LOGIC ---
    public static function restoreFile($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");
            
            $file_id = (int)$_POST['file_id'];
            $restore_type = $_POST['restore_type'] ?? 'direct'; // 'file_only', 'file_with_folder', or 'direct'

            $stmt = $pdo->prepare("SELECT folder_id FROM files WHERE id = ?");
            $stmt->execute([$file_id]);
            $file = $stmt->fetch();
            if (!$file) die("File not found.");
            
            $folder_id = $file['folder_id'];

            if ($restore_type === 'file_with_folder') {
                // Restore File + Folder
                $pdo->prepare("UPDATE files SET is_archived = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$file_id]);
                $pdo->prepare("UPDATE folders SET is_archived = 0 WHERE id = ?")->execute([$folder_id]);
                self::log($pdo, 'restore', $file_id, "Restored file and its folder.");
                header("Location: index.php?route=archives&msg=restored_both");
            } 
            elseif ($restore_type === 'file_only') {
                // Restore File Only -> Move to Recovered Files
                $stmtRec = $pdo->prepare("SELECT id FROM folders WHERE folder_name = 'Recovered Files' LIMIT 1");
                $stmtRec->execute();
                $recFolder = $stmtRec->fetch();
                
                if ($recFolder) {
                    $recovered_folder_id = $recFolder['id'];
                    $pdo->prepare("UPDATE folders SET is_archived = 0 WHERE id = ?")->execute([$recovered_folder_id]);
                } else {
                    $stmtIns = $pdo->prepare("INSERT INTO folders (folder_name, created_by, is_archived) VALUES ('Recovered Files', ?, 0)");
                    $stmtIns->execute([$_SESSION['admin_id']]);
                    $recovered_folder_id = $pdo->lastInsertId();
                }

                $pdo->prepare("UPDATE files SET is_archived = 0, folder_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$recovered_folder_id, $file_id]);
                self::log($pdo, 'restore', $file_id, "Restored file to Recovered Files.");
                header("Location: index.php?route=archives&msg=restored_recovered");
            } 
            else {
                // Direct restore (folder is already active)
                $pdo->prepare("UPDATE files SET is_archived = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$file_id]);
                self::log($pdo, 'restore', $file_id, "Restored file.");
                header("Location: index.php?route=archives&msg=restored");
            }
            exit;
        } else {
            // Fallback for direct GET request
            $id = (int)$_GET['id'];
            $stmt = $pdo->prepare("UPDATE files SET is_archived = 0, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$id]);
            header("Location: index.php?route=archives&msg=restored");
            exit;
        }
    }

    public static function restoreFolder($pdo) {
        $folder_id = (int)$_GET['id'];
        $stmt = $pdo->prepare("UPDATE folders SET is_archived = 0 WHERE id = ?");
        $stmt->execute([$folder_id]);

        $stmtFiles = $pdo->prepare("UPDATE files SET is_archived = 0 WHERE folder_id = ? AND is_deleted = 0");
        $stmtFiles->execute([$folder_id]);
        header("Location: index.php?route=archives&msg=restored");
        exit;
    }

    // --- TRUE HARD DELETE LOGIC (INDIVIDUAL FILE) ---
    public static function delete($pdo) {
        $id = (int)($_GET['id'] ?? 0);
        $folder_id = $_GET['folder_id'] ?? null;

        // 1. Get the file details (specifically the physical path) before deleting
        $stmt = $pdo->prepare("SELECT title, file_path FROM files WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();

        if ($file) {
            // 2. Permanently delete the physical file from the XAMPP storage directory
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }

            // 3. Completely erase the record from the MySQL database
            $deleteStmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $deleteStmt->execute([$id]);

            self::log($pdo, 'delete', $id, "Permanently deleted file: " . $file['title']);
        }

        if ($folder_id) {
            header("Location: index.php?route=folder/view&id=" . (int)$folder_id . "&msg=deleted");
        } else {
            $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?route=dashboard';
            header("Location: $referer");
        }
        exit;
    }

    public static function editFolder($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF failed");
            $folder_id = (int)$_POST['folder_id'];
            $new_name = clean($_POST['new_folder_name']);

            // Prevent renaming the system folder
            $stmt = $pdo->prepare("SELECT folder_name FROM folders WHERE id = ?");
            $stmt->execute([$folder_id]);
            if ($stmt->fetchColumn() === 'Recovered Files') {
                header("Location: index.php?route=categories&msg=error_system_folder"); exit;
            }

            $stmt = $pdo->prepare("UPDATE folders SET folder_name = ? WHERE id = ?");
            $stmt->execute([$new_name, $folder_id]);
            header("Location: index.php?route=categories&msg=folder_updated");
            exit;
        }
    }

    // --- TRUE HARD DELETE LOGIC (FOLDER & ALL CONTENTS) ---
    public static function deleteFolder($pdo) {
        $folder_id = (int)$_GET['id'];
        
        // Prevent deleting the system folder
        $stmt = $pdo->prepare("SELECT folder_name FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        if ($stmt->fetchColumn() === 'Recovered Files') {
            header("Location: index.php?route=categories&msg=error_system_folder"); exit;
        }

        // 1. Get ALL files inside this folder (even archived ones)
        $stmt = $pdo->prepare("SELECT id, file_path, title FROM files WHERE folder_id = ?");
        $stmt->execute([$folder_id]);
        $files = $stmt->fetchAll();

        // 2. Loop through and physically destroy every file and its DB record
        foreach ($files as $file) {
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']); // Wipes from hard drive
            }
            
            $deleteFile = $pdo->prepare("DELETE FROM files WHERE id = ?");
            $deleteFile->execute([$file['id']]); // Wipes from DB
            
            self::log($pdo, 'delete', $file['id'], "Permanently deleted file via Folder Deletion: " . $file['title']);
        }

        // 3. Completely erase the folder record from the MySQL database
        $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
        $stmt->execute([$folder_id]);
        
        self::log($pdo, 'delete_folder', null, "Permanently Deleted Folder ID: " . $folder_id);

        $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php?route=categories';
        header("Location: $referer");
        exit;
    }

    // --- TRUE HARD DELETE LOGIC (BULK FILES) ---
    public static function bulkDelete($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");
            
            $folder_id = (int)$_POST['folder_id'];
            $file_ids = $_POST['file_ids'] ?? [];

            if (!empty($file_ids)) {
                // Prepare a placeholder string for the IN clause (e.g., "?, ?, ?")
                $placeholders = implode(',', array_fill(0, count($file_ids), '?'));
                
                // Fetch the physical paths before deleting
                $stmt = $pdo->prepare("SELECT id, title, file_path FROM files WHERE id IN ($placeholders) AND folder_id = ?");
                // Append the folder_id to the end of the file_ids array to pass all params securely
                $params = array_merge($file_ids, [$folder_id]);
                $stmt->execute($params);
                $files = $stmt->fetchAll();

                foreach ($files as $file) {
                    if (file_exists($file['file_path'])) {
                        unlink($file['file_path']); // Hard delete physical file
                    }
                    self::log($pdo, 'delete', $file['id'], "Permanently deleted via Bulk Delete: " . $file['title']);
                }

                // Hard delete from Database
                $deleteStmt = $pdo->prepare("DELETE FROM files WHERE id IN ($placeholders)");
                $deleteStmt->execute($file_ids);
            }

            header("Location: index.php?route=folder/view&id=" . $folder_id . "&msg=deleted_bulk");
            exit;
        }
    }

    // --- PROCESSORS ---
    private static function processFileContent($filePath, $ext) {
        $text = "";
        if ($ext === 'pdf') {
            try {
                if (class_exists('\Smalot\PdfParser\Parser')) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($filePath);
                    $text = $pdf->getText();
                }
            } catch (Exception $e) { }

            if (strlen(trim($text)) < 50) {
                $text = self::runTesseractOCR($filePath);
            }
        } elseif ($ext === 'docx') {
            $text = self::extractDocxText($filePath);
        }
        return $text;
    }

    private static function extractDocxText($filename) {
        $text = '';
        $zip = new ZipArchive;
        if ($zip->open($filename) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $data = $zip->getFromIndex($index);
                $zip->close();
                $doc = new DOMDocument();
                $doc->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                $text = strip_tags($doc->saveXML());
            }
        }
        return $text;
    }

    private static function runTesseractOCR($pdfPath) {
        $tempImg = sys_get_temp_dir() . '/ocr_temp_%03d.png';
        $gsCommand = "gswin64c -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r300 -dLastPage=5 -sOutputFile=\"$tempImg\" \"$pdfPath\"";
        if (DIRECTORY_SEPARATOR === '/') $gsCommand = "gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r300 -dLastPage=5 -sOutputFile=\"$tempImg\" \"$pdfPath\"";
        exec($gsCommand);

        $ocrText = "";
        $images = glob(sys_get_temp_dir() . '/ocr_temp_*.png');
        if ($images) {
            foreach ($images as $img) {
                $cmd = "tesseract \"$img\" stdout -l eng"; 
                $ocrText .= shell_exec($cmd) . " ";
                unlink($img);
            }
        }
        return $ocrText ? $ocrText : "";
    }

    private static function log($pdo, $action, $file_id, $desc) {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action_type, file_id, description, ip_address) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], $action, $file_id, $desc, $_SERVER['REMOTE_ADDR']]);
    }

    public static function moveFile($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");
            
            $file_id = (int)$_POST['file_id'];
            $new_folder_id = (int)$_POST['new_folder_id'];
            $current_folder_id = (int)$_POST['current_folder_id'];

            $stmt = $pdo->prepare("UPDATE files SET folder_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$new_folder_id, $file_id]);

            self::log($pdo, 'move', $file_id, "Moved file to folder ID: " . $new_folder_id);
            header("Location: index.php?route=folder/view&id=$current_folder_id&msg=file_moved");
            exit;
        }
    }
}
?>