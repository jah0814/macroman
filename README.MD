📋 MACRO ACCESS - Drug Testing Center Management System
A comprehensive web-based system for managing drug test records, generating reports, and analyzing test results for a drug testing center.

📌 Features
🔐 Authentication & User Management
Secure login/logout system

Role-based access (Admin, Staff)

Password reset requests (Staff only, approved by Admin)

User management (Admin can add/edit/delete staff)

📊 Dashboard
Interactive charts (weekly/monthly test volume)

Real-time statistics

Quick access to analytics

📝 Record Management
Add, edit, view, and archive test records

Upload client photos

Search, filter, and sort records

Pagination for large datasets

📈 Analytics
METH and THC result breakdown

Gender distribution charts

Test trends over time (day/month/year)

Date range filtering

📑 Reports
Filterable reports (by client, company, METH/THC results)

Print-friendly report templates

Export functionality

🖨️ Print Functionality
Individual record printing

Current page printing

Professional report templates with logo

🛠️ Technology Stack
Technology	Version	Purpose
PHP	8.0+	Backend logic
MySQL	10.4+	Database
HTML5	-	Structure
CSS3	-	Styling
JavaScript	ES6	Frontend interactivity
Chart.js	3.x	Charts and graphs
Font Awesome	6.x	Icons

----------------------------------------------------------------------------------------------

💻 Installation Guide
Prerequisites
XAMPP (Windows) / LAMP (Linux) / MAMP (Mac)

PHP 8.0 or higher

MySQL 10.4 or higher

Web browser (Chrome/Firefox/Edge)

Step 1: Install XAMPP
Download XAMPP from: https://www.apachefriends.org/

Install XAMPP in default location (C:\xampp)

Open XAMPP Control Panel

Start Apache and MySQL services

Step 2: Download the Project
Option A: Using Git

bash
cd C:\xampp\htdocs
git clone https://github.com/your-repo/macroaccess.git
Option B: Manual Download

Download the project ZIP file

Extract to C:\xampp\htdocs\macroaccess

Step 3: Create Database
Open browser and go to: http://localhost/phpmyadmin

Click New on the left sidebar

Enter database name: macro_access

Select utf8mb4_general_ci as collation

Click Create

Step 4: Import Database Tables
Option A: Using SQL File

Click on macro_access database

Click Import tab

Click Choose File and select macro_access.sql

Click Go

Option B: Manual SQL Execution

sql
-- Run the complete SQL query provided earlier
Step 5: Configure Database Connection
Open config/Database.php and verify settings:

php
<?php
class Database {
    private $host = "localhost";
    private $db_name = "macro_access";
    private $username = "root";
    private $password = "";
    public $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
Step 6: Create Uploads Folder
bash
cd C:\xampp\htdocs\macroaccess
mkdir uploads
Ensure the folder has write permissions.

Step 7: Configure Base URL
If your project is in a subfolder, update paths in all files. The default structure uses relative paths.

Step 8: Run the Application
Start Apache and MySQL in XAMPP

Open browser and go to: http://localhost/macroaccess

Login with default credentials:

Username	Password	Role
admin	password123	Administrator
Step 9: First Time Setup
Add Staff Users (Admin only)

Go to Account page

Click "ADD STAFF"

Fill in username, full name, position, and temporary password

Add Test Records

Go to Records page

Fill in client information

Upload photo (optional)

Add drug test results

View Analytics

Go to Analytics page

Filter by date range

View charts and statistics

Generate Reports

Go to Reports page

Use filters to narrow down results

Click "PRINT REPORT" for hard copy

🔧 Configuration Settings
Default Admin Account
Field	Value
Username	admin
Password	password123
Full Name	Administrator
Position	ADMIN
Database Settings
Parameter	Value
Host	localhost
Database	macro_access
Username	root
Password	(empty by default)
PHP Settings (php.ini)
Recommended settings:

ini
max_execution_time = 120
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
🚀 Usage Guide
For Administrators
Action	How to
Add Staff	Account page → ADD STAFF
Edit Staff	Account page → Click Edit button
Delete Staff	Account page → Click Delete button
Reset Staff Password	Account page → Password Reset Requests → Reset Password
Add Test Record	Records page → Fill form → ADD
Edit Record	Records page → Click EDIT button
Archive Record	Records page → Click ARCHIVE button
View Archive	Records page → ARCHIVE LIST button
For Staff
Action	How to
View Records	Reports page
Request Password Reset	Login page → Forgot Password
View Own Account	Account page
🐛 Troubleshooting
Error: "Database connection failed"
Solution:

Check if MySQL is running in XAMPP

Verify database name is macro_access

Check username/password in config/Database.php

Error: "Session already started"
Solution:

Remove session_start() from view files (already handled in index.php)

Error: "Table doesn't exist"
Solution:

Run the complete SQL query to create all tables

Check table name spelling (test_records vs records)

Error: "Upload failed"
Solution:

Create uploads/ folder in project root

Set folder permissions to 755 (Linux/Mac) or allow write (Windows)

Error: "Chart not showing"
Solution:

Ensure there is data in test_records table

Check browser console for JavaScript errors

Verify Chart.js CDN is accessible

Login Page Keeps Reloading
Solution:

Clear browser cache

Check session configuration in PHP

Verify header.php is not redirecting incorrectly

📱 Browser Support
Browser	Minimum Version
Google Chrome	90+
Mozilla Firefox	88+
Microsoft Edge	90+
Safari	14+
🤝 Contributing
Fork the repository

Create a feature branch

Commit your changes

Push to the branch

Open a Pull Request

📄 License
This project is for educational purposes.

👥 Authors
Macro Access Drug Testing Center

🙏 Acknowledgments
Chart.js for charts

Font Awesome for icons

XAMPP for local development environment

📞 Support
For issues or questions:

Check the Troubleshooting section

Ensure all prerequisites are installed

Verify database connection settings

🔄 Update History
Version	Date	Changes
1.0	2024	Initial release
🎯 Quick Start Commands
bash
# Clone the repository
git clone https://github.com/your-repo/macroaccess.git

# Navigate to project
cd macroaccess

# Create uploads directory
mkdir uploads

# Set permissions (Linux/Mac)
chmod 755 uploads

# Start XAMPP services
# Open browser to http://localhost/macroaccess
✅ Installation Complete!