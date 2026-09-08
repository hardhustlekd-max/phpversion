# Migration Plan: React + Express + Firestore to PHP + AJAX + MySQL

**Project**: Simplified Municipal Permit & BMA Authority System (Bahir Dar City Transport Enforcement)  
**Target Architecture**: Standalone PHP 8.1+ with AJAX (Fetch API) and MySQL (PDO)  
**Deployment Target**: Standard XAMPP, Laragon, WAMP, or Apache/Nginx + PHP + MySQL (`http://localhost/project/`)

---

## 1. Current Architecture Overview

```text
React 19 + Vite 6 (Frontend SPA)
       ↓
Express 4 (Node.js Server / API layer)
       ↓
Firebase Firestore (Cloud NoSQL Database) + Firebase Admin SDK
       ↓
Browser LocalStorage / IndexedDB (Offline cache & sync)
```

### Key Components & Workflows in Original Application:
1. **Frontend**:
   - Single Page Application built in React 19 with Vite.
   - Rich bilingual Amharic/English UI with Ethiopian Calendar (GMT+3) synchronization.
   - Dual theme support (Light and Dark).
   - Material design iconography and custom Ethiopian font (`Abyssinica SIL`).
   - Role-Based Access Control (RBAC) with 4 system roles:
     - `clerk`: Register vehicles, adjust submissions, generate permits.
     - `officer`: Roadside scanner, verification logs, unregistered vehicle citations.
     - `admin`: Operational dashboard, municipal approvals, print orders, records management.
     - `superadmin`: User administration, sub-city freezing, RBAC permissions matrix, system backup & reset, audit logs.
   - Camera and document uploads for portrait photos, national ID (front & back), driving licenses, police permits, and payment receipts.
   - QR code generation and live camera QR scanning for roadside verification.
   - A4 and PVC printable card layouts.

2. **Backend & Data**:
   - Node.js Express server (`server.ts`) hosting `/api/*` endpoints.
   - Firebase Admin SDK connecting to Firestore database (`permit`).
   - Dual-mode data persistence: Client-side Firebase SDK + Express API fallback + IndexedDB offline cache.

---

## 2. Target Architecture: Standalone PHP + AJAX + MySQL

```text
Apache / Nginx Web Server
       ↓
index.php (Single Page / Modular View Controller)
       ↓
AJAX / Modern JavaScript Fetch API (app.js)
       ↓
PHP RESTful AJAX Endpoints (ajax/*.php)
       ↓
PHP PDO Prepared Statements (config/database.php)
       ↓
MySQL Relational Database (permit_db)
       ↓
Local File Storage (image/ directory for documents & media)
```

---

## 3. Structural Mapping

### 3.1 React Components to PHP Pages & Includes

| React Component | Target PHP Page / Component | Target JavaScript Module |
|---|---|---|
| `src/App.tsx` | `php-version/index.php` | `assets/js/app.js` |
| `src/components/LoginPage.tsx` | `php-version/pages/login.php` (or login modal in `index.php`) | `app.js` (auth handler) |
| `src/components/HomePage.tsx` | `php-version/index.php` (main layout shell) | `app.js` (page router & state store) |
| Header & Topbar | `php-version/includes/header.php` + `includes/navbar.php` | `app.js` (clock, notifications, profile) |
| Footer | `php-version/includes/footer.php` | `app.js` |
| `src/components/MunicipalDashboardOverview.tsx` | `php-version/pages/dashboard.php` | `app.js` (renderDashboard) |
| `src/components/MultiStepRegistrationForm.tsx` | `php-version/pages/forms.php` | `app.js` (initMultiStepForm) |
| `src/components/TablesPage.tsx` | `php-version/pages/tables.php` | `app.js` (initTablesPage, search, filters) |
| `src/components/TodaySubmissionsPage.tsx` | `php-version/pages/today_submissions.php` | `app.js` (initTodaySubmissions) |
| `src/components/SharedScannerModal.tsx` | `php-version/pages/scan.php` + Scanner Modal | `assets/js/scanner.js` |
| `src/components/OfficerVerificationHistory.tsx`| `php-version/pages/inspection_report.php` | `app.js` (renderInspectionReport) |
| `src/components/UnregisteredVehicleForm.tsx` | `php-version/pages/report_unregistered.php` | `app.js` (initUnregisteredForm) |
| `src/components/UnregisteredReportsList.tsx` | `php-version/pages/unregistered_list.php` | `app.js` (renderUnregisteredList) |
| `src/components/PaymentReceiptsPage.tsx` | `php-version/pages/payment_receipts.php` | `app.js` (renderPaymentReceipts) |
| `src/components/SuperAdminInterface.tsx` | `php-version/pages/superadmin.php` | `app.js` (renderSuperAdmin) |
| `src/components/RolePermissionManagement.tsx` | Included in `php-version/pages/superadmin.php` | `app.js` (renderPermissionMatrix) |
| `src/components/SettingsPage.tsx` | `php-version/pages/settings.php` | `app.js` (initSettingsPage) |
| `src/components/A4PermitPaper.tsx` | Printable Modal in `php-version/includes/modals.php` | `app.js` (printPermit) |
| `src/components/QRCodeCard.tsx` | QR Card Modal in `php-version/includes/modals.php` | `assets/js/qrcode.min.js` |

---

### 3.2 Express Route to PHP AJAX Endpoint Mapping

