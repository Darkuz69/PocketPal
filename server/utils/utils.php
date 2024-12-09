<?php
require_once("conn.php");

/**
 * Checks if the current request method is POST
 * 
 * @return bool True if request method is POST, false otherwise
 */
function isPostRequest() {
  // Compare server request method with POST
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
 * Checks if the current request method is GET
 * 
 * @return bool True if request method is GET, false otherwise
 */
function isGetRequest() {
  // Verify request method is set and is GET
  return (isset($_SERVER['REQUEST_METHOD'])) && ($_SERVER['REQUEST_METHOD'] == 'GET');
}

/**
 * Sanitizes user input by trimming whitespace
 * 
 * @param array $data Input array to sanitize
 * @return array Sanitized input array with whitespace removed
 */
function sanitizeUserInput(array $data) {
  // Use array_map to apply trim to all elements of the input array
  return array_map('trim', $data);
}

/**
 * Displays an alert message to the user
 * 
 * @param string $message Message to display
 */
function showMessage(string $message) {
  // Use JavaScript alert to show message
  echo '<script>alert(\''.$message.'\')</script>';
}

/**
 * Redirects the user to a specified page
 * 
 * @param string $page URL or path to redirect to
 */
function redirectTo(string $page) {
  // Use JavaScript to change window location
  echo '<script>window.location.href = \''.$page.'\'</script>';
}

/**
 * Validates that user input is not empty
 * 
 * @param string $userInput The input to validate
 * @return bool True if input is not empty, false otherwise
 */
function validateInput(string $userInput) {
  // Return false if input is completely empty
  if(empty($userInput)) return false;

  return true;
}