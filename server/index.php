<?php
// Utility functions are expected to be included from utils/utils.php
require_once("utils/utils.php");

/**
 * Adds a new user to the database
 * 
 * @param mysqli $conn Database connection object
 * @param array $data User details (first, last, middle, suffix)
 * @return bool True if user is successfully added, false otherwise
 */
function addUser(mysqli $conn, array $data) {
  // Prepare SQL statement to insert user details
  $stmt = mysqli_prepare($conn, 'INSERT INTO User(FirstName, LastName, MiddleInitial, Suffix) VALUES(?, ?, ?, ?)');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("ssss", $data['first'], $data['last'], $data['middle'], $data['suffix'])){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  $stmt->close();
  return true;
}

/**
 * Adds a new account with hashed password to the database
 * 
 * @param mysqli $conn Database connection object
 * @param array $data Account details (id, user, pass)
 * @return bool True if account is successfully added, false otherwise
 */
function addAccount(mysqli $conn, array $data) {
  // Hash the password using bcrypt for secure storage
  $encrypt = password_hash($data['pass'], PASSWORD_BCRYPT);
  
  // Prepare SQL statement to insert account details
  $stmt = mysqli_prepare($conn, 'INSERT INTO Account(UserID, Username, PasswordHash) VALUES(?, ?, ?)');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("iss", $data['id'], $data['user'], $encrypt)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  $stmt->close();
  return true;
}

/**
 * Retrieves the UserID for a specific user
 * 
 * @param mysqli $conn Database connection object
 * @param array $data User details to search for
 * @return int|bool UserID if found, false otherwise
 */
function getUserID(mysqli $conn, array $data) {
  // Prepare SQL statement to find UserID
  $stmt = mysqli_prepare($conn, 'SELECT UserID FROM User WHERE FirstName=? AND LastName=? AND MiddleInitial=? AND Suffix=?');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("ssss", $data['first'], $data['last'], $data['middle'], $data['suffix'])){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Fetch and return the UserID
  $result = $stmt->get_result();
  $stmt->close();
  return $result->fetch_assoc()['UserID'];
}

/**
 * Checks if a user already exists in the database
 * 
 * @param mysqli $conn Database connection object
 * @param array $data User details to check
 * @return bool True if user exists, false otherwise
 */
function userExists(mysqli $conn, array $data) {
  // Prepare SQL statement to check user existence
  $stmt = mysqli_prepare($conn, 'SELECT UserID FROM User WHERE FirstName=? AND LastName=? AND MiddleInitial=? AND Suffix=?');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("ssss", $data['first'], $data['last'], $data['middle'], $data['suffix'])){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Check if any rows are returned
  $result = $stmt->get_result();
  $exists = $result->num_rows > 0;
  if($exists) {
    showMessage("User with the same information already exists.");
  }

  $stmt->close();
  return $exists;
}

/**
 * Checks if a username is already taken
 * 
 * @param mysqli $conn Database connection object
 * @param string $user Username to check
 * @return bool True if username exists, false otherwise
 */
function usernameExists(mysqli $conn, string $user) {
  // Prepare SQL statement to check username existence
  $stmt = mysqli_prepare($conn, 'SELECT AccountID FROM Account WHERE Username=?');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("s", $user)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Check if any rows are returned
  $result = $stmt->get_result();
  $exists = $result->num_rows > 0;
  if($exists) {
    showMessage("User with the same information already exists.");
  }

  $stmt->close();
  return $exists;
}

/**
 * Retrieves the AccountID for a given username
 * 
 * @param mysqli $conn Database connection object
 * @param string $user Username to search for
 * @return int|bool AccountID if found, false otherwise
 */
function getAccountID(mysqli $conn, string $user) {
  // Prepare SQL statement to find AccountID
  $stmt = mysqli_prepare($conn, 'SELECT AccountID FROM Account WHERE Username=?');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("s", $user)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Check if any rows are returned
  $result = $stmt->get_result();
  $exists = $result->num_rows == 0;
  if($exists) {
    showMessage("Invalid username. Please try again.");
    return false;
  }

  $stmt->close();
  return $result->fetch_assoc()['AccountID'];
}