| Express API Endpoint | HTTP Method | PHP AJAX Endpoint | Description |
|---|---|---|---|
| `/api/health` | GET | `ajax/sync.php?action=health` | Server & DB health probe |
| `/api/auth/login` | POST | `ajax/auth.php?action=login` | Verify credentials, create PHP session |
| `/api/auth/users` | GET | `ajax/users.php?action=list` | List system users |
| `/api/auth/users` | POST | `ajax/users.php?action=create` | Add system user |
| `/api/auth/users/update` | POST | `ajax/users.php?action=update` | Update system user fields |
| `/api/auth/change-password` | POST | `ajax/auth.php?action=change_password` | Change user password |
| `/api/auth/users/:id` | DELETE | `ajax/users.php?action=delete` | Delete user |
| `/api/sync` | GET | `ajax/sync.php?action=all` | Fetch all initial collections in one call |
| `/api/registrations` | POST | `ajax/registrations.php?action=save` | Upsert motorcycle registration |
| `/api/registrations/status` | POST | `ajax/registrations.php?action=update_status` | Approve, reject, or update permit status |
| `/api/officers` | POST | `ajax/officers.php?action=save` | Save officer assignment |
| `/api/officers/update` | POST | `ajax/officers.php?action=update` | Update officer details |
| `/api/print-orders` | POST | `ajax/print_orders.php?action=create` | Create batch print order |
| `/api/print-orders/status` | POST | `ajax/print_orders.php?action=update_status` | Update batch print order status |
| `/api/verification-logs` | POST | `ajax/verifications.php?action=save` | Record roadside scan & verification |
| `/api/settings` | POST | `ajax/settings.php?action=save` | Update system settings |
| `/api/reset-database` | POST | `ajax/reset.php?action=factory_reset` | Wipe database and restore defaults |
| `/api/reset-data` | POST | `ajax/reset.php?action=clear_data` | Clear transaction data |
| *New: Upload Endpoint* | POST | `ajax/upload.php` | Handle document and portrait image uploads |

---

### 3.3 Database Model Transformation (Firestore to MySQL)

| Firestore Collection | MySQL Table | Primary Key | Key Foreign Keys / Indexes |
|---|---|---|---|
| `users` | `users` | `id` (VARCHAR 64) | `badge_id` (UNIQUE), `email` (INDEX), `role` |
| `registrations` | `registrations` | `id` (VARCHAR 64) | `plate_number` (INDEX), `engine_serial` (INDEX), `status`, `sub_city` |
| `officers` | `officers` | `id` (VARCHAR 64) | `badge_id` (INDEX), `sub_city`, `status` |
| `print_orders` | `print_orders` | `id` (VARCHAR 64) | `order_date`, `status` |
| `verifications` | `verifications` | `id` (VARCHAR 64) | `plate_number` (INDEX), `registration_id`, `verification_status` |
| `unregistered_reports`| `unregistered_reports` | `id` (VARCHAR 64) | `officer_badge_id`, `status`, `sub_city` |
| `payment_receipts` | `payment_receipts` | `id` (VARCHAR 64) | `receipt_number` (INDEX), `owner_registration_id` |
| `settings` | `system_settings` | `setting_key` (VARCHAR 64) | `id` = 'global_config' |
| `audit_logs` | `audit_logs` | `id` (VARCHAR 64) | `actor_badge_id`, `timestamp` (INDEX), `severity` |
| `role_permissions` | `role_permissions` | `role_id` + `task_id` | `role_id`, `task_id` (PRIMARY KEY) |

---

### 3.4 Dependency Replacement Matrix

| Original Dependency | Purpose in React App | PHP / JS Replacement | Action |
|---|---|---|---|
| `react` & `react-dom` | UI Rendering Engine | Modular PHP templates + Vanilla JS | Removed |
| `vite` | Dev Server & Bundler | Native Apache / Nginx web server | Removed |
| `express` | HTTP API Routing | PHP scripts in `ajax/*.php` | Replaced |
| `firebase` & `firebase-admin` | Database & Storage SDK | MySQL 8 / MariaDB + PHP PDO | Replaced |
| `motion` | UI Transitions | CSS keyframe transitions & animations | Replaced |
| `qrcode.react` | QR Code Generation | Standalone client-side `qrcode.min.js` | Replaced |
| `@yudiel/react-qr-scanner` / `jsqr` | Camera QR scanning | HTML5 Barcode/QR Detector + jsQR canvas | Replaced |
| `idb` | IndexedDB Storage | LocalStorage fallback + MySQL persistence | Replaced |
| `lucide-react` | Icons | SVG / Material Icons inline & font glyphs | Replaced |

---

## 4. Security & Architecture Guidelines

1. **Prepared Statements**: All database operations must strictly use PDO with parameterized queries (`$stmt->prepare()` and `$stmt->execute()`).
2. **Session Security**: Session tokens are maintained server-side with `session_start()`, validating user credentials and permissions before processing any mutating AJAX request.
3. **Password Security**: Passwords are securely hashed using `password_hash($pass, PASSWORD_BCRYPT)` and checked with `password_verify()`.
4. **File Upload Security**:
   - Files are stored in `image/permits/`, `image/receipts/`, `image/evidence/`, and `image/avatars/`.
   - Extensions strictly validated to `.jpg`, `.jpeg`, `.png`, `.webp`.
   - MIME types checked via PHP `finfo_file`.
   - Unique filenames generated using `bin2hex(random_bytes(16))` to prevent path traversal and file execution.
5. **Bilingual & Localization**:
   - Native Ethiopian Calendar conversion (GMT+3) fully supported in JavaScript and PHP helper functions.
   - Amharic and English translation dictionary maintained for seamless toggling without page reloading.
