<?php
session_start();
session_unset();
session_destroy();

// Redirect to unified login page
header("Location: login.php");
exit;
?>
