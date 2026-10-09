# PDOHO Digital Records Hub

A centralized, enterprise-grade web application designed to digitize manual employee records and streamline file management for the Provincial Department of Health Office (PDOHO) in Catanduanes. Built to optimize storage, retrieval, and archiving, this system replaces traditional filing cabinets with an intelligent, highly searchable digital repository. 

Developed by John Carl S. Sanchez.

## 🚀 Core Features

* **Advanced File Management:** Batch upload up to 1,000 files simultaneously. Supports PDFs, Word Documents (DOCX), Excel Spreadsheets (XLS/XLSX), CSVs, and standard image formats.
* **Infinite Sub-Folder Nesting:** Organize documents dynamically with a fully nestable category and sub-folder architecture.
* **Intelligent Full-Text Search Engine:** Utilizes Tesseract OCR and Smalot PDF Parser to extract and index text from uploaded PDFs and DOCX files, allowing users to search by document titles, associated names, descriptions, or the actual text content inside the files.
* **In-Browser Document Preview:** View PDFs and images directly within the system interface via asynchronous modal loading without needing to download the files first.
* **Dual-Layer Deletion System:** Features a fail-safe "Archive" (Soft Delete) mechanism for easy restoration, alongside a secure "Permanent Delete" (Hard Delete) that wipes files from both the database and the physical hard drive.
* **Storage Optimization via Junctions:** System handles heavy file storage by cleanly mapping the web directory uploads directly to a secondary physical drive, preventing the main OS drive from reaching capacity.
* **Security & Auditing:** Role-based access control with comprehensive backend audit logging that tracks every upload, download, file move, rename, and deletion by IP address and timestamp.

## 🛠️ Tech Stack

* **Frontend:** HTML5, CSS3, JavaScript (Vanilla/AJAX), Bootstrap 5, Bootstrap Icons
* **Backend:** PHP 8 (Vanilla OOP/Procedural hybrid)
* **Database:** MySQL (InnoDB)
* **Utilities:** ZipArchive (DOCX parsing), Smalot/pdfparser, Tesseract OCR, Ghostscript

## ⚙️ Installation & Deployment Setup

### 1. Prerequisites
* **XAMPP** (Apache & MySQL)
* **Ghostscript** (For PDF rendering)
* **Tesseract OCR** (For image-to-text extraction)

### 2. Environment Configuration
Install XAMPP and configure the `php.ini` file to handle large batch uploads. Find and update the following values:
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300

Ensure Ghostscript (\bin) and Tesseract OCR installation paths are added to your Windows System Environment PATH variables.

3. Database Initialization
Start Apache and MySQL via the XAMPP Control Panel.

Open phpMyAdmin (http://localhost/phpmyadmin) and create a new database named secure_docs.

Import the provided schema by running the contents of database/dbMigration.sql in the SQL tab.

The system is pre-configured with a default administrator account:

Username: admin

Password: admin123

4. Storage Mapping (Optional but Recommended)
To prevent the main C:\ drive from filling up, route the upload directory to a secondary drive (e.g., D:\PDOHO_Uploads) using a Windows Directory Junction. Open Command Prompt as Administrator and run:
mklink /J "C:\xampp\htdocs\secure-doc-system\public\uploads" "D:\PDOHO_Uploads"

📁 Directory Structure
Plaintext
secure-doc-system/
├── app/
│   ├── config/          # Database connection strings (db.php)
│   ├── controllers/     # Core PHP logic (FileController, AuthController)
│   └── views/           # Frontend UI templates and Modals
├── database/            # SQL migration scripts
├── public/              # Web root, CSS/JS assets, and the index router
│   ├── assets/
│   ├── backups/
│   └── uploads/         # Junction link target for file storage
└── vendor/              # Composer dependencies (PDF parsers)
