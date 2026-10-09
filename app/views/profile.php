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

    /* Page Layout & Container */
    .profile-card {
        border-radius: 12px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.03);
        background: #ffffff;
        overflow: hidden;
    }
    
    /* Header Section with Avatar */
    .profile-header-bg {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        padding: 40px 20px 60px 20px;
        text-align: center;
        color: white;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #ffffff;
        color: #0d6efd;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        border: 4px solid rgba(255,255,255,0.3);
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        margin-bottom: 15px;
    }
    
    /* Form Sections */
    .section-title {
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin-bottom: 1rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.5rem;
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
    .form-control, .form-select {
        border-left: none;
        box-shadow: none !important;
        transition: all 0.2s;
    }
    .input-group:focus-within .input-group-text,
    .input-group:focus-within .form-control,
    .input-group:focus-within .toggle-password {
        border-color: #0d6efd;
    }
    .input-group:focus-within .input-group-text:first-child {
        color: #0d6efd;
    }
    .form-control:disabled, .form-control[readonly] {
        background-color: #f1f5f9;
        cursor: not-allowed;
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

    /* Security Section */
    .security-box {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
    }
</style>

<div class="container-fluid pb-5">
    
    <nav aria-label="breadcrumb" class="mb-4 mt-2">
        <ol class="breadcrumb" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">My Profile</li>
        </ol>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card profile-card">
                
                <div class="profile-header-bg">
                    <div class="profile-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <h3 class="mb-1 fw-bold"><?= htmlspecialchars($user['name'] ?? 'Administrator') ?></h3>
                    <p class="mb-0 text-white-50"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user['email'] ?? 'No email provided') ?></p>
                </div>

                <div class="card-body p-5" style="margin-top: -30px; background: white; border-radius: 20px 20px 0 0;">
                    
                    <form action="index.php?route=profile/update" method="POST" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="mb-5">
                            <h6 class="section-title"><i class="bi bi-person-badge me-2"></i>Account Information</h6>
                            
                            <div class="mb-4">
                                <label class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-at"></i></span>
                                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" readonly>
                                </div>
                                <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i> Usernames cannot be changed once created.</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h6 class="section-title"><i class="bi bi-card-text me-2"></i>Personal Information</h6>

                            <div class="mb-4">
                                <label class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h6 class="section-title"><i class="bi bi-key me-2"></i>Account Recovery (Offline)</h6>
                            
                            <div class="mb-4">
                                <label class="form-label">Security Question</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-question-circle"></i></span>
                                    <input type="text" name="security_question" class="form-control" 
                                           placeholder="e.g. What city were you born in?" 
                                           value="<?= htmlspecialchars($user['security_question'] ?? '') ?>" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Security Answer</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" name="security_answer" id="security_answer" class="form-control" 
                                           placeholder="<?= !empty($user['security_answer']) ? 'Leave blank to keep current answer' : 'Enter your answer' ?>" 
                                           <?= !empty($user['security_answer']) ? '' : 'required' ?>>
                                    <span class="input-group-text toggle-password" data-target="security_answer"><i class="bi bi-eye-slash"></i></span>
                                </div>
                                <div class="form-text text-muted mt-2"><i class="bi bi-info-circle me-1"></i> This answer will be used if you forget your password. It is case-sensitive.</div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <h6 class="section-title"><i class="bi bi-shield-check me-2"></i>Account Security</h6>
                            <div class="security-box d-flex justify-content-between align-items-center flex-wrap gap-3">
                                <div>
                                    <strong class="d-block text-dark"><i class="bi bi-clock-history text-muted me-2"></i>Last Login</strong>
                                    <span class="text-muted" style="font-size: 0.9rem;">
                                        <?= !empty($user['last_login']) ? date('F j, Y - g:i A', strtotime($user['last_login'])) : 'Current Session' ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <strong class="d-block text-dark"><i class="bi bi-person-gear text-muted me-2"></i>Role</strong>
                                    <span class="badge bg-primary px-3 rounded-pill">System Administrator</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 justify-content-end border-top pt-4">
                            <a href="index.php?route=dashboard" class="btn btn-light border fw-medium px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary fw-bold px-5 shadow-sm" id="saveBtn">
                                <span id="btnText"><i class="bi bi-save me-2"></i>Save Changes</span>
                                <span id="btnLoader" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const profileLink = document.querySelector('a[href="index.php?route=profile"]');
        if (profileLink) {
            profileLink.classList.add('bg-primary', 'text-white', 'shadow-sm');
            profileLink.classList.remove('text-muted');
        }

        // Toggle Password Visibility
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

        // Loading state on form submit
        const profileForm = document.getElementById('profileForm');
        const saveBtn = document.getElementById('saveBtn');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');

        profileForm.addEventListener('submit', function() {
            saveBtn.disabled = true;
            btnText.innerText = 'Saving...';
            btnLoader.classList.remove('d-none');
        });
    });
</script>

<?php include 'layouts/footer.php'; ?>