function getUserInfo(mysqli $conn, int $id) {
  $stmt = mysqli_prepare($conn, 'SELECT * FROM User INNER JOIN Account ON Account.UserID = User.UserID WHERE Account.AccountID = ?');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("i", $id)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  $result = $stmt->get_result();
  $userInfo = $result->fetch_assoc();
  if(!$userInfo) {
    showMessage("Empty Account.");
    return false;
  }

  return $userInfo;
}

/**
 * Fetches the password hash for a given AccountID
 * 
 * @param mysqli $conn Database connection object
 * @param int $id AccountID to retrieve hash for
 * @return string|bool Password hash if found, false otherwise
 */
function fetchHash(mysqli $conn, int $id) {
  // Prepare SQL statement to retrieve password hash
  $stmt = mysqli_prepare($conn, 'SELECT PasswordHash FROM Account WHERE AccountID=?');

  // Bind parameters to prepared statement
  if(!$stmt->bind_param("i", $id)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Fetch and return the password hash
  $result = $stmt->get_result();
  $stmt->close();
  return $result->fetch_assoc()['PasswordHash'];
}

/**
 * Compares a plain text password with a stored hash
 * 
 * @param string $hash Stored password hash
 * @param string $password Plain text password to verify
 * @return bool True if password matches hash, false otherwise
 */
function compareHash(string $hash, string $password) {
  // Use PHP's built-in password_verify to compare
  return password_verify($password, $hash);
}

/**
 * Removes a user from the database
 * 
 * @param mysqli $conn Database connection object
 * @param int $id UserID to remove
 * @return bool True if user is successfully removed, false otherwise
 */
function removeUser(mysqli $conn, int $id) {
  // Prepare SQL statement to delete user
  $stmt = mysqli_prepare($conn, 'DELETE FROM User WHERE UserID=?');
  
  // Bind parameters to prepared statement
  if(!$stmt->bind_param("i", $id)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  return true;
}

/**
 * Validates username format
 * 
 * @param string $username Username to validate
 * @return bool True if username is valid, false otherwise
 */
function validateUsername(string $username) {
  // Username must be 3-20 characters, only letters, numbers, and underscores
  return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username) === 1;
}

/**
 * Validates password complexity
 * 
 * @param string $password Password to validate
 * @return bool True if password meets complexity requirements, false otherwise
 */
function validatePassword(string $password) {
  // Password must:
  // - Be 8-50 characters long
  // - Contain at least one uppercase letter
  // - Contain at least one lowercase letter
  // - Contain at least one digit
  // - Contain at least one special character
  return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]).{8,50}$/', $password) === 1;
}

/**
 * Checks if password and confirmation match
 * 
 * @param string $password Original password
 * @param string $confirm Confirmation password
 * @return bool True if passwords match, false otherwise
 */
function checkConfirm(string $password, string $confirm) {
  // Simple string comparison
  return $password === $confirm;
}

function setLoginSession(mysqli $conn, int $id) {
  $userInfo = getUserInfo($conn, $id);
  if(!$userInfo) {
    showMessage("Something went wrong fetching the user's information.");
    return false;
  }

  $_SESSION['UserID'] = $userInfo['UserID'];
  $_SESSION['AccountID'] = $userInfo['AccountID'];
  $_SESSION['FirstName'] = $userInfo['FirstName'];
  $_SESSION['MiddleInitial'] = $userInfo['MiddleInitial'];
  $_SESSION['Suffix'] = $userInfo['Suffix'];
  $_SESSION['LastName'] = $userInfo['LastName'];
  $_SESSION['LogInStatus'] = true;
  $_SESSION['Filter'] = '*';
  $_SESSION['Month'] = date('Y-m');

  return true;
}

