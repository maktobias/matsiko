<?php
session_start();

class Session {
    public static function start() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }
    
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public static function getUserId() {
        return self::get('user_id');
    }
    
    public static function getUserGroup() {
        return self::get('user_group');
    }
    
    public static function hasPermission($permission) {
        $userGroup = self::get('user_group');
        if (!$userGroup) return false;
        
        $permissions = json_decode($userGroup['permissions'], true);
        return isset($permissions['all']) || isset($permissions[$permission]);
    }
    
    public static function isAdmin() {
        $userGroup = self::get('user_group');
        return $userGroup && $userGroup['name'] === 'Administrator';
    }
}

// Auto-start session
Session::start();
?>