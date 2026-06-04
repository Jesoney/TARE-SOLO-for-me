<?php
session_start();
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/db.php';

$db = getDb();
$userId = $_SESSION['user_id'];

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    redirect('/personal-asset-manager/pages/assets.php');
}

$stmt = $db->prepare("DELETE FROM assets WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);

if ($stmt->rowCount() > 0) {
    showAlert('资产删除成功');
} else {
    showAlert('资产不存在或无权删除', 'danger');
}

redirect('/personal-asset-manager/pages/assets.php');
