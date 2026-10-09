<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Login | Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 15px;
        }
        .login-card {
            background: #ffffff;
            padding: 2.5rem 2rem;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border: 1px solid rgba(255,255,255,0.8);
        }
        .brand-logo-container {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .brand-logo-container img {
            width: 110px;
            height: 110px;
            object-fit: contain;
            margin-bottom: 15px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1));
        }
        .brand-title {
            font-weight: 700;
            color: #212529;
            font-size: 1.4rem;
            margin-bottom: 4px;
        }
        .brand-subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 25px;
        }
        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #495057;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .input-icon-wrapper {
            position: relative;
        }
        .input-icon-wrapper .form-control {
            padding: 0.8rem 1rem 0.8rem 2.8rem;
            border-radius: 8px;
            border: 1px solid #ced4da;
            background-color: #f8f9fa;
            transition: all 0.2s;
        }
        .input-icon-wrapper .form-control:focus {
            background-color: #ffffff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.1rem;
            z-index: 4;
        }
        .input-icon-wrapper .form-control:focus ~ .input-icon {
            color: #0d6efd;
        }
        .btn-login {
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            background-color: #0d6efd;
            border: none;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .system-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.8rem;
            color: #adb5bd;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        
        <div class="brand-logo-container">
            <img src="assets/dohh.png" alt="Department of Health Logo">
            <h3 class="brand-title">Admin Access</h3>
            <p class="brand-subtitle">PDOHO Digital Records Hub</p>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success d-flex align-items-center p-3 mb-4 rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-3"></i>
                <div style="font-size: 0.9rem; font-weight: 500;">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center p-3 mb-4 rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div style="font-size: 0.9rem; font-weight: 500;">
                    <?= htmlspecialchars($error) ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="index.php?route=auth/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="mb-4">
                <label for="username" class="form-label">Username</label>
                <div class="input-icon-wrapper">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Enter your username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
                    <i class="bi bi-person-fill input-icon"></i>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="index.php?route=auth/forgot_password" class="text-decoration-none fw-bold text-primary" style="font-size: 0.8rem;">Forgot Password?</a>
                </div>
                <div class="input-icon-wrapper">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                    <i class="bi bi-lock-fill input-icon"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-login w-100 mt-2">
                Sign In <i class="bi bi-box-arrow-in-right ms-2"></i>
            </button>
            
        </form>

    </div>
    
    <div class="system-footer">
        <i class="bi bi-shield-lock-fill me-1"></i> Authorized Personnel Only<br>
        &copy; <?= date('Y') ?> PDOHO Digital Records Hub
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>