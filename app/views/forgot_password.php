<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery | Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;
        }
        .login-wrapper { width: 100%; max-width: 420px; padding: 15px; }
        .login-card {
            background: #ffffff; padding: 2.5rem 2rem; border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(255,255,255,0.8);
        }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #495057; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper .form-control {
            padding: 0.8rem 1rem 0.8rem 2.8rem; border-radius: 8px; border: 1px solid #ced4da;
            background-color: #f8f9fa; transition: all 0.2s;
        }
        .input-icon-wrapper .form-control:focus {
            background-color: #ffffff; border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .input-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #6c757d; font-size: 1.1rem; z-index: 4; }
        .btn-login { padding: 0.8rem; font-weight: 600; font-size: 1rem; border-radius: 8px; background-color: #0d6efd; border: none; }
        .btn-login:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        
        <div class="text-center mb-4">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-shield-lock-fill fs-1"></i>
            </div>
            <h3 class="fw-bold" style="color: #212529; font-size: 1.4rem;">Account Recovery</h3>
            <p class="text-muted" style="font-size: 0.9rem;">Verify your identity to reset password.</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center p-3 mb-4 rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div style="font-size: 0.9rem; font-weight: 500;"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="index.php?route=auth/forgot_password" method="POST">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <?php if ($step == 1): ?>
                <div class="mb-4">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-icon-wrapper">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required autofocus>
                        <i class="bi bi-person-fill input-icon"></i>
                    </div>
                </div>
                <button type="submit" name="verify_username" class="btn btn-primary btn-login w-100 mt-2">
                    Next <i class="bi bi-arrow-right ms-2"></i>
                </button>

            <?php elseif ($step == 2): ?>
                <div class="mb-3">
                    <label class="form-label text-primary"><i class="bi bi-question-circle-fill me-1"></i> Security Question</label>
                    <div class="p-3 bg-light border rounded-3 mb-3 fw-medium text-dark text-center">
                        "<?= htmlspecialchars($question) ?>"
                    </div>
                </div>

                <div class="mb-4">
                    <label for="answer" class="form-label">Your Answer</label>
                    <div class="input-icon-wrapper">
                        <input type="text" class="form-control" id="answer" name="answer" placeholder="Enter your answer" required autofocus>
                        <i class="bi bi-key-fill input-icon"></i>
                    </div>
                </div>

                <button type="submit" name="verify_answer" class="btn btn-primary btn-login w-100 mt-2">
                    Verify Answer <i class="bi bi-shield-check ms-2"></i>
                </button>
            <?php endif; ?>
        </form>

        <div class="text-center mt-4">
            <a href="index.php?route=login" class="text-decoration-none text-muted fw-medium" style="font-size: 0.9rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>