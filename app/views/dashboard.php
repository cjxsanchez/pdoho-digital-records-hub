<?php include 'layouts/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* Typography & Global Layout */
    body { 
        font-family: 'Inter', sans-serif; 
        background-color: #f4f7fa; 
    }
    .dash-title { 
        font-size: 28px; 
        font-weight: 700; 
        color: #1e293b; 
        letter-spacing: -0.5px;
    }
    
    /* Modern Stat Cards */
    .stat-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.02);
        background-color: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
    }
    .stat-label {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Icon Circles */
    .icon-circle {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
    }
    .icon-primary { background-color: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .icon-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
    .icon-warning { background-color: rgba(255, 193, 7, 0.1); color: #d97706; } 

    /* Content Cards & Tables */
    .content-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
        background: #fff;
    }
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
    .table th {
        font-size: 13px;
        text-transform: uppercase;
        color: #475569;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-bottom-width: 1px;
    }
    
    /* Icon Buttons */
    .btn-icon {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
    }
    .btn-icon:hover {
        transform: scale(1.05);
    }
</style>

<div class="container-fluid pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
        <h2 class="dash-title mb-0"><i class="bi bi-grid-1x2-fill text-primary me-2 fs-4"></i> Admin Dashboard</h2>
        <span class="text-muted fw-medium d-none d-md-block">
            <i class="bi bi-person-circle me-1"></i> Welcome back, <?= htmlspecialchars($_SESSION['admin_user'] ?? 'Administrator') ?>
        </span>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-circle icon-primary me-4">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label mb-1">Total Admins</div>
                        <div class="stat-number"><?= $stats['admins'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-circle icon-success me-4">
                        <i class="bi bi-file-earmark-check-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label mb-1">Total Files</div>
                        <div class="stat-number"><?= $stats['files'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="icon-circle icon-warning me-4">
                        <i class="bi bi-folder-fill"></i>
                    </div>
                    <div>
                        <div class="stat-label mb-1">Total Folders</div>
                        <div class="stat-number"><?= $stats['folders'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4 g-4">
        <div class="col-lg-8">
            <div class="card content-card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Files Uploaded Per Month (<?= date('Y') ?>)</h6>
                </div>
                <div class="card-body">
                    <canvas id="uploadsBarChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card content-card h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-pie-chart-fill text-success me-2"></i>Files by Category</h6>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="categoryPieChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card content-card">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center">
            <i class="bi bi-clock-history text-muted me-2 fs-5"></i>
            <h6 class="mb-0 fw-bold text-dark">Recent Uploads</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Date</th>
                            <th class="py-3">Document Details</th>
                            <th class="py-3">Uploaded By</th>
                            <th class="text-center py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_files)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                    <span class="fw-medium">No recent uploads found.</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recent_files as $file): ?>
                            
                            <?php 
                                $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                                $icon_class = ($ext === 'docx') ? 'bi-file-earmark-word-fill text-primary' : 'bi-file-earmark-pdf-fill text-danger';
                            ?>

                            <tr>
                                <td class="ps-4 text-muted fw-medium" style="font-size: 0.9rem;">
                                    <?= date('M d, Y', strtotime($file['uploaded_at'])) ?>
                                </td>
                                
                                <td>
                                    <div class="d-flex align-items-center py-1">
                                        <i class="<?= $icon_class ?> me-3" style="font-size: 2rem; line-height: 1;"></i>
                                        <div>
                                            <strong class="d-block text-dark" style="font-size: 0.95rem; font-weight: 600;">
                                                <?= htmlspecialchars($file['title']) ?>
                                            </strong>
                                            <span class="text-muted" style="font-size: 0.8rem;">Associated Name: <?= htmlspecialchars($file['name']) ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        <i class="bi bi-person-badge me-1"></i> Admin
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="index.php?route=files/download&id=<?= $file['id'] ?>" target="_blank" 
                                           class="btn btn-light border btn-icon text-primary shadow-sm" 
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="View Document">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="index.php?route=files/delete&id=<?= $file['id'] ?>" 
                                           class="btn btn-light border btn-icon text-danger shadow-sm"
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Document"
                                           onclick="return confirm('Are you sure you want to delete this file?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 1055;">
    <div id="systemToast" class="toast align-items-center text-bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-medium" id="toastMessage"></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    // Receive PHP Arrays directly into Javascript variables
    const monthlyUploadData = <?= json_encode($monthly_data ?? array_fill(0, 12, 0)) ?>;
    const folderLabels = <?= json_encode($folder_labels ?? []) ?>;
    const folderCounts = <?= json_encode($folder_counts ?? []) ?>;

    document.addEventListener("DOMContentLoaded", function() {
        
        // 1. INITIALIZE TOOLTIPS
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

        // 2. CHECK URL FOR SUCCESS MESSAGES AND TRIGGER TOAST
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('msg')) {
            const msgParam = urlParams.get('msg');
            let toastText = "Action completed successfully.";
            
            if (msgParam === 'uploaded') toastText = "File(s) uploaded successfully.";
            if (msgParam === 'deleted') toastText = "File deleted successfully.";
            if (msgParam === 'folder_created') toastText = "Folder created successfully.";
            
            const toastEl = document.getElementById('systemToast');
            document.getElementById('toastMessage').innerText = toastText;
            const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
            toast.show();
            
            // Clean URL to prevent re-triggering on refresh
            window.history.replaceState({}, document.title, window.location.pathname + "?route=dashboard");
        }

        // 3. DYNAMIC CHART.JS CONFIGURATIONS

        // Bar Chart - Files Uploaded Per Month
        const barCtx = document.getElementById('uploadsBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Files Uploaded',
                    data: monthlyUploadData, // Dynamic Data from DB
                    backgroundColor: 'rgba(13, 110, 253, 0.85)',
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 }, // Only show whole numbers
                        grid: { borderDash: [4, 4] } 
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Function to generate repeating nice colors for pie chart
        const generateColors = (count) => {
            const baseColors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#fd7e14', '#20c997', '#e83e8c', '#6c757d'];
            let colors = [];
            for(let i=0; i < count; i++) {
                colors.push(baseColors[i % baseColors.length]);
            }
            return colors;
        };

        // Pie Chart - Files by Category
        const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: folderLabels.length > 0 ? folderLabels : ['No Folders Yet'],
                datasets: [{
                    data: folderCounts.length > 0 ? folderCounts : [1], 
                    backgroundColor: folderCounts.length > 0 ? generateColors(folderCounts.length) : ['#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { usePointStyle: true, padding: 20 } 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if(folderCounts.length === 0) return ' 0 files';
                                return ' ' + context.raw + ' files';
                            }
                        }
                    }
                }
            }
        });
    });
</script>

<?php include 'layouts/footer.php'; ?>