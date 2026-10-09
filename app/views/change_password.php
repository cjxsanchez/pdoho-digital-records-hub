<?php include 'layouts/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f7fa;
    }

    /* Card Styling */
    .security-card {
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        background: #ffffff;
        overflow: hidden;
    }
    
    /* Header Icon */
    .icon-circle {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Input Field Enhancements */
    .form-label {
        font-weight: 600;
        color: #334155;
        font-size: 0.95rem;
    }
    .input-group-text {
        background-color: #f8fafc;
        border-right: none;
        color: #94a3b8;
    }
    .form-control {
        border-left: none;
        box-shadow: none !important;
        transition: all 0.2s;
    }
    /* Right side icon for the eye toggle */
    .toggle-password {
        background-color: transparent;
        border-left: none;
        color: #64748b;
        cursor: pointer;
        transition: color 0.2s;
    }
    .toggle-password:hover {
        color: #0d6efd;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control,
    .input-group:focus-within .toggle-password {
        border-color: #0d6efd;
    }
    .input-group:focus-within .input-group-text:first-child {
        color: #0d6efd;
    }

    /* Strength Meter */
    .progress {
        background-color: #e2e8f0;
        border-radius: 4px;
    }
    .progress-bar {
        transition: width 0.3s ease, background-color 0.3s ease;
    }
</style>

<div class="container-fluid pb-5">
    
    <nav aria-label="breadcrumb" class="mb-4 mt-2">
        <ol class="breadcrumb" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Change Password</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-xl-5">
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" style="border-radius: 10px;">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" style="border-radius: 10px;">
                    <i class="bi bi-check-circle-fill me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <hr class="my-2 opacity-25">
                    <p class="mb-0 small"><i class="bi bi-info-circle me-1"></i> For your security, you will need to log in again on your next session.</p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card security-card">
                <div class="card-body p-4 p-md-5">
                    
                    <div class="text-center mb-4">
                        <div class="icon-circle bg-primary bg-opacity-10 text-primary mx-auto mb-3">
                            <i class="bi bi-shield-lock-fill fs-1"></i>
                        </div>
                        <h3 class="fw-bold text-dark">Change Password</h3>
                        <p class="text-muted" style="font-size: 0.95rem;">Update your password to keep your account secure. Make sure it’s strong and not used elsewhere.</p>
                    </div>

                    <hr class="border-secondary opacity-25 mb-4">

                    <form action="index.php?route=password/update" method="POST" id="passwordForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="mb-4">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password" required>
                                <span class="input-group-text toggle-password" data-target="current_password"><i class="bi bi-eye-slash"></i></span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-key"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Create new password" required>
                                <span class="input-group-text toggle-password" data-target="new_password"><i class="bi bi-eye-slash"></i></span>
                            </div>
                            
                            <div class="progress mt-2" style="height: 6px;">
                                <div id="strengthBar" class="progress-bar bg-danger" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small id="strengthText" class="text-muted fw-medium" style="font-size: 0.8rem;">Password strength</small>
                                <small id="strengthHints" class="text-muted" style="font-size: 0.75rem;"></small>
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

                        <div class="d-flex gap-3 mt-4 pt-3 border-top">
                            <a href="index.php?route=dashboard" class="btn btn-light border fw-medium px-4 w-50 text-muted">Cancel</a>
                            <button type="submit" class="btn btn-primary fw-bold px-4 w-50 shadow-sm" id="updateBtn" disabled>
                                <span id="btnText">Update Password</span>
                                <span id="btnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>

            <div class="bg-white rounded-3 p-4 mt-4 border shadow-sm">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-lightbulb text-warning me-2"></i>Security Tips</h6>
                <ul class="text-muted mb-0 ps-3" style="font-size: 0.9rem; line-height: 1.6;">
                    <li>Use a unique password at least <strong>8 characters</strong> long.</li>
                    <li>Combine uppercase letters, numbers, and symbols.</li>
                    <li>Never share your credentials with anyone.</li>
                    <li>Change your password regularly (every 90 days).</li>
                </ul>
            </div>

        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- Sidebar Active State ---
        const passLink = document.querySelector('a[href="index.php?route=password"]');
        if (passLink) {
            passLink.classList.add('bg-primary', 'text-white', 'shadow-sm');
            passLink.classList.remove('text-muted');
        }

        // --- Elements ---
        const newPassInput = document.getElementById('new_password');
        const confPassInput = document.getElementById('confirm_password');
        const updateBtn = document.getElementById('updateBtn');
        const matchText = document.getElementById('matchText');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        const strengthHints = document.getElementById('strengthHints');

        let isStrong = false;
        let isMatched = false;

        // --- Toggle Password Visibility ---
        const toggleButtons = document.querySelectorAll('.toggle-password');
        toggleButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                }
            });
        });

        // --- Real-Time Password Strength logic ---
        newPassInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;

            if (val.length > 0) {
                if (val.length >= 8) score += 1;
                if (/[A-Z]/.test(val)) score += 1;
                if (/[0-9]/.test(val)) score += 1;
                if (/[^A-Za-z0-9]/.test(val)) score += 1;
            }

            // Update UI based on score
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
                isStrong = true; // Allow medium passwords to be submitted
            } else if (score >= 4) {
                strengthBar.style.width = '100%';
                strengthBar.className = 'progress-bar bg-success';
                strengthText.innerText = 'Strong';
                strengthText.className = 'text-success fw-bold';
                isStrong = true;
            }

            checkSubmitState();
            checkMatch(); // Re-check match if confirming password was already typed
        });

        // --- Password Match Logic ---
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

        // --- Form Submit State ---
        function checkSubmitState() {
            const currentPass = document.getElementById('current_password').value;
            if (isStrong && isMatched && currentPass.length > 0) {
                updateBtn.disabled = false;
            } else {
                updateBtn.disabled = true;
            }
        }
        
        document.getElementById('current_password').addEventListener('input', checkSubmitState);

        // --- Loading State on Submit ---
        document.getElementById('passwordForm').addEventListener('submit', function() {
            updateBtn.disabled = true;
            document.getElementById('btnText').innerText = 'Updating...';
            document.getElementById('btnLoader').classList.remove('d-none');
        });
    });
</script>

<?php include 'layouts/footer.php'; ?>