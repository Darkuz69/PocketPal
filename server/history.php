<?php
require_once('utils/utils.php');

if(isPostRequest()) {
  $data = sanitizeUserInput($_POST);

  if(isset($data['filter'])) {
    session_start();
    $_SESSION['Month'] = $data['month'];
    session_write_close();
  }

  redirectTo('../client/history.php');
  exit();
} else {
  showMessage('Something went wrong.');
  redirectTo('../client/history.php');
  exit();
}