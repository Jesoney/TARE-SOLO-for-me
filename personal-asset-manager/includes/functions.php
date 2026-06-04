<?php

require_once __DIR__ . '/config.php';

function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($success, $message = '', $data = []) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function formatMoney($amount) {
    return '¥' . number_format($amount, 2);
}

function formatDate($date) {
    return date('Y-m-d', strtotime($date));
}

function formatDateTime($datetime) {
    return date('Y-m-d H:i', strtotime($datetime));
}

function getPagination($total, $page, $perPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;

    return [
        'total' => $total,
        'total_pages' => $totalPages,
        'current_page' => $page,
        'per_page' => $perPage,
        'offset' => $offset,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

function validateRequired($value, $fieldName) {
    if (empty(trim($value))) {
        return "$fieldName 不能为空";
    }
    return null;
}

function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "邮箱格式不正确";
    }
    return null;
}

function validateLength($value, $fieldName, $min, $max) {
    $len = mb_strlen($value);
    if ($len < $min || $len > $max) {
        return "$fieldName 长度应在 $min 到 $max 个字符之间";
    }
    return null;
}

function validateNumber($value, $fieldName) {
    if (!is_numeric($value) || $value < 0) {
        return "$fieldName 必须是非负数";
    }
    return null;
}

function showAlert($message, $type = 'success') {
    $_SESSION['alert'] = ['message' => $message, 'type' => $type];
}

function getAlert() {
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_ADMIN;
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role']
    ];
}
