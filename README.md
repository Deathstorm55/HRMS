# Project Readme

## Overview
This project is a web-based application that includes features such as user authentication, hall management, QR code generation, and more. It utilizes PHP as the backend language and integrates various libraries and plugins for enhanced functionality.

---

## Resources Used
### Libraries and Plugins
1. **PHP QR Code Library**:
   - Location: `libs/phpqrcode`
   - Purpose: Generates QR codes.
   - License: LGPL 3.
   - Reference: [PHP QR Code Library Documentation](http://phpqrcode.sourceforge.net/)

2. **CodeMirror**:
   - Location: `plugins/codemirror`
   - Purpose: Provides a versatile text editor for code highlighting.
   - License: MIT.
   - Reference: [CodeMirror Documentation](https://codemirror.net/)

3. **OverlayScrollbars**:
   - Location: `plugins/overlayScrollbars`
   - Purpose: Customizes scrollbars for better UI/UX.
   - License: MIT.
   - Reference: [OverlayScrollbars Documentation](https://kingsora.github.io/OverlayScrollbars/)

4. **FontAwesome**:
   - Location: `plugins/fontawesome-free`
   - Purpose: Provides icons for UI elements.
   - License: SIL OFL 1.1.
   - Reference: [FontAwesome Documentation](https://fontawesome.com/)

5. **Summernote**:
   - Location: `plugins/summernote`
   - Purpose: WYSIWYG editor for rich text editing.
   - License: MIT.
   - Reference: [Summernote Documentation](https://summernote.org/)

---

## Requirements to Run
1. **Server Requirements**:
   - PHP 7.4 or higher.
   - MySQL 5.7 or higher.
   - Apache or Nginx web server.

2. **PHP Extensions**:
   - `gd` (for QR code generation).
   - `mysqli` (for database connectivity).
   - `session` (for user authentication).

3. **Database**:
   - Import the SQL file located at `database/dms_db.sql` into your MySQL database.

---

## Setup Instructions
### Step 1: Clone the Repository
```bash
git clone <repository-url>
cd <project-directory>
```
### Step 2: Database Configuration
```sql
CREATE DATABASE dms_db;
```
```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_db_user');
define('DB_PASSWORD', 'your_db_password');
define('DB_NAME', 'dms_db');
```
### Step 3: Enable GD Extension
1. **Open **php.ini**
2. **Find and uncomment:**
```ini
extension=gd
```
3. **Restart web server:**
```bash
# Apache
sudo systemctl restart apache2

# Nginx
sudo systemctl restart nginx
```
### Step 4: Import Database Schema
1. **Import dms_db.sql using phpMyAdmin**

### Step 5: Access application
```txt
http://localhost/index.php
Default Admin Credentials: admin / admin123
```
### System Architecture
```
├── api/
├── includes/
│   ├── config.php
│   └── auth.php
├── uploads/
├── index.php
├── dashboard.php
└── dms_db.sql
echo "# HRMS" >> README.md
git init
git add README.md
git commit -m "first commit"
git branch -M main
git remote add origin https://github.com/Deathstorm55/HRMS.git
git push -u origin main
```
## Key Features
    - Student Registration & Login
    - Real-time Hall Availability Tracking
    - Guest Booking Management
    - Complaint Management System
    - Staff Feedback Module
    - Automated Capacity Calculations





#   H R M S  
 