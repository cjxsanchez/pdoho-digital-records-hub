<?php
class AdminController {
    
    // Show Profile Page
    public static function profile($pdo) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();
        
        require '../app/views/profile.php';
    }

    // Process Profile Update
    public static function updateProfile($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) die("CSRF validation failed");

            $name = clean($_POST['name']);
            $email = clean($_POST['email']);
            $security_question = clean($_POST['security_question']);
            $security_answer = $_POST['security_answer']; // Raw input
            
            try {
                // If user typed a new security answer, hash it and update everything
                if (!empty($security_answer)) {
                    $hashed_answer = password_hash($security_answer, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, security_question = ?, security_answer = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $security_question, $hashed_answer, $_SESSION['admin_id']]);
                } else {
                    // If answer is blank, keep the old answer but update the other fields
                    $stmt = $pdo->prepare("UPDATE admins SET name = ?, email = ?, security_question = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $security_question, $_SESSION['admin_id']]);
                }
                
                $_SESSION['success'] = "Profile updated successfully.";
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { 
                    $_SESSION['error'] = "This email address is already in use.";
                } else {
                    $_SESSION['error'] = "An error occurred while updating the profile.";
                }
            }

            header("Location: index.php?route=profile");
            exit;
        }
    }

    // Process Password Change
    public static function updatePassword($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            // Fetch current user
            $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $user = $stmt->fetch();

            if (!password_verify($current, $user['password'])) {
                $error = "Current password is incorrect.";
                require '../app/views/change_password.php';
                return;
            }

            if ($new !== $confirm) {
                $error = "New passwords do not match.";
                require '../app/views/change_password.php';
                return;
            }

            // 1. Update Password in Database
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['admin_id']]);

            // 2. SECURITY UPGRADE: Force Re-authentication
            // Unset the authentication flags, effectively logging the user out
            unset($_SESSION['admin_id']);
            unset($_SESSION['admin_user']);
            unset($_SESSION['last_activity']);

            // 3. Set a flash message so they know WHY they were logged out
            $_SESSION['success'] = "Password changed successfully. Please log in again with your new password.";
            
            // 4. Redirect straight to the login page
            header("Location: index.php?route=login");
            exit;
        }
    }
}
?>