<?php
session_start();
session_unset();
session_destroy();
header('Location: /personal-asset-manager/index.php');
exit;
