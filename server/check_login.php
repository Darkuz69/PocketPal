<?php
require_once("utils/utils.php");
session_start();

//session_destroy();

if(isset($_SESSION['LogInStatus']) && $_SESSION['LogInStatus']) {
  if(basename($_SERVER['PHP_SELF']) == 'index.php')redirectTo('../client/home.php');
  session_write_close();
  return;
} else {
  session_write_close();
  if(basename($_SERVER['PHP_SELF']) != 'index.php') redirectTo("../client/index.php");
  return;
}