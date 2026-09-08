<?php
/**
 * Database Connection using PHP PDO
 * Bahir Dar Motorcycle Permit & Enforcement System
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    /**
     * Get singleton PDO connection
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                    DB_HOST,
                    DB_PORT,
                    DB_NAME,
                    DB_CHARSET
                );

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // If this is an AJAX/API request, output clean JSON error
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
                    || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
                    || (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'ajax/') !== false)) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Database connection failed: ' . $e->getMessage(),
                        'hint' => 'Ensure MySQL is running in XAMPP/Laragon and `permit_db` database is imported.'
                    ]);
                    exit;
                }

                // If regular browser request, show friendly setup prompt
                die('
                    <div style="font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
                        <h2 style="color: #dc2626; margin-top: 0;">Database Connection Error</h2>
                        <p style="color: #475569; font-size: 14px; line-height: 1.6;">
                            Could not connect to the MySQL database <strong>' . htmlspecialchars(DB_NAME) . '</strong> on <strong>' . htmlspecialchars(DB_HOST) . '</strong>.
                        </p>
                        <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: monospace; font-size: 13px; color: #b91c1c;">
                            ' . htmlspecialchars($e->getMessage()) . '
                        </div>
                        <h3 style="color: #1e293b; font-size: 15px; margin-top: 20px;">Quick Setup Instructions:</h3>
                        <ol style="color: #475569; font-size: 13px; line-height: 1.8; padding-left: 20px;">
                            <li>Start <strong>Apache</strong> and <strong>MySQL</strong> in your control panel (XAMPP / Laragon / WAMP).</li>
                            <li>Open <strong>phpMyAdmin</strong> (<a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a>).</li>
                            <li>Create a database named <code>permit_db</code> (or import <code>sql/database.sql</code>).</li>
                            <li>Verify database credentials in <code>config/config.php</code>.</li>
                            <li>Refresh this page.</li>
                        </ol>
                    </div>
                ');
            }
        }

        return self::$instance;
    }

    /**
     * Helper to run prepared query
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Helper to fetch all rows
     */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Helper to fetch single row
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    /**
     * Helper to execute insert/update/delete
     */
    public static function execute(string $sql, array $params = []): bool {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }
}
