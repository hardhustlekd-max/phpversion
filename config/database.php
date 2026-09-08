<?php
/**
 * Database Connection using PHP PDO
 * Hardened for PHP 8.3 and InfinityFree Shared Hosting
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
                $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
                $port = defined('DB_PORT') ? DB_PORT : '3306';
                $name = defined('DB_NAME') ? DB_NAME : 'permit_db';
                $user = defined('DB_USER') ? DB_USER : 'root';
                $pass = defined('DB_PASS') ? DB_PASS : '';
                $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

                $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                self::$instance = new PDO($dsn, $user, $pass, $options);
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
                        'hint' => 'Check MySQL host, dbname, user, and password in config/config.php.'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                // If regular browser request, show friendly setup & diagnostic prompt
                $dbNameSafe = htmlspecialchars(defined('DB_NAME') ? DB_NAME : 'permit_db');
                $dbHostSafe = htmlspecialchars(defined('DB_HOST') ? DB_HOST : '127.0.0.1');
                $errMsgSafe = htmlspecialchars($e->getMessage());

                die('
                    <div style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; max-width: 650px; margin: 40px auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 16px; background: #fff; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom: 15px;">
                            <span style="font-size:24px;">⚠️</span>
                            <h2 style="color: #0F172A; margin: 0; font-size: 20px; font-weight: 800;">MySQL Database Connection Notice</h2>
                        </div>
                        <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 15px 0;">
                            The application could not connect to MySQL database <code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-weight:bold;">' . $dbNameSafe . '</code> on host <code style="background:#f1f5f9; padding:2px 6px; border-radius:4px; font-weight:bold;">' . $dbHostSafe . '</code>.
                        </p>
                        <div style="background: #FEF2F2; padding: 12px 16px; border-radius: 8px; border: 1px solid #FECACA; font-family: monospace; font-size: 12px; color: #991B1B; margin-bottom: 20px; word-break: break-all;">
                            ' . $errMsgSafe . '
                        </div>
                        <h3 style="color: #0F172A; font-size: 14px; margin: 0 0 10px 0; font-weight: 700;">Easy Setup Steps for InfinityFree / Shared Hosting:</h3>
                        <ol style="color: #475569; font-size: 13px; line-height: 1.8; padding-left: 20px; margin: 0 0 20px 0;">
                            <li>Log in to your <strong>InfinityFree Control Panel (vPanel)</strong>.</li>
                            <li>Go to <strong>MySQL Databases</strong> and find your <strong>MySQL Hostname</strong> (e.g. <code>sql123.infinityfree.com</code>).</li>
                            <li>Open <strong>phpMyAdmin</strong> and import <code>sql/database.sql</code> into your database.</li>
                            <li>Edit <code>config/config.php</code> and set:
                                <pre style="background:#f8fafc; padding:10px; border-radius:6px; border:1px solid #e2e8f0; font-size:11px; margin:5px 0;">define(\'DB_HOST\', \'sqlXXX.infinityfree.com\');\ndefine(\'DB_NAME\', \'epiz_XXXXXXXX_permit_db\');\ndefine(\'DB_USER\', \'epiz_XXXXXXXX\');\ndefine(\'DB_PASS\', \'YOUR_VPANEL_PASSWORD\');</pre>
                            </li>
                            <li>Refresh this page.</li>
                        </ol>
                        <div style="text-align:right;">
                            <a href="login.php" style="display:inline-block; background:#0B1E48; color:#fff; text-decoration:none; padding:8px 16px; border-radius:8px; font-size:12px; font-weight:bold;">Go to Login Page</a>
                        </div>
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
        return self::query($sql, $params)->rowCount() > 0;
    }

    /**
     * Get last inserted ID
     */
    public static function lastInsertId(): string {
        return self::getConnection()->lastInsertId();
    }
}
