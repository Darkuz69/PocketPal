<?php
$host = 'localhost';
$user = 'root';
$password = '';
$db = 'matrix';

try {
  $conn = mysqli_connect($host, $user, $password, $db);
} catch (Exception $ex) {
  die($ex->getMessage());
}