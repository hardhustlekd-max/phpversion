<?php
/**
 * Role-Based Access Control (RBAC)
 * Dynamic permission matrix for the 16 system tasks across roles
 */

require_once __DIR__ . '/../config/database.php';

class RBAC {
    /**
     * Map user role key to role_id in permissions table
     */
    public static function getRoleId(string $role): string {
        return match ($role) {
            'clerk' => 'role-secretary',
            'officer' => 'role-officer',
            'admin' => 'role-manager',
            'it_specialist' => 'role-it',
            'superadmin', 'super_admin' => 'role-superadmin',
            default => str_starts_with($role, 'role-') ? $role : "role-{$role}",
        };
    }

    /**
     * Get permission state ('allow', 'view_only', 'deny') for a role and task ID
     */
    public static function getPermissionState(string $userRole, int $taskId): string {
        // Superadmin always has full access
        if ($userRole === 'superadmin' || $userRole === 'super_admin' || $userRole === 'role-superadmin') {
            return 'allow';
        }

        $roleId = self::getRoleId($userRole);

        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->prepare("SELECT permission_state FROM role_permissions WHERE role_id = ? AND task_id = ? LIMIT 1");
            $stmt->execute([$roleId, $taskId]);
            $res = $stmt->fetchColumn();

            if ($res) {
                return $res;
            }
        } catch (Exception $e) {
            // fallback
        }

        // Default fallbacks if not found in database
        $defaults = [
            'role-secretary' => [1 => 'allow', 2 => 'allow', 3 => 'allow', 4 => 'allow', 5 => 'deny', 6 => 'allow', 7 => 'allow', 8 => 'allow', 9 => 'allow', 10 => 'allow', 11 => 'deny', 12 => 'deny', 13 => 'deny', 14 => 'deny', 15 => 'deny', 16 => 'deny'],
            'role-officer' => [1 => 'deny', 2 => 'deny', 3 => 'deny', 4 => 'deny', 5 => 'allow', 6 => 'allow', 7 => 'allow', 8 => 'view_only', 9 => 'view_only', 10 => 'deny', 11 => 'deny', 12 => 'deny', 13 => 'deny', 14 => 'deny', 15 => 'deny', 16 => 'deny'],
            'role-manager' => [1 => 'allow', 2 => 'allow', 3 => 'allow', 4 => 'allow', 5 => 'allow', 6 => 'allow', 7 => 'allow', 8 => 'allow', 9 => 'allow', 10 => 'allow', 11 => 'view_only', 12 => 'view_only', 13 => 'deny', 14 => 'deny', 15 => 'allow', 16 => 'allow'],
            'role-it' => [1 => 'view_only', 2 => 'view_only', 3 => 'view_only', 4 => 'view_only', 5 => 'allow', 6 => 'view_only', 7 => 'view_only', 8 => 'view_only', 9 => 'view_only', 10 => 'view_only', 11 => 'allow', 12 => 'allow', 13 => 'allow', 14 => 'allow', 15 => 'allow', 16 => 'allow']
        ];

        return $defaults[$roleId][$taskId] ?? 'deny';
    }

    public static function isAllowed(string $userRole, int $taskId): bool {
        return self::getPermissionState($userRole, $taskId) === 'allow';
    }

    public static function isViewable(string $userRole, int $taskId): bool {
        $state = self::getPermissionState($userRole, $taskId);
        return $state === 'allow' || $state === 'view_only';
    }

    /**
     * Get complete matrix for the RBAC editor
     */
    public static function getMatrix(): array {
        try {
            $pdo = Database::getConnection();
            $rows = $pdo->query("SELECT role_id, task_id, permission_state FROM role_permissions")->fetchAll();
            $matrix = [];
            foreach ($rows as $r) {
                $matrix[$r['role_id']][(int)$r['task_id']] = $r['permission_state'];
            }
            return $matrix;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Save matrix changes
     */
    public static function saveMatrix(array $matrix): bool {
        try {
            $pdo = Database::getConnection();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("
                INSERT INTO role_permissions (role_id, task_id, permission_state)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE permission_state = VALUES(permission_state)
            ");

            foreach ($matrix as $roleId => $tasks) {
                if (is_array($tasks)) {
                    foreach ($tasks as $taskId => $state) {
                        if (in_array($state, ['allow', 'view_only', 'deny'], true)) {
                            $stmt->execute([$roleId, (int)$taskId, $state]);
                        }
                    }
                }
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return false;
        }
    }
}
