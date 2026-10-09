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

    /* Modern Search Bar */
    .search-wrapper {
        position: relative;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        overflow: hidden;
    }
    .search-icon {
        position: absolute;
        left: 1.2rem;
        color: #94a3b8;
        font-size: 1.2rem;
        pointer-events: none;
    }
    .search-input {
        border: none;
        box-shadow: none !important;
        padding: 1rem 1rem 1rem 3rem;
        font-size: 1rem;
        background: transparent;
        width: 100%;
    }
    .search-input::placeholder {
        color: #94a3b8;
    }

    /* Folder Grid Layout */
    .folder-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    /* Folder Card Styling */
    .folder-card {
        transition: all 0.25s ease-in-out;
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        position: relative; 
    }
    .folder-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
        border-color: rgba(13, 110, 253, 0.3);
        cursor: pointer;
    }
    .folder-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        padding: 1.5rem;
    }
    .folder-icon { 
        font-size: 3rem; 
        color: #ffc107; 
        filter: drop-shadow(0 2px 4px rgba(255, 193, 7, 0.3));
        display: block;
        margin-bottom: 15px;
    }
    
    /* 3-Dot Dropdown Menu */
    .folder-actions {
        position: absolute;
        top: 15px;
        right: 15px;
        z-index: 10;
    }
    .folder-actions .dropdown-toggle::after { 
        content: none; 
    }
    .folder-actions .action-btn {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 1.3rem; 
        padding: 2px 8px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .folder-actions .action-btn:hover { 
        color: #1e293b; 
        background-color: #f1f5f9;
    }

    /* Action Dropdown Tweaks */
    .dropdown-menu {
        border-radius: 10px;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .dropdown-item {
        font-size: 0.9rem;
        font-weight: 500;
    }
</style>

<div class="container-fluid pb-5">
    
    <nav aria-label="breadcrumb" class="mb-2 mt-2">
        <ol class="breadcrumb" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="index.php?route=dashboard" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">Categories</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <h2 class="mb-0 fw-bold" style="color: #1e293b; letter-spacing: -0.5px;">
            <i class="bi bi-collection text-primary me-2"></i> Document Categories
        </h2>
        <button class="btn btn-primary shadow-sm rounded-pill px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#createFolderModal">
            <i class="bi bi-plus-lg me-1"></i> Create Folder
        </button>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <?php if($_GET['msg'] == 'folder_deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Folder and all its contents deleted successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'folder_updated'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Folder renamed successfully.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'folder_archived'): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Folder moved to Archives.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif($_GET['msg'] == 'error_system_folder'): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                <i class="bi bi-shield-lock-fill me-2"></i> <strong>Action Denied:</strong> "Recovered Files" is a protected system folder.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form action="index.php" method="GET" class="mb-4">
        <input type="hidden" name="route" value="categories">
        <div class="search-wrapper">
            <i class="bi bi-search search-icon"></i>
            <input type="text" name="q" id="searchInput" class="form-control search-input" placeholder="Search folders, files, names, or year..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit" class="d-none">Search</button>
        </div>
    </form>

    <?php if (isset($is_search) && $is_search): ?>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold text-dark">Search Results for "<span class="text-primary"><?= htmlspecialchars($_GET['q']) ?></span>"</h5>
            <a href="index.php?route=categories" class="btn btn-sm btn-outline-secondary rounded-pill px-3">&larr; Back to Folders</a>
        </div>

        <?php if(empty($files) && empty($folders)): ?>
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-0">
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-search fs-1 d-block mb-3 text-secondary opacity-50"></i>
                        No folders or documents found matching your criteria.
                    </div>
                </div>
            </div>
        <?php else: ?>
            
            <?php if(!empty($folders)): ?>
                <h6 class="text-muted mt-4 mb-3 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="bi bi-folder2-open me-2"></i>Matched Folders</h6>
                
                <div class="folder-grid mb-4" id="folderGrid">
                    <?php foreach ($folders as $folder): ?>
                    <div class="folder-card-wrapper">
                        <div class="folder-card h-100">
                            
                            <?php if($folder['folder_name'] !== 'Recovered Files'): ?>
                            <div class="folder-actions dropdown">
                                <button class="action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <a class="dropdown-item py-2" href="index.php?route=folder/view&id=<?= $folder['id'] ?>">
                                            <i class="bi bi-folder2-open me-2 text-success"></i> Open
                                        </a>
                                    </li>
                                    <li>
                                        <button class="dropdown-item py-2" type="button" onclick="openEditFolderModal(<?= $folder['id'] ?>, '<?= htmlspecialchars(addslashes($folder['folder_name'])) ?>')">
                                            <i class="bi bi-pencil-square me-2 text-warning"></i> Rename 
                                        </button>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-2 text-secondary" href="index.php?route=folder/archive&id=<?= $folder['id'] ?>" onclick="return confirm('Move this folder to Archives?');">
                                            <i class="bi bi-archive me-2"></i> Archive
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger py-2" href="index.php?route=folder/delete&id=<?= $folder['id'] ?>" onclick="return confirm('WARNING: Are you sure you want to delete this folder?\n\nALL FILES inside this folder will also be PERMANENTLY DELETED.');">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <?php endif; ?>

                            <a href="index.php?route=folder/view&id=<?= $folder['id'] ?>" class="folder-card-link">
                                <i class="bi bi-folder-fill folder-icon"></i>
                                <h6 class="folder-title fw-bold text-dark text-truncate mb-3" title="<?= htmlspecialchars($folder['folder_name']) ?>" style="font-size: 1.1rem;">
                                    <?= htmlspecialchars($folder['folder_name']) ?>
                                    <?php if($folder['folder_name'] === 'Recovered Files'): ?>
                                        <span class="badge bg-secondary ms-1" style="font-size: 0.7rem; vertical-align: middle;">System</span>
                                    <?php endif; ?>
                                </h6>
                                <div class="d-flex flex-column text-muted" style="font-size: 0.85rem; gap: 6px;">
                                    <span><i class="bi bi-file-earmark-text me-2 opacity-75"></i><?= $folder['file_count'] ?> Files</span>
                                    <span><i class="bi bi-calendar3 me-2 opacity-75"></i>Created: <?= isset($folder['created_at']) ? date('M Y', strtotime($folder['created_at'])) : 'Recently' ?></span>
                                    <span><i class="bi bi-person me-2 opacity-75"></i>Owner: Admin</span>
                                </div>
                            </a>

                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if(!empty($files)): ?>
                <h6 class="text-muted mt-4 mb-3 fw-bold text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;"><i class="bi bi-file-earmark-text me-2"></i>Matched Documents</h6>
                <div class="card shadow-sm border-0" style="border-radius: 12px;">
                    <div class="card-body p-0">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-4 py-3">Document Details</th>
                                    <th class="py-3">Date Modified</th> 
                                    <th class="py-3">Size</th>
                                    <th class="text-center py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($files as $file): ?>
                                    
                                    <?php 
                                        $ext = strtolower(pathinfo($file['file_path'], PATHINFO_EXTENSION));
                                        $icon_class = ($ext === 'docx') ? 'bi-file-earmark-word-fill text-primary' : 'bi-file-earmark-pdf-fill text-danger';
                                    ?>

                                    <tr class="file-row">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <i class="<?= $icon_class ?> me-3" style="font-size: 2.2rem; line-height: 1;"></i>
                                                <div>
                                                    <strong class="d-block text-dark" style="font-size: 1rem; font-weight: 600; letter-spacing: 0.5px;">
                                                        <?= htmlspecialchars($file['title']) ?>
                                                    </strong>
                                                    <span class="text-muted" style="font-size: 0.85rem;">Associated Name: <?= htmlspecialchars($file['name']) ?></span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-muted fw-medium" style="font-size: 0.9rem;"><?= date('M d, Y - h:i A', strtotime($file['updated_at'])) ?></td>
                                        
                                        <td><?= round($file['file_size'] / 1024, 1) ?> KB</td>
                                        <td class="text-center">
                                            <div class="btn-group shadow-sm">
                                                <a href="index.php?route=files/download&id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank" title="View Document">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="index.php?route=files/download&id=<?= $file['id'] ?>&action=download" class="btn btn-sm btn-outline-success" download="<?= htmlspecialchars($file['title']) ?>.<?= $ext ?>" title="Download Document">
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                                <a href="index.php?route=files/delete&id=<?= $file['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this file?');" title="Delete Document">
                                                    <i class="bi bi-trash"></i> Delete
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    <?php else: ?>

        <?php if(empty($folders)): ?>
            <div class="alert alert-light border shadow-sm d-flex align-items-center p-4 mt-3" style="border-radius: 12px;">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-4 text-primary">
                    <i class="bi bi-folder-x fs-2"></i>
                </div>
                <div>
                    <h5 class="mb-1 fw-bold text-dark">No folders created yet</h5>
                    <p class="mb-0 text-muted">Click the "Create Folder" button above to get started organizing your documents.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="folder-grid" id="folderGrid">
                <?php foreach ($folders as $folder): ?>
                <div class="folder-card-wrapper">
                    <div class="folder-card h-100">
                        
                        <?php if($folder['folder_name'] !== 'Recovered Files'): ?>
                        <div class="folder-actions dropdown">
                            <button class="action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item py-2" href="index.php?route=folder/view&id=<?= $folder['id'] ?>">
                                        <i class="bi bi-folder2-open me-2 text-success"></i> Open
                                    </a>
                                </li>
                                <li>
                                    <button class="dropdown-item py-2" type="button" onclick="openEditFolderModal(<?= $folder['id'] ?>, '<?= htmlspecialchars(addslashes($folder['folder_name'])) ?>')">
                                        <i class="bi bi-pencil-square me-2 text-warning"></i> Rename 
                                    </button>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 text-secondary" href="index.php?route=folder/archive&id=<?= $folder['id'] ?>" onclick="return confirm('Move this folder to Archives?');">
                                        <i class="bi bi-archive me-2"></i> Archive
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger py-2" href="index.php?route=folder/delete&id=<?= $folder['id'] ?>" onclick="return confirm('WARNING: Are you sure you want to delete this folder?\n\nALL FILES inside this folder will also be PERMANENTLY DELETED.');">
                                        <i class="bi bi-trash me-2"></i> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <a href="index.php?route=folder/view&id=<?= $folder['id'] ?>" class="folder-card-link">
                            <i class="bi bi-folder-fill folder-icon"></i>
                            <h6 class="folder-title fw-bold text-dark text-truncate mb-3" title="<?= htmlspecialchars($folder['folder_name']) ?>" style="font-size: 1.1rem;">
                                <?= htmlspecialchars($folder['folder_name']) ?>
                                <?php if($folder['folder_name'] === 'Recovered Files'): ?>
                                    <span class="badge bg-secondary ms-1" style="font-size: 0.7rem; vertical-align: middle;">System</span>
                                <?php endif; ?>
                            </h6>
                            <div class="d-flex flex-column text-muted" style="font-size: 0.85rem; gap: 6px;">
                                <span><i class="bi bi-file-earmark-text me-2 opacity-75"></i><?= $folder['file_count'] ?> Files</span>
                                <span><i class="bi bi-calendar3 me-2 opacity-75"></i>Created: <?= isset($folder['created_at']) ? date('M Y', strtotime($folder['created_at'])) : 'Recently' ?></span>
                                <span><i class="bi bi-person me-2 opacity-75"></i>Owner: Admin</span>
                            </div>
                        </a>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=folder/create" method="POST">
                <div class="modal-header bg-primary text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus me-2"></i>Create New Folder</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Folder Name</label>
                        <input type="text" name="folder_name" class="form-control form-control-lg bg-light" required>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editFolderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <form action="index.php?route=folder/edit" method="POST">
                <div class="modal-header text-dark" style="background-color: #ffc107; border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Rename Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="folder_id" id="edit_folder_id" value="">
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Folder Name</label>
                        <input type="text" name="new_folder_name" id="edit_folder_name" class="form-control form-control-lg bg-light" required>
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

<script>
    function openEditFolderModal(folderId, currentName) {
        document.getElementById('edit_folder_id').value = folderId;
        document.getElementById('edit_folder_name').value = currentName;
        var myModal = new bootstrap.Modal(document.getElementById('editFolderModal'));
        myModal.show();
    }

    document.addEventListener("DOMContentLoaded", function() {
        const searchInput = document.getElementById('searchInput');
        if(searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                const filter = e.target.value.toLowerCase();
                
                const folderCards = document.querySelectorAll('.folder-card-wrapper');
                folderCards.forEach(card => {
                    const text = card.innerText.toLowerCase();
                    if (text.includes(filter)) {
                        card.style.display = "";
                    } else {
                        card.style.display = "none";
                    }
                });

                const fileRows = document.querySelectorAll('.file-row');
                fileRows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    if (text.includes(filter)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        }
    });
</script>

<?php include 'layouts/footer.php'; ?>