<?php
// generate_pass.php
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<strong>Password:</strong> " . $password . "<br>";
echo "<strong>Valid Hash:</strong> " . $hash . "<br><br>";
echo "Run this SQL command in phpMyAdmin:<br>";
echo "<pre>UPDATE admins SET password = '$hash' WHERE username = 'admin';</pre>";
?>