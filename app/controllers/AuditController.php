<?php
class AuditController {

    public static function index($pdo) {
        // 1. Capture Filter Inputs
        $filter_action = $_GET['action_type'] ?? '';
        $filter_date_start = $_GET['date_start'] ?? '';
        $filter_date_end = $_GET['date_end'] ?? '';
        
        // 2. Build Query Dynamically
        $sql = "SELECT a.*, u.username 
                FROM audit_logs a 
                LEFT JOIN admins u ON a.admin_id = u.id 
                WHERE 1=1";
        
        $params = [];

        if ($filter_action) {
            $sql .= " AND a.action_type = ?";
            $params[] = $filter_action;
        }
        if ($filter_date_start) {
            $sql .= " AND DATE(a.created_at) >= ?";
            $params[] = $filter_date_start;
        }
        if ($filter_date_end) {
            $sql .= " AND DATE(a.created_at) <= ?";
            $params[] = $filter_date_end;
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT 100"; // Pagination recommended for real prod

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // 3. Load View
        require '../app/views/audit_logs.php';
    }

    public static function export($pdo) {
        // Same filtering logic as index, but without LIMIT
        $filter_action = $_GET['action_type'] ?? '';
        
        $sql = "SELECT a.id, a.created_at, u.username, a.action_type, a.ip_address, a.description 
                FROM audit_logs a 
                LEFT JOIN admins u ON a.admin_id = u.id 
                WHERE 1=1";
        
        $params = [];
        if ($filter_action) {
            $sql .= " AND a.action_type = ?";
            $params[] = $filter_action;
        }
        $sql .= " ORDER BY a.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        // 4. Generate CSV Stream
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_report_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // CSV Headers
        fputcsv($output, ['Log ID', 'Timestamp', 'Admin User', 'Action Type', 'IP Address', 'Description']);

        // Stream Rows (Memory Efficient)
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }
}
?>