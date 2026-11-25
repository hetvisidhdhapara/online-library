
<?php
session_start();

// Clear student session
$_SESSION = array();

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
