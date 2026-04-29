<?php
session_start();
session_unset();
session_destroy();

// Redirect back to Homepage.php
header("Location: ../Homepage.php"); 
exit();
?>