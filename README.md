# Enforcement Pro: Command Central (PHP / MySQL Edition)
### የባህርዳር ሞተረኞች ማህበር • የትራፊክ ቁጥጥርና ፈቃድ ማዕከል

A full-stack, standalone **PHP 8 + MySQL** application for motorcycle permit registration, roadside enforcement, QR/barcode scanning, and municipal traffic management.

---

## 🚀 Quick Setup Guide (XAMPP / WAMP / Apache / Nginx)

### 1. Place in Web Server Root
Copy the `php-version` directory into your web server document root:
- **XAMPP (Windows):** `C:\xampp\htdocs\enforcement-pro`
- **XAMPP (Linux):** `/opt/lampp/htdocs/enforcement-pro`
- **Ubuntu/Debian Apache:** `/var/www/html/enforcement-pro`

### 2. Import Database
1. Open **phpMyAdmin** (`http://localhost/phpmyadmin`) or your MySQL client.
2. Create a new database named `enforcement_db` (or import directly):
   ```sql
   CREATE DATABASE IF NOT EXISTS `enforcement_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import the file:
   ```bash
   php-version/sql/database.sql
   ```
   *(This creates all tables, seeds 16-task RBAC matrix, municipal demo accounts, system settings, and sample vehicles).*

### 3. Configure Database Connection
Open `config/config.php` and verify your MySQL credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'enforcement_db');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Ensure Folder Permissions
Ensure the upload directories in `image/` are writable:
```bash
chmod -R 775 image/
```

### 5. Access the Application
Open your browser and navigate to:
```
http://localhost/enforcement-pro/index.php
```

---

## 🔑 Default Accounts & Credentials

| Role | Badge ID | Default Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `SUPER-ADMIN-01` | `admin123` | Full access, RBAC Matrix, DB reset, audit |
| **Branch Admin** | `ADMIN-001` | `admin123` | Approvals, records, reporting, printer settings |
| **Field Officer** | `OFFICER-442` | `officer123` | Roadside QR scan, plate verify, patrol violations |
| **Clerk / Secretary** | `CLERK-209` | `clerk123` | Multi-step registration, owner documents, payments |

---

## 🌟 Core System Modules & Features

### 1. Role-Based Access Control (16 Operational Tasks)
Full permission matrix governance (`RBAC::isAllowed()`, `RBAC::isViewable()`) with configurable statuses (`allow`, `view_only`, `deny`) across Super Admin, Admin, Officer, Clerk, and Public roles.

### 2. Ethiopian Calendar (GMT+3) & Live Traditional Clock
- Seamless conversion between Ethiopian Ge'ez calendar and Gregorian dates.
- Real-time header clock with 12-month traditional daytime/nighttime calculation.
- Month-level synchronization and leap-year Pagume math.

### 3. Multi-Step Registration Engine
- **Step 1:** Driver / Owner Information (Full Name, Phone, Sub-City, Blood Group).
- **Step 2:** Motorcycle Technical Specifications (Electric / Gas < 110cc, Brand, Model, Chassis, Engine Serial, Plate Number).
- **Step 3:** Document Uploads & Captures (Driver Portrait, National ID, Driving License, Police Permit).
- **Step 4:** Review, Payment Receipt, and Instant Offline QR Generation.

### 4. Roadside QR & Barcode Enforcement Scanner
- Camera scanning interface via HTML5 video stream and native BarcodeDetector API.
- Instant plate lookup search directly from top dashboard header and scanner page.
- Comprehensive result card showing portrait, national ID, permit status, and one-click inspection logging.

### 5. Hardware & Card Printing Engine
- **CR80 Standard PVC Card** (85.6mm × 54mm) layout with dual-sided badge formatting, photo, vehicle specs, and high-density QR code.
- **A4 Heavyweight Permit Paper** layout for official municipal archives and roadside glovebox permits.

### 6. Sub-City Governance & Emergency Freeze
Allows municipal administrators to halt new permits and freeze operations in specific sub-cities (Fasilo, Belay Zeleke, Dagmawi Minilik, Gish Abay, Atse Tewodros, Tana) during security emergencies.

### 7. Revenue & Payment Receipt Tracking
Complete tracking of ETB 850 permit fees, renewal dates, and 30-day expiration alerts.

### 8. Super Admin Database Maintenance
- One-click JSON database backup download.
- Purge rejected applications.
- Master operational reset with timestamp and security audit logging.
