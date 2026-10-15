<?php
// logout.php — place in: C:\xampp\htdocs\portal\logout.php
session_start();
session_destroy();
header("Location: /portal/login.php");
exit();
?>