// Main request handling logic
if(isPostRequest()) {
  // Sanitize and filter all user inputs
  $data = sanitizeUserInput($_POST);

  // Login request handling
  if(isset($data['login'])) {
    // Prepare account login data
    $account = array(
      'username' => $data['username'] ?? '',
      'password' => $data['password'] ?? ''
    );

    // Validate each input is not empty
    foreach($account as $key => $value) {
      if(!validateInput($value)) {
        showMessage("Invalid input for $key.");
        redirectTo('../client/index.php');
        exit();
      }
    }

    // Retrieve AccountID
    $id = getAccountID($conn, $account['username']);
    if(!$id) {
      redirectTo('../client/index.php');
      exit();
    }

    // Fetch password hash
    $hash = fetchHash($conn, $id);
    if(!$hash) {
      redirectTo('../client/index.php');
      exit();
    }

    // Verify password
    if(!compareHash($hash, $account['password'])) {
      showMessage('Incorrect password. Please try again.');
      redirectTo('../client/index.php');
      exit();
    }

    session_start();
    if(!setLoginSession($conn, $id)) {
      redirectTo('../client/index.php');
      exit();
    }
    session_write_close();

    redirectTo('../client/home.php');
  // Registration request handling
  } else if(isset($data['register'])) {
    // Prepare user details
    $user = array(
      'first' => $data['first'] ?? '',
      'last' => $data['last'] ?? '',
      'middle' => $data['middle'] ?? '',
      'suffix' => $data['suffix'] ?? ''
    );

    // Validate required user fields
    foreach($user as $key => $value) {
      // Skip optional fields
      if($key == 'middle' || $key == 'suffix') {
        continue;
      }

      if(!validateInput($value)) {
        showMessage("Invalid input for $key.");
        redirectTo('../client/index.php');
        exit();
      }
    }

    // Check if user already exists
    if(userExists($conn, $user)) {
      redirectTo('../client/index.php');
      exit();
    }

    // Add user to database
    if(!addUser($conn, $user)) {
      redirectTo('../client/index.php');
      exit();
    }

    // Prepare account details
    $account = array(
      'id' => getUserID($conn, $user) ?? '',
      'user' => $data['user'] ?? '',
      'pass' => $data['pass'] ?? '',
      'confirm' => $data['confirm'] ?? ''
    );

    // Validate all account inputs
    foreach($account as $key => $value) {
      if(!validateInput($value)) {
        showMessage("Invalid input for $key");
        redirectTo('../client/index.php');
        exit();
      }
    }

    // Validate username format
    if(!validateUsername($account['user'])) {
      showMessage("Username must be 3-20 characters long and contain only letters, numbers, and underscores.");
      removeUser($conn, $account['id']);
      redirectTo('../client/index.php');
      exit();
    }

    // Validate password complexity
    if(!validatePassword($account['pass'])) {
      showMessage("Password must be 8-50 characters long and include uppercase, lowercase, number, and special character.");
      removeUser($conn, $account['id']);
      redirectTo('../client/index.php');
      exit();
    }

    // Check if username is already taken
    if(usernameExists($conn, $account['user'])) {
      removeUser($conn, $account['id']);
      showMessage('Username is already taken.');
      redirectTo('../client/index.php');
      exit();
    }

    // Confirm password matches
    if(!checkConfirm($account['pass'], $account['confirm'])) {
      removeUser($conn, $account['id']);
      showMessage('The password and confirmation password are not the same.');
      redirectTo('../client/index.php');
      exit();
    }

    // Add account to database
    if(!addAccount($conn, $account)) {
      removeUser($conn, $account['id']);
      redirectTo('../client/index.php');
      exit();
    }

    showMessage("Account created successfully! You can now log in.");
  }

  // Redirect after processing
  redirectTo("../client/index.php");
  exit();
} else {
  // Handle non-POST requests
  showMessage('Something went wrong.');
  redirectTo("../client/index.php");
  exit();
}