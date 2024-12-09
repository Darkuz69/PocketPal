<?php
require_once("../server/check_login.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PocketPal</title>
  <link rel="stylesheet" href="styles/index.css">
  <link rel="icon" href="images/favicon.png">

</head>
<body>

  <div class="content-container" id="welcome">
    <div class="main-content" id="welcome-msg">
      <h1>PocketPal</h1>
      <div class="sub-content">
        The ultimate sidekick for student finances.
      </div>
    </div>
  </div>
  
  <div class="content-container" id="login">
    <div class="main-content" id="login-main">
      <h2>Login to PocketPal</h2>
      <form action="../server/index.php" method="POST">
        <div class="input-content">
          <label for="username">🤖 Username:</label><br>
          <input type="text" name="username" minlength="8" required>
        </div>
        <div class="input-content">
          <label for="password">🔑 Password:</label><br>
          <input type="password" name="password" minlength="8" required>
        </div>
        <div class="input-content">
          <input id="login-btn" type="submit" name="login" value="Log in">
        </div>
      </form>
      <div class="sub-content">
        Not registered yet?
        <button class="page-btn" onclick="togglePage('login')">Sign Up</button>
      </div>
    </div>
  </div>
  
  <div class="content-container" id="signup">
    <div class="main-content" id="signup-main">
      <h2>Register to PocketPal</h2>
      <form action="../server/index.php" method="POST">
        <div class="input-content">
          <label for="first">🤵🏻 Firstname:</label><br>
          <input type="text" name="first" required>
        </div>
        <div class="input-content">
          <label for="last">🤵🏻 Lastname:</label><br>
          <input type="text" name="last" required>
        </div>
        <div class="input-content" style="text-align:center">
          <input type="text" name="middle" style="width:49%" placeholder="Middle Initial" maxlength="1">
          <input type="text" name="suffix" style="width:49%" placeholder="Suffix">
        </div>
        <div class="input-content">
          <label for="user">🤖 Account Username:</label><br>
          <input type="text" name="user" minlength="8" required>
        </div>
        <div class="input-content">
          <label for="pass">🔑 Account Password:</label><br>
          <input type="password" name="pass" minlength="8" required>
        </div>
        <div class="input-content">
          <label for="confirm">✅ Confirm Password:</label><br>
          <input type="password" name="confirm" minlength="8" required>
        </div>
        <div class="input-content">
          <input id="signup-btn" type="submit" name="register" value="Sign up">
        </div>
      </form>
      <div class="sub-content">
        Already registered?
        <button class="page-btn" onclick="togglePage('signup')">Log in</button>
      </div>
    </div>
  </div>

  <script src="scripts/index.js"></script>

</body>
</html>