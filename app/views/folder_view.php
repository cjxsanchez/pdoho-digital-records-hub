<?php include 'layouts/header.php'; ?>

<?php 
    // --- BREADCRUMB LOGIC ---
    // Fetch the folder hierarchy to build dynamic breadcrumbs
    $breadcrumbs = [];
    $current_folder_id = $folder['id'];
    
    while ($current_folder_id) {
        $stmt_crumb = $pdo->prepare("SELECT id, folder_name, parent_id FROM folders WHERE id = ?");
        $stmt_crumb->execute([$current_folder_id]);
        $crumb_folder = $stmt_crumb->fetch();
        
        if ($crumb_folder) {
            // Add to the beginning of the array so root is first
            array_unshift($breadcrumbs, [
                'id' => $crumb_folder['id'],
                'name' => $crumb_folder['folder_name']
            ]);
            $current_folder_id = $crumb_folder['parent_id'];
        } else {
            break;
        }
    }

    $stmt_all_folders = $pdo->prepare("SELECT id, folder_name FROM folders WHERE is_archived = 0 AND id != ? ORDER BY folder_name ASC");
    $stmt_all_folders->execute([$folder['id']]);
    $available_folders = $stmt_all_folders->fetchAll();
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f7fa;
    }

    .content-card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
        background: #ffffff;
        overflow: hidden;
    }
    
    .table > :not(caption) > * > * {
        padding: 1rem 1rem;
        vertical-align: middle;
    }
    .table-striped > tbody > tr:nth-of-type(odd) > * {
        background-color: rgba(0,0,0,0.01); 
    }
    .table-hover > tbody > tr:hover > * {
        background-color: #f8fafc; 
        transition: background-color 0.2s ease;
    }

    /* Make the file rows look clickable */
    .file-row {
        cursor: pointer;
    }
    
    .sortable-header {
        font-size: 13px;
        text-transform: uppercase;
        color: #475569;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: color 0.2s;
    }
    .sortable-header:hover {
        color: #0d6efd;
    }
    .sort-icon {
        font-size: 14px;
        margin-left: 5px;
    }

    .dropdown-toggle::after { content: none; }
    .action-btn { 
        background: transparent; 
        border: none; 
        color: #64748b; 
        transition: all 0.2s; 
        padding: 5px 10px; 
        cursor: pointer;
        border-radius: 6px;
    }
    .action-btn:hover { 
        color: #1e293b; 
        background-color: #f1f5f9;
    }
    .dropdown-menu {
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .dropdown-item {
        font-size: 0.9rem;
        font-weight: 500;
        padding: 8px 16px;
    }

    .file-icon-large { font-size: 2.2rem; line-height: 1; }
    .text-purple { color: #8b5cf6 !important; }

    /* Bulk Action Bar Styling */
    #bulkActionBar {
        background-color: #e0f2fe;
        border-bottom: 1px solid #bae6fd;
        display: none; /* Hidden by default */
        padding: 10px 20px;
        align-items: center;
        justify-content: space-between;
    }
</style>

<div class="container-fluid pb-5">
    
    <nav aria-label="breadcrumb" class="mb-2 mt-2">
        <ol class="breadcrumb" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php?route=categories" class="text-decoration-none text-muted">Categories</a></li>
            
            <?php 
                // Loop through the breadcrumbs we built
                $total_crumbs = count($breadcrumbs);
                foreach ($breadcrumbs as $index => $crumb): 
                    if ($index === $total_crumbs - 1): // This is the current active folder
            ?>
                        <li class="breadcrumb-item active fw-bold text-dark" aria-current="page"><?= htmlspecialchars($crumb['name']) ?></li>
            <?php 
                    else: // These are the parent folders, make them clickable links
            ?>
                        <li class="breadcrumb-item"><a href="index.php?route=folder/view&id=<?= $crumb['id'] ?>" class="text-decoration-none text-muted"><?= htmlspecialchars($crumb['name']) ?></a></li>
            <?php 
                    endif; 
                endforeach; 
            ?>
        </ol>
    </nav>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'uploaded'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Document(s) uploaded successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'file_renamed'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Document renamed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'file_archived'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Document moved to Archives!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'file_moved'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-arrows-move me-2"></i> Document successfully moved to the new folder!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Document permanently deleted.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'deleted_bulk'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Selected documents permanently deleted.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'folder_created'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Sub-folder created successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'folder_updated'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
                <i class="bi bi-check-circle-fill me-2"></i> Folder renamed successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="mb-0 fw-bold" style="color: #1e293b; letter-spacing: -0.5px;">
            <i class="bi bi-folder2-open text-warning me-2"></i> <?= htmlspecialchars($folder['folder_name']) ?>
        </h2>
        
        <div class="d-flex gap-3 align-items-center w-auto">
            <button class="btn btn-primary shadow-sm rounded-pill px-4 fw-medium flex-shrink-0" data-bs-toggle="modal" data-bs-target="#createSubFolderModal">
                <i class="bi bi-folder-plus me-2"></i> New Sub-Folder
            </button>

            <button class="btn btn-success shadow-sm rounded-pill px-4 fw-medium flex-shrink-0" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-cloud-arrow-up-fill me-2"></i> Upload File
            </button>
        </div>
    </div>

    <?php 
        $current_sort = $_GET['sort'] ?? 'date_desc';
        
        $name_sort = ($current_sort === 'name_asc') ? 'name_desc' : 'name_asc';
        $date_sort = ($current_sort === 'date_desc') ? 'date_asc' : 'date_desc';
        $size_sort = ($current_sort === 'size_desc') ? 'size_asc' : 'size_desc';

        $icon_muted = 'bi-arrow-down-up text-muted opacity-25';
        $name_icon = ($current_sort === 'name_asc') ? 'bi-arrow-up text-primary' : (($current_sort === 'name_desc') ? 'bi-arrow-down text-primary' : $icon_muted);
        $date_icon = ($current_sort === 'date_asc') ? 'bi-arrow-up text-primary' : (($current_sort === 'date_desc') ? 'bi-arrow-down text-primary' : $icon_muted);
        $size_icon = ($current_sort === 'size_asc') ? 'bi-arrow-up text-primary' : (($current_sort === 'size_desc') ? 'bi-arrow-down text-primary' : $icon_muted);
    ?>

    <div class="card content-card">
        <form action="index.php?route=files/bulk_delete" method="POST" id="bulkActionForm">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">

            <div id="bulkActionBar">
                <span class="text-primary fw-bold"><i class="bi bi-check-square-fill me-2"></i> <span id="selectedCount">0</span> file(s) selected</span>
                <button type="submit" class="btn btn-sm btn-danger fw-bold shadow-sm" onclick="return confirm('Are you sure you want to permanently delete ALL selected files? This cannot be undone.');">
                    <i class="bi bi-trash-fill me-1"></i> Delete Selected
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle mb-0">
                    <thead class="table-light" style="border-bottom: 2px solid #e2e8f0;">
                        <tr>
                            <th class="text-center" style="width: 50px;">
                                <input class="form-check-input" type="checkbox" id="selectAllCheckbox" style="cursor: pointer;">
                            </th>
                            <th class="ps-2">
                                <a href="index.php?route=folder/view&id=<?= $folder['id'] ?>&sort=<?= $name_sort ?>" class="sortable-header">
                                    Name <i class="bi <?= $name_icon ?> sort-icon"></i>
                                </a>
                            </th>
                            <th>Owner</th>
                            <th>
                                <a href="index.php?route=folder/view&id=<?= $folder['id'] ?>&sort=<?= $date_sort ?>" class="sortable-header">
                                    Date Modified <i class="bi <?= $date_icon ?> sort-icon"></i>
                                </a>
                            </th>
                            <th>
                                <a href="index.php?route=folder/view&id=<?= $folder['id'] ?>&sort=<?= $size_sort ?>" class="sortable-header">
                                    File Size <i class="bi <?= $size_icon ?> sort-icon"></i>
                                </a>
                            </th>
                            <th class="text-center sortable-header" style="pointer-events: none;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sub_folders)): ?>
                            <?php foreach ($sub_folders as $sub): ?>
                                <tr class="file-row" data-url="index.php?route=folder/view&id=<?= $sub['id'] ?>">
                                    <td class="text-center"></td>
                                    <td class="ps-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-folder-fill text-warning me-3 file-icon-large"></i>
                                            <div>
                                                <strong class="d-block text-dark file-title" style="font-size: 0.95rem; font-weight: 600; letter-spacing: 0.3px;">
                                                    <?= htmlspecialchars($sub['folder_name']) ?>
                                                </strong>
                                                <span class="text-muted" style="font-size: 0.85rem;">Folder</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted fw-medium" style="font-size: 0.9rem;">--</td>
                                    <td class="text-muted fw-medium" style="font-size: 0.9rem;">--</td>
                                    <td class="text-muted fw-medium" style="font-size: 0.9rem;">--</td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical fs-5 text-dark"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                <li>
                                                    <a class="dropdown-item text-primary py-2" href="index.php?route=folder/view&id=<?= $sub['id'] ?>">
                                                        <i class="bi bi-folder2-open me-2"></i> Open
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-warning py-2" type="button" onclick="openRenameFolderModal(<?= $sub['id'] ?>, '<?= htmlspecialchars(addslashes($sub['folder_name'])) ?>')">
                                                        <i class="bi bi-pencil-square me-2"></i> Rename
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <a class="dropdown-item text-secondary py-2" href="index.php?route=folder/archive&id=<?= $sub['id'] ?>" onclick="return confirm('Archive this entire folder and all its contents?');">
                                                        <i class="bi bi-archive me-2"></i> Archive
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger py-2" href="index.php?route=folder/delete&id=<?= $sub['id'] ?>" onclick="return confirm('PERMANENTLY DELETE this folder and EVERYTHING inside it? This cannot be undone!');">
                                                        <i class="bi bi-trash me-2"></i> Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (empty($files) && empty($sub_folders)): ?>
                            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>This folder is empty.</td></tr>
                        <?php else: ?>
                            <?php foreach ($files as $file): ?>
                            
                            <?php 
                                $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                                $icon_class = 'bi-file-earmark-text-fill text-secondary'; 
                                $is_previewable = false;

                                if ($ext === 'pdf') {
                                    $icon_class = 'bi-file-earmark-pdf-fill text-danger';
                                    $is_previewable = true;
                                } elseif (in_array($ext, ['doc', 'docx'])) {
                                    $icon_class = 'bi-file-earmark-word-fill text-primary';
                                } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                                    $icon_class = 'bi-file-earmark-excel-fill text-success';
                                } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $icon_class = 'bi-file-earmark-image-fill text-purple';
                                    $is_previewable = true;
                                }
                                
                                $file_url = "index.php?route=files/download&id=" . $file['id'];
                                $preview_type = ($ext === 'pdf') ? 'pdf' : 'image';
                            ?>

                            <tr class="file-row" data-url="<?= $file_url ?>">
                                <td class="text-center">
                                    <input class="form-check-input row-checkbox" type="checkbox" name="file_ids[]" value="<?= $file['id'] ?>" style="cursor: pointer;">
                                </td>
                                <td class="ps-2">
                                    <div class="d-flex align-items-center">
                                        <i class="<?= $icon_class ?> me-3 file-icon-large"></i>
                                        <div>
                                            <strong class="d-block text-dark" style="font-size: 0.95rem; font-weight: 600; letter-spacing: 0.3px;">
                                                <?= htmlspecialchars($file['title']) ?>
                                            </strong>
                                            <span class="text-muted" style="font-size: 0.85rem;">Assoc. Name: <?= htmlspecialchars($file['name']) ?></span>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-muted fw-medium" style="font-size: 0.9rem;">
                                    <i class="bi bi-person-circle me-1 opacity-50"></i> <?= htmlspecialchars($file['owner_name'] ?? 'Admin') ?>
                                </td>
                                
                                <td class="text-muted fw-medium" style="font-size: 0.9rem;">
                                    <?= date('M d, Y - h:i A', strtotime($file['updated_at'])) ?>
                                </td>
                                
                                <td class="text-muted fw-medium" style="font-size: 0.9rem;">
                                    <?= round($file['file_size'] / 1024, 1) ?> KB
                                </td>
                                
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical fs-5 text-dark"></i>
                                        </button>
                                        
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                            <li>
                                                <?php if($is_previewable): ?>
                                                    <button class="dropdown-item text-primary" type="button" onclick="openPreviewModal('<?= htmlspecialchars(addslashes($file['title'])) ?>', '<?= $file_url ?>', '<?= $preview_type ?>')">
                                                        <i class="bi bi-eye me-2"></i> Preview
                                                    </button>
                                                <?php else: ?>
                                                    <a class="dropdown-item text-primary" href="<?= $file_url ?>" target="_blank">
                                                        <i class="bi bi-eye me-2"></i> View (New Tab)
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-success" href="<?= $file_url ?>&action=download" download="<?= htmlspecialchars($file['title']) ?>.<?= $ext ?>">
                                                    <i class="bi bi-download me-2"></i> Download
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-warning py-2" type="button" onclick="openEditFileModal(<?= $file['id'] ?>, '<?= htmlspecialchars(addslashes($file['title'])) ?>')">
                                                    <i class="bi bi-pencil-square me-2"></i> Rename
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item text-info py-2" type="button" onclick="openMoveFileModal(<?= $file['id'] ?>, '<?= htmlspecialchars(addslashes($file['title'])) ?>')">
                                                    <i class="bi bi-arrows-move me-2"></i> Move
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-secondary py-2" href="index.php?route=files/archive&id=<?= $file['id'] ?>&folder_id=<?= $folder['id'] ?>" onclick="return confirm('Move this file to Archives?');">
                                                    <i class="bi bi-archive me-2"></i> Archive
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger py-2" 
                                                   href="index.php?route=files/delete&id=<?= $file['id'] ?>&folder_id=<?= $folder['id'] ?>" 
                                                   onclick="return confirm('Are you sure you want to permanently delete this file?');">
                                                    <i class="bi bi-trash me-2"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="renameFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=folder/edit" method="POST">
                <div class="modal-header text-dark" style="background-color: #ffc107; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Rename Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="folder_id" id="rename_folder_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Folder Name</label>
                        <input type="text" name="new_folder_name" id="rename_folder_name" class="form-control form-control-lg bg-light" required>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="createSubFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=folder/create" method="POST">
                <div class="modal-header text-white" style="background-color: #0d6efd; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus me-2"></i>Create Sub-Folder</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="parent_id" value="<?= $folder['id'] ?>"> <div class="mb-3">
                        <label class="form-label fw-bold">Folder Name</label>
                        <input type="text" name="folder_name" class="form-control form-control-lg bg-light" placeholder="e.g., 2026 Reports" required>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-light border-bottom-0">
                <h5 class="modal-title fw-bold text-dark" id="previewTitle"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10 d-flex justify-content-center align-items-center" id="previewBody" style="height: 75vh;">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moveFileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=files/move" method="POST">
                <div class="modal-header text-white" style="background-color: #0dcaf0; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-arrows-move me-2"></i>Move File</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="file_id" id="move_file_id" value="">
                    <input type="hidden" name="current_folder_id" value="<?= $folder['id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Move "<span id="move_file_name_display" class="text-info"></span>" To:</label>
                        <select name="new_folder_id" class="form-select form-select-lg bg-light" required>
                            <option value="">-- Select Destination Folder --</option>
                            <?php foreach($available_folders as $af): ?>
                                <option value="<?= $af['id'] ?>"><?= htmlspecialchars($af['folder_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white px-4 fw-bold">Move File</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editFileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=files/rename" method="POST">
                <div class="modal-header text-dark" style="background-color: #ffc107; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Rename File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="file_id" id="edit_file_id" value="">
                    <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Document Title</label>
                        <input type="text" name="new_title" id="edit_file_name" class="form-control form-control-lg bg-light" required>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning px-4 fw-bold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header bg-success text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                <h5 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up-fill me-2"></i>Upload Documents</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" id="modalCloseBtn"></button>
            </div>
            
            <form id="ajaxUploadForm" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="folder_id" value="<?= $folder['id'] ?>"> 
                    <input type="hidden" name="multiple_upload" value="1"> 
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Choose Files</label>
                        <input type="file" id="multipleFileInput" name="files[]" class="form-control form-control-lg bg-light" accept=".pdf, .docx, .xls, .xlsx, .csv, image/*, application/pdf, application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple required>
                        <div class="form-text text-muted mt-2" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle me-1"></i> Hold <kbd>Ctrl</kbd> or <kbd>Shift</kbd> to select multiple files (Max: 1000). <br>
                            <span class="ms-3">Document titles are auto-generated from file names.</span>
                        </div>
                    </div>

                    <div id="uploadProgressContainer" class="d-none mt-4">
                        <label class="form-label fw-bold text-primary mb-1" id="uploadStatusText">Uploading files... Please wait.</label>
                        <div class="progress shadow-sm" style="height: 20px; border-radius: 10px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success fw-bold" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary fw-medium" data-bs-dismiss="modal" id="uploadCancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold shadow-sm" id="uploadSubmitBtn">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Files
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// --- DOUBLE CLICK / TAP TO OPEN FILE ---
document.addEventListener("DOMContentLoaded", function() {
    const fileRows = document.querySelectorAll('.file-row');
    
    fileRows.forEach(row => {
        row.addEventListener('dblclick', function(e) {
            if (e.target.closest('.dropdown') || e.target.closest('.form-check-input')) {
                return;
            }
            const url = this.getAttribute('data-url');
            if (url) {
                window.open(url, '_blank');
            }
        });

        let lastTap = 0;
        row.addEventListener('touchend', function(e) {
            if (e.target.closest('.dropdown') || e.target.closest('.form-check-input')) {
                return;
            }
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;
            if (tapLength < 500 && tapLength > 0) {
                const url = this.getAttribute('data-url');
                if (url) {
                    window.open(url, '_blank');
                }
                e.preventDefault();
            }
            lastTap = currentTime;
        });
    });
});

// --- BULK SELECTION LOGIC ---
document.addEventListener("DOMContentLoaded", function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountSpan = document.getElementById('selectedCount');

    function updateBulkActionBar() {
        const selectedCount = document.querySelectorAll('.row-checkbox:checked').length;
        selectedCountSpan.innerText = selectedCount;
        
        if (selectedCount > 0) {
            bulkActionBar.style.display = 'flex';
        } else {
            bulkActionBar.style.display = 'none';
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
        }
    }

    if(selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            rowCheckboxes.forEach(function(checkbox) {
                checkbox.checked = isChecked;
            });
            updateBulkActionBar();
        });
    }

    rowCheckboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateBulkActionBar();
            
            // Handle the state of the "Check All" box
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            } else if (document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length && selectAllCheckbox) {
                selectAllCheckbox.checked = true;
            }
        });
    });
});

// --- RENAME FOLDER LOGIC ---
function openRenameFolderModal(folderId, currentName) {
    document.getElementById('rename_folder_id').value = folderId;
    document.getElementById('rename_folder_name').value = currentName;
    var myModal = new bootstrap.Modal(document.getElementById('renameFolderModal'));
    myModal.show();
}

// --- MOVE FILE LOGIC ---
function openMoveFileModal(fileId, currentName) {
    document.getElementById('move_file_id').value = fileId;
    document.getElementById('move_file_name_display').innerText = currentName;
    var myModal = new bootstrap.Modal(document.getElementById('moveFileModal'));
    myModal.show();
}

// --- FILE RENAME LOGIC ---
function openEditFileModal(fileId, currentName) {
    document.getElementById('edit_file_id').value = fileId;
    document.getElementById('edit_file_name').value = currentName;
    var myModal = new bootstrap.Modal(document.getElementById('editFileModal'));
    myModal.show();
}

// --- FILE PREVIEW LOGIC ---
function openPreviewModal(title, url, type) {
    // FIXED: Using standard strings with concatenation to avoid stripped backticks
    document.getElementById('previewTitle').innerHTML = '<i class="bi bi-file-earmark-text me-2 text-primary"></i> ' + title;
    const body = document.getElementById('previewBody');
    
    body.innerHTML = '<div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"><span class="visually-hidden">Loading...</span></div>';
    
    var previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    previewModal.show();

    setTimeout(() => {
        if(type === 'pdf') {
            // FIXED: Using standard strings
            body.innerHTML = '<iframe src="' + url + '" width="100%" height="100%" style="border:none; border-radius: 0 0 12px 12px;"></iframe>';
        } else if (type === 'image') {
            // FIXED: Using standard strings
            body.innerHTML = '<img src="' + url + '" style="max-width: 100%; max-height: 100%; object-fit: contain; padding: 20px;">';
        }
    }, 400);
}

document.getElementById('previewModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('previewBody').innerHTML = '';
});

// --- AJAX BULK UPLOAD LOGIC ---
document.getElementById('ajaxUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var form = this;
    var fileInput = document.getElementById('multipleFileInput');

    if (fileInput.files.length === 0) {
        alert("Please select at least one file.");
        return;
    }
    if (fileInput.files.length > 1000) {
        alert("Upload failed: Maximum of 1000 files allowed per upload.");
        return;
    }

    var formData = new FormData(form);
    var xhr = new XMLHttpRequest();

    var submitBtn = document.getElementById('uploadSubmitBtn');
    var cancelBtn = document.getElementById('uploadCancelBtn');
    var closeBtn = document.getElementById('modalCloseBtn');
    var progressContainer = document.getElementById('uploadProgressContainer');
    var progressBar = document.getElementById('uploadProgressBar');
    var statusText = document.getElementById('uploadStatusText');

    submitBtn.disabled = true;
    cancelBtn.disabled = true;
    closeBtn.disabled = true;
    fileInput.disabled = true;
    
    progressBar.style.width = "0%";
    progressBar.innerHTML = "0%";
    statusText.innerHTML = "Uploading files... Please wait.";
    statusText.className = "form-label fw-bold text-primary mb-1";
    progressBar.classList.add('progress-bar-animated');
    progressContainer.classList.remove('d-none');

    xhr.upload.addEventListener("progress", function(evt) {
        if (evt.lengthComputable) {
            var percentComplete = Math.round((evt.loaded / evt.total) * 100);
            progressBar.style.width = percentComplete + "%";
            progressBar.innerHTML = percentComplete + "%";
            progressBar.setAttribute("aria-valuenow", percentComplete);

            if (percentComplete === 100) {
                statusText.innerHTML = "<i class='bi bi-gear-wide-connected spin me-2'></i>Processing files... This may take a while.";
                progressBar.classList.remove('progress-bar-animated');
            }
        }
    }, false);

    xhr.onload = function() {
        if (xhr.status === 200) {
            var responseText = xhr.responseText.trim();
            
            if(responseText.startsWith('Upload failed:')) {
                alert(responseText);
                resetUploadUI();
            } else {
                statusText.innerHTML = "<i class='bi bi-check-circle-fill me-1'></i> Upload Complete! Refreshing...";
                statusText.className = "form-label fw-bold text-success mb-1";
                var currentSort = new URLSearchParams(window.location.search).get('sort') || 'date_desc';
                window.location.href = 'index.php?route=folder/view&id=<?= $folder['id'] ?>&sort=' + currentSort + '&msg=uploaded';
            }
        } else {
            alert("An error occurred during upload. Please try again.");
            resetUploadUI();
        }
    };

    xhr.onerror = function() {
        alert("Network error occurred. The upload limit may have been drastically exceeded. Check server configuration.");
        resetUploadUI();
    };

    function resetUploadUI() {
        submitBtn.disabled = false;
        cancelBtn.disabled = false;
        closeBtn.disabled = false;
        fileInput.disabled = false;
        progressContainer.classList.add('d-none');
        fileInput.value = ""; 
    }

    xhr.open("POST", "index.php?route=files/upload", true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send(formData);
});
</script>

<?php include 'layouts/footer.php'; ?>