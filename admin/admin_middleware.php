<?php
session_start();
// simple admin guard: must be logged in and email ends with admin@popmart.com
// if tried to access and is not an admin, user will be redirected to index.php
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
  header('Location: /website-popmart/index.php');
  exit;
}
if (!preg_match('/admin@popmart\.com$/i', $_SESSION['email'])) {
  header('Location: /website-popmart/index.php');
  exit;
}
?>
