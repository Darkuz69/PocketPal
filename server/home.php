<?php
require_once('utils/utils.php');

if(isPostRequest()) {
  $data = sanitizeUserInput($_POST);

  if(isset($data['logout'])) {
    session_start();
    session_destroy();
  }

  redirectTo('../client/index.php');
  exit();
}