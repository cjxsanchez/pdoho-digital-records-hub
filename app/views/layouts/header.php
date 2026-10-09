<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDOHO Digital Records Hub</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { 
            background-color: #f4f6f9; 
            overflow-x: hidden; /* Prevents horizontal scrollbar during animation */
        }
        
        /* Sidebar Styling & Animation */
        .sidebar { 
            height: 100vh; /* Changed from min-height to fixed height */
            position: sticky; /* Makes the sidebar stay in place */
            top: 0;
            overflow-y: auto; /* Allows scrolling inside sidebar if menu gets too long */
            background: #343a40; 
            color: #fff; 
            width: 250px;
            transition: margin 0.3s ease-in-out;
            z-index: 1040;
        }
        
        /* Clean scrollbar for sidebar */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background-color: #495057; border-radius: 4px; }

        /* Sidebar Logo Styling */
        .sidebar-logo-container {
            text-align: center;
            padding: 15px 0;
        }
        .sidebar-logo {
            max-width: 120px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
            transition: transform 0.2s;
        }
        .sidebar-logo:hover {
            transform: scale(1.05);
        }

        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block; 
            border-radius: 6px;
            margin-bottom: 2px;
            transition: all 0.2s;
        }
        .sidebar a:hover { 
            background: #495057; 
            color: #fff; 
            transform: translateX(3px); 
        }
        
        /* Desktop Toggled State */
        .sidebar.collapsed {
            margin-left: -250px;
        }

        /* Mobile Responsive State */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed; /* Overrides sticky for mobile */
                margin-left: -250px; 
                box-shadow: 2px 0 15px rgba(0,0,0,0.3);
            }
            .sidebar.active {
                margin-left: 0; 
            }
        }

        /* Top Sticky Header */
        .top-navbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background-color: #f4f6f9; /* Matches body background */
            /* The margins below negate the p-4 padding of the main container to stretch it edge-to-edge */
            margin: -1.5rem -1.5rem 1.5rem -1.5rem; 
            padding: 1.5rem 1.5rem 0.5rem 1.5rem; 
            border-bottom: 1px solid #dee2e6;
        }

        /* Hamburger Button */
        .hamburger-btn {
            background: none;
            border: none;
            font-size: 1.8rem;
            color: #343a40;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }
        .hamburger-btn:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="d-flex">
    
    <div class="sidebar p-3" id="sidebar">
        <div class="sidebar-logo-container position-relative">
            <img src="assets/dohh.png" alt="DOH Logo" class="sidebar-logo">
            
            <button class="hamburger-btn text-white d-md-none position-absolute top-0 end-0" id="closeSidebarBtn" style="font-size: 1.5rem; margin-top: -5px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <hr class="mt-2 mb-3 border-secondary">
        
        <a href="index.php?route=dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
        <a href="index.php?route=categories"><i class="bi bi-folder2-open me-2"></i> Categories</a>
        <a href="index.php?route=archives"><i class="bi bi-archive me-2"></i> Archives</a> 
        
        <hr class="border-secondary my-3">
        <h6 class="text-muted px-3 mt-3 mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">Account</h6>
        <a href="index.php?route=profile"><i class="bi bi-person me-2"></i> My Profile</a> 
        <a href="index.php?route=password"><i class="bi bi-shield-lock me-2"></i> Change Password</a> 
        <a href="index.php?route=logout" class="text-danger mt-4"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
    </div>
    
    <div class="flex-grow-1 p-4 w-100">
        
        <div class="top-navbar d-flex align-items-center">
            <button class="hamburger-btn me-3" id="toggleSidebarBtn">
                <i class="bi bi-list"></i>
            </button>
            <h5 class="mb-0 text-muted fw-normal d-none d-sm-block">PDOHO Digital Records Hub</h5>
        </div>

        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const sidebar = document.getElementById("sidebar");
                const toggleBtn = document.getElementById("toggleSidebarBtn");
                const closeBtn = document.getElementById("closeSidebarBtn");

                // Toggle Sidebar (Works for both Desktop and Mobile)
                toggleBtn.addEventListener("click", function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.toggle("active"); // Mobile slide-in
                    } else {
                        sidebar.classList.toggle("collapsed"); // Desktop slide-out
                    }
                });

                // Close Sidebar (Mobile only X button)
                closeBtn.addEventListener("click", function() {
                    sidebar.classList.remove("active");
                });
            });
        </script>