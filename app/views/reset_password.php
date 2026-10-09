<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Document Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            display: flex; align-items: center; justify-content: center; min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0;
        }
        .login-wrapper { width: 100%; max-width: 450px; padding: 15px; }
        .login-card { background: #ffffff; padding: 2.5rem 2rem; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.08); border: 1px solid rgba(255,255,255,0.8); }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #495057; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group-text { background-color: #f8f9fa; border-color: #ced4da; color: #6c757d; border-right: none; }
        .form-control { border-left: none; padding: 0.8rem; background-color: #f8f9fa; border-color: #ced4da; box-shadow: none !important; }
        .form-control:focus { background-color: #fff; border-color: #0d6efd; }
        .input-group:focus-within .input-group-text, .input-group:focus-within .toggle-password { border-color: #0d6efd; }
        .input-group:focus-within .input-group-text:first-child { color: #0d6efd; }
        .toggle-password { background-color: transparent; border-left: none; color: #6c757d; cursor: pointer; border-color: #ced4da; }
        .btn-login { padding: 0.8rem; font-weight: 600; font-size: 1rem; border-radius: 8px; background-color: #0d6efd; border: none; transition: all 0.3s ease; }
        .btn-login:hover { background-color: #0b5ed7; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
        .progress { background-color: #e2e8f0; border-radius: 4px; height: 6px; }
        .progress-bar { transition: width 0.3s ease, background-color 0.3s ease; }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">
        
        <div class="text-center mb-4">
            <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                <i class="bi bi-key-fill fs-1"></i>
            </div>
            <h3 class="fw-bold" style="color: #212529; font-size: 1.4rem;">Create New Password</h3>
            <p class="text-muted" style="font-size: 0.9rem;">Please enter a strong password below.</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger d-flex align-items-center p-3 mb-4 rounded-3 border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-3"></i>
                <div style="font-size: 0.9rem; font-weight: 500;"><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <form action="index.php?route=auth/reset_password" method="POST" id="passwordForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <div class="mb-3">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Create new password" required autofocus>
                    <span class="input-group-text toggle-password" data-target="new_password"><i class="bi bi-eye-slash"></i></span>
                </div>
                
                <div class="progress mt-2">
                    <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%;"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small id="strengthText" class="text-muted fw-medium" style="font-size: 0.8rem;">Password strength</small>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-check2-circle"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Repeat new password" required>
                    <span class="input-group-text toggle-password" data-target="confirm_password"><i class="bi bi-eye-slash"></i></span>
                </div>
                <small id="matchText" class="fw-medium d-block mt-1" style="font-size: 0.85rem;"></small>
            </div>

            <button type="submit" id="updateBtn" class="btn btn-primary btn-login w-100 mt-2" disabled>
                Update Password <i class="bi bi-arrow-right-circle ms-2"></i>
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php?route=login" class="text-decoration-none text-danger fw-medium" style="font-size: 0.9rem;">
                Cancel Reset
            </a>
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const newPassInput = document.getElementById('new_password');
        const confPassInput = document.getElementById('confirm_password');
        const updateBtn = document.getElementById('updateBtn');
        const matchText = document.getElementById('matchText');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');

        let isStrong = false;
        let isMatched = false;

        // Toggle Password Visibility
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('bi-eye-slash', 'bi-eye');
                } else {
                    input.type = 'password';
                    icon.classList.replace('bi-eye', 'bi-eye-slash');
                }
            });
        });

        // Real-Time Password Strength logic
        newPassInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;

            if (val.length > 0) {
                if (val.length >= 8) score += 1;
                if (/[A-Z]/.test(val)) score += 1;
                if (/[0-9]/.test(val)) score += 1;
                if (/[^A-Za-z0-9]/.test(val)) score += 1;
            }

            if (val.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.innerText = 'Password strength';
                strengthText.className = 'text-muted fw-medium';
                isStrong = false;
            } else if (score <= 1) {
                strengthBar.style.width = '33%';
                strengthBar.className = 'progress-bar bg-danger';
                strengthText.innerText = 'Weak';
                strengthText.className = 'text-danger fw-bold';
                isStrong = false;
            } else if (score === 2 || score === 3) {
                strengthBar.style.width = '66%';
                strengthBar.className = 'progress-bar bg-warning';
                strengthText.innerText = 'Medium';
                strengthText.className = 'text-warning fw-bold';
                isStrong = true;
            } else if (score >= 4) {
                strengthBar.style.width = '100%';
                strengthBar.className = 'progress-bar bg-success';
                strengthText.innerText = 'Strong';
                strengthText.className = 'text-success fw-bold';
                isStrong = true;
            }

            checkSubmitState();
            checkMatch(); 
        });

        // Password Match Logic
        function checkMatch() {
            const val1 = newPassInput.value;
            const val2 = confPassInput.value;

            if (val2.length === 0) {
                matchText.innerText = '';
                isMatched = false;
            } else if (val1 === val2) {
                matchText.innerText = '✔ Passwords match';
                matchText.className = 'fw-medium d-block mt-1 text-success';
                isMatched = true;
            } else {
                matchText.innerText = '❌ Passwords do not match';
                matchText.className = 'fw-medium d-block mt-1 text-danger';
                isMatched = false;
            }
            checkSubmitState();
        }

        confPassInput.addEventListener('input', checkMatch);

        // Form Submit State
        function checkSubmitState() {
            if (isStrong && isMatched) {
                updateBtn.disabled = false;
            } else {
                updateBtn.disabled = true;
            }
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>