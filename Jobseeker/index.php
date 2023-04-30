<?php
session_start();

# If user is logged in and is a job seeker, redirect to job seeker dashboard
if (isset($_SESSION["type"]) && $_SESSION["type"] == "jobseeker") {
  header("Location: dashboard.php");
  exit;
}

# If user is logged in and is an employer, redirect to employer dashboard
if (isset($_SESSION["type"]) && $_SESSION["type"] == "employer") {
  header("Location: ../Employer/dashboard.php");
  exit;
}

header("Location: ../index.php");
exit;
?>