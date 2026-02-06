<?php
declare(strict_types=1);

if (!isset($mysqli)) {
    require_once CONFIG_PATH . '/connection.php';
}

/**
 * Permission Helper Functions
 * Functions for checking user roles and permissions
 * 
 * Note: Requires $mysqli to be available (include connection.php before using these functions)
 */

/**
 * Check if user has a specific role
 * 
 * @param int $userId User ID
 * @param string $roleSlug Role slug (e.g., 'super_admin', 'admin')
 * @return bool
 */
function user_has_role(int $userId, string $roleSlug): bool
{
    global $mysqli;
    
    $stmt = $mysqli->prepare(
        "SELECT COUNT(*) as count
         FROM user_roles ur
         INNER JOIN roles r ON ur.role_id = r.id
         WHERE ur.user_id = ? AND r.slug = ?"
    );
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("is", $userId, $roleSlug);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return (int)($row['count'] ?? 0) > 0;
}

/**
 * Check if current logged-in user has a specific role
 * 
 * @param string $roleSlug Role slug
 * @return bool
 */
function current_user_has_role(string $roleSlug): bool
{
    if (!is_authenticated()) {
        return false;
    }
    
    $userId = (int)($_SESSION['id'] ?? 0);
    return user_has_role($userId, $roleSlug);
}

/**
 * Check if user has a specific permission
 * 
 * @param int $userId User ID
 * @param string $permissionSlug Permission slug (e.g., 'users.create', 'articles.delete')
 * @return bool
 */
function user_has_permission(int $userId, string $permissionSlug): bool
{
    global $mysqli;
    
    // Super admin has all permissions
    if (user_has_role($userId, 'super_admin')) {
        return true;
    }
    
    $stmt = $mysqli->prepare(
        "SELECT COUNT(*) as count
         FROM user_roles ur
         INNER JOIN role_permissions rp ON ur.role_id = rp.role_id
         INNER JOIN permissions p ON rp.permission_id = p.id
         WHERE ur.user_id = ? AND p.slug = ?"
    );
    
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("is", $userId, $permissionSlug);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return (int)($row['count'] ?? 0) > 0;
}

/**
 * Check if current logged-in user has a specific permission
 * 
 * @param string $permissionSlug Permission slug
 * @return bool
 */
function current_user_has_permission(string $permissionSlug): bool
{
    if (!is_authenticated()) {
        return false;
    }
    
    $userId = (int)($_SESSION['id'] ?? 0);
    return user_has_permission($userId, $permissionSlug);
}

/**
 * Check if current user is super admin
 * 
 * @return bool
 */
function is_super_admin(): bool
{
    return current_user_has_role('super_admin');
}

/**
 * Require a specific permission, redirect if not authorized
 * 
 * @param string $permissionSlug Permission slug
 * @param string $redirectUrl Optional redirect URL (default: home.php)
 * @return void
 */
function require_permission(string $permissionSlug, string $redirectUrl = 'home.php'): void
{
    if (!current_user_has_permission($permissionSlug)) {
        set_flash('error', 'You do not have permission to access this page.');
        header('Location: ' . url($redirectUrl));
        exit;
    }
}

/**
 * Require a specific role, redirect if not authorized
 * 
 * @param string $roleSlug Role slug
 * @param string $redirectUrl Optional redirect URL (default: home.php)
 * @return void
 */
function require_role(string $roleSlug, string $redirectUrl = 'home.php'): void
{
    if (!current_user_has_role($roleSlug)) {
        set_flash('error', 'You do not have permission to access this page.');
        header('Location: ' . url($redirectUrl));
        exit;
    }
}

/**
 * Get all roles for a user
 * 
 * @param int $userId User ID
 * @return array Array of role data
 */
function get_user_roles(int $userId): array
{
    global $mysqli;
    
    $stmt = $mysqli->prepare(
        "SELECT r.id, r.name, r.slug, r.description
         FROM user_roles ur
         INNER JOIN roles r ON ur.role_id = r.id
         WHERE ur.user_id = ?
         ORDER BY r.name"
    );
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
    
    $stmt->close();
    return $roles;
}

/**
 * Get all permissions for a user (through their roles)
 * 
 * @param int $userId User ID
 * @return array Array of permission slugs
 */
function get_user_permissions(int $userId): array
{
    global $mysqli;
    
    // Super admin has all permissions
    if (user_has_role($userId, 'super_admin')) {
        $stmt = $mysqli->prepare("SELECT slug FROM permissions");
        $stmt->execute();
        $result = $stmt->get_result();
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row['slug'];
        }
        $stmt->close();
        return $permissions;
    }
    
    $stmt = $mysqli->prepare(
        "SELECT DISTINCT p.slug
         FROM user_roles ur
         INNER JOIN role_permissions rp ON ur.role_id = rp.role_id
         INNER JOIN permissions p ON rp.permission_id = p.id
         WHERE ur.user_id = ?
         ORDER BY p.slug"
    );
    
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = $row['slug'];
    }
    
    $stmt->close();
    return $permissions;
}

/**
 * Load user roles and permissions into session
 * Call this after login
 * 
 * @param int $userId User ID
 * @return void
 */
function load_user_permissions_into_session(int $userId): void
{
    ensure_session();
    
    $_SESSION['roles'] = get_user_roles($userId);
    $_SESSION['permissions'] = get_user_permissions($userId);
    $_SESSION['is_super_admin'] = user_has_role($userId, 'super_admin');
    
    // Store role slugs for quick access
    $_SESSION['role_slugs'] = array_column($_SESSION['roles'], 'slug');
}
