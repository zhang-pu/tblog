<?php
/**
 * Admin Logout
 */
session_start();
$_SESSION['admin'] = false;
session_destroy();
header('Location: login.php');
exit;
