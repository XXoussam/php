<?php
# Initialize the session
session_start();

# Unset all session variables
$_SESSION = array();

# Destroy the session
session_destroy();
if (isset($_GET['reason'])) {

    echo "<script>" . "window.location.href='./Employer/login.php?reason=1';" . "</script>";
}else{
# Redirect to login page
echo "<script>" . "window.location.href='./index.php';" . "</script>";}
exit;
