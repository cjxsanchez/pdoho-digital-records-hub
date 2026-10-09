<?php
class AuthController {
    
    // Config: Max 5 failed attempts before lockout
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_TIME = 900; // 15 minutes in seconds

    public static function login($pdo) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1. CSRF Check (FIXED: Added isset check for $_SESSION to prevent undefined key warning)
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Session expired or invalid token. Please refresh the page and try again.";
                require '../app/views/login.php';
                return;
            }

            $username = clean($_POST['username']);
            $password = $_POST['password'];
            $ip = $_SERVER['REMOTE_ADDR'];

            // 2. Check Brute Force / Lockout Status
            if (self::isLockedOut($pdo, $username, $ip)) {
                $error = "Too many failed attempts. Please try again in 15 minutes.";
                require '../app/views/login.php'; 
                return;
            }

            // 3. Fetch User
            $stmt = $pdo->prepare("SELECT id, username, password, status FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // 4. Validate Credentials
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'locked') {
                    $error = "Account is administratively locked.";
                    require '../app/views/login.php';
                    return;
                }

                // 5. Success: Rotate Session ID (Fixation Protection)
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['last_activity'] = time();

                // Clear failed attempts for this IP/User
                self::clearLoginAttempts($pdo, $username, $ip);

                // ---> NEW: Update last_login timestamp in the database <---
                $updateLogin = $pdo->prepare("UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
                $updateLogin->execute([$user['id']]);

                // Log Activity
                self::logAudit($pdo, $user['id'], 'login', 'Admin logged in successfully');

                header("Location: index.php?route=dashboard");
                exit;

            } else {
                // 6. Failure: Log Attempt & Increment Counter
                self::recordFailedLogin($pdo, $username, $ip);
                $error = "Invalid credentials.";
                require '../app/views/login.php';
            }
        } else {
            // GET Request: Show Login Form
            require '../app/views/login.php';
        }
    }

    public static function logout() {
        // Destroy session data securely
        session_unset();
        session_destroy();
        header("Location: index.php?route=login");
        exit;
    }

    // --- Security Helper Methods ---

    private static function isLockedOut($pdo, $username, $ip) {
        // Count recent failed attempts
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE (username = ? OR ip_address = ?) AND attempt_time > (UNIX_TIMESTAMP() - ?)");
        $stmt->execute([$username, $ip, self::LOCKOUT_TIME]);
        $count = $stmt->fetchColumn();

        return $count >= self::MAX_LOGIN_ATTEMPTS;
    }

    private static function recordFailedLogin($pdo, $username, $ip) {
        $stmt = $pdo->prepare("INSERT INTO login_attempts (username, ip_address, attempt_time) VALUES (?, ?, UNIX_TIMESTAMP())");
        $stmt->execute([$username, $ip]);
    }

    private static function clearLoginAttempts($pdo, $username, $ip) {
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE username = ? OR ip_address = ?");
        $stmt->execute([$username, $ip]);
    }

    private static function logAudit($pdo, $user_id, $action, $desc) {
        $stmt = $pdo->prepare("INSERT INTO audit_logs (admin_id, action_type, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $action, $desc, $_SERVER['REMOTE_ADDR']]);
    }

    // --- OFFLINE PASSWORD RECOVERY SYSTEM ---
    
    public static function forgotPassword($pdo) {
        $step = $_SESSION['reset_step'] ?? 1;
        $question = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // FIXED: Added isset check for $_SESSION
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Session expired. Please refresh and try again.";
                require '../app/views/forgot_password.php';
                return;
            }

            // STEP 1: Verify Username
            if (isset($_POST['verify_username'])) {
                $username = clean($_POST['username']);
                $stmt = $pdo->prepare("SELECT security_question FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $q = $stmt->fetchColumn();

                if ($q) {
                    $_SESSION['reset_username'] = $username;
                    $_SESSION['reset_step'] = 2;
                } else {
                    $error = "User not found or no security question configured.";
                }
            }
            
            // STEP 2: Verify Security Answer
            if (isset($_POST['verify_answer'])) {
                $answer = $_POST['answer'];
                $stmt = $pdo->prepare("SELECT security_answer FROM admins WHERE username = ? LIMIT 1");
                $stmt->execute([$_SESSION['reset_username']]);
                $hashed_answer = $stmt->fetchColumn();

                if (password_verify($answer, $hashed_answer)) {
                    $_SESSION['reset_step'] = 3;
                    header("Location: index.php?route=auth/reset_password");
                    exit;
                } else {
                    $error = "Incorrect security answer.";
                    $step = 2; // Keep them on step 2
                }
            }
        }

        // Fetch question for Step 2 UI
        if ($step == 2 && isset($_SESSION['reset_username'])) {
            $stmt = $pdo->prepare("SELECT security_question FROM admins WHERE username = ?");
            $stmt->execute([$_SESSION['reset_username']]);
            $question = $stmt->fetchColumn();
        }

        require '../app/views/forgot_password.php';
    }

    public static function resetPassword($pdo) {
        // Ensure user passed the security question
        if (!isset($_SESSION['reset_step']) || $_SESSION['reset_step'] !== 3) {
            header("Location: index.php?route=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // FIXED: Added isset check for $_SESSION
            if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                $error = "Session expired. Please refresh and try again.";
                require '../app/views/reset_password.php';
                return;
            }
            
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            if ($new !== $confirm) {
                $error = "Passwords do not match.";
                require '../app/views/reset_password.php';
                return;
            }

            // Update Password
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
            $stmt->execute([$hash, $_SESSION['reset_username']]);

            // Clear reset session data
            unset($_SESSION['reset_username']);
            unset($_SESSION['reset_step']);

            $_SESSION['success'] = "Password updated successfully. You can now log in.";
            header("Location: index.php?route=login");
            exit;
        }

        require '../app/views/reset_password.php';
    }
}
?>