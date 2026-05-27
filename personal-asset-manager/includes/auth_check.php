<?php

require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    redirect('/personal-asset-manager/pages/login.php');
}

function requireAdmin() {
    if (!isAdmin()) {
        showAlert('您没有权限访问此页面', 'danger');
        redirect('/personal-asset-manager/pages/dashboard.php');
    }
}
