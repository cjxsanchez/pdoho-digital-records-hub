<?php
// Simple permission checker
$dirs = [
    'uploads' => __DIR__ . '/uploads',
    'backups' => __DIR__ . '/backups',
    'backups/db' => __DIR__ . '/backups/db',
    'backups/files' => __DIR__ . '/backups/files'
];

echo "<h2>Permission Status Check</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Directory</th><th>Exists?</th><th>Writable?</th><th>Status</th></tr>";

foreach ($dirs as $name => $path) {
    // Attempt to create directory if missing
    if (!is_dir($path)) {
        @mkdir($path, 0755, true);
    }

    $exists = is_dir($path) ? "Yes" : "No";
    $writable = is_writable($path) ? "Yes" : "No";
    
    // Visual Status
    if ($exists === "Yes" && $writable === "Yes") {
        $status = "<span style='color:green; font-weight:bold;'>READY</span>";
    } else {
        $status = "<span style='color:red; font-weight:bold;'>ERROR</span>";
    }

    echo "<tr>";
    echo "<td>" . htmlspecialchars($name) . "</td>";
    echo "<td>$exists</td>";
    echo "<td>$writable</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}
echo "</table>";

echo "<p><em>Note: Delete this file after verification for security.</em></p>";
?>