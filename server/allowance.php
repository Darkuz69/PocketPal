<?php
require_once('utils/utils.php');

function deleteAllowance(mysqli $conn, int $id) {
  $stmt = $conn->prepare('DELETE FROM Allowance WHERE AllowanceID=?');

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

  $stmt->close();
  return true;
}

function editAllowance(mysqli $conn, array $data) {
  session_start();
  $stmt = $conn->prepare('UPDATE Allowance SET SourceID=?, Description=?, AllowanceAmount=? WHERE AllowanceID=?');

  // Bind parameters to prepared statement
  if(!$stmt->bind_param("isdi", $data['category'], $data['desc'], $data['amount'], $data['id'])){
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

  session_abort();
  $stmt->close();
  return true;
}

function addAllowance(mysqli $conn, array $data) {
  session_start();
  $stmt = $conn->prepare('INSERT INTO Allowance(AccountID, SourceID, Description, AllowanceAmount) VALUES(?, ?, ?, ?)');

  // Bind parameters to prepared statement
  if(!$stmt->bind_param("iisd", $_SESSION['AccountID'], $data['category'], $data['desc'], $data['amount'])){
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

  session_abort();
  $stmt->close();
  return true;
}

function getTotalAllowanceExcept(mysqli $conn, int $id) {
  session_start();
  $stmt = $conn->prepare('SELECT SUM(AllowanceAmount) AS Total FROM Allowance WHERE AccountID=? AND DATE_FORMAT(AllowanceDate, "%Y-%m")=? AND NOT AllowanceID=?');
  if(!$stmt->bind_param("isi", $_SESSION['AccountID'], $_SESSION['Month'], $id)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }
  
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }
  
  session_abort();
  $result = $stmt->get_result();
  $stmt->close();
  return $result->fetch_assoc()['Total'];
}

function getTotalExpense(mysqli $conn) {
  session_start();
  $stmt = $conn->prepare('SELECT SUM(ExpenseAmount) AS Total FROM Expense WHERE AccountID=? AND DATE_FORMAT(ExpenseDate, "%Y-%m")=?');
  if(!$stmt->bind_param("is", $_SESSION['AccountID'], $_SESSION['Month'])){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }
  
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }
  
  session_abort();
  $result = $stmt->get_result();
  $stmt->close();
  return $result->fetch_assoc()['Total'];
}

function displayCategory(mysqli $conn) {
  $query =  "SELECT Allowance.AllowanceID, Allowance.Description, Allowance.AllowanceAmount, Allowance.AllowanceDate, Allowance.SourceID, AllowanceSource.SourceName FROM Allowance INNER JOIN AllowanceSource ON Allowance.SourceID = AllowanceSource.SourceID WHERE AccountID=? AND DATE_FORMAT(AllowanceDate, '%Y-%m')=?";
  if($_SESSION['Filter'] != '*') $query .= " AND AllowanceSource.SourceID=?";
  $query .= " ORDER BY Allowance.AllowanceDate DESC";

  $stmt = $conn->prepare($query);
  if($_SESSION['Filter'] != '*') {
    if(!$stmt->bind_param("isi", $_SESSION['AccountID'], $_SESSION['Month'], $_SESSION['Filter'])){
      showMessage("Binding parameters failed: " . $stmt->error);
      $stmt->close();
      return false;
    }
  } else {
    if(!$stmt->bind_param("is", $_SESSION['AccountID'], $_SESSION['Month'])){
      showMessage("Binding parameters failed: " . $stmt->error);
      $stmt->close();
      return false;
    }
  }

  // Execute the statement
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()) {
    $date = new DateTime($row['AllowanceDate']);

    echo '<tr>';
    echo '  <td>'.$date->format('F j, Y @ h:i A').'</td>';
    echo '  <td>'.$row['SourceName'].'</td>';
    echo '  <td>'.$row['Description'].'</td>';
    echo '  <td>₱ '.$row['AllowanceAmount'].'</td>';
    echo '  <td>
              <button id="edit" onclick="editOverlay('.$row['AllowanceID'].', \''.$row['Description'].'\', '.$row['SourceID'].', '.$row['AllowanceAmount'].')">Edit</button>
              <button id="delete" onclick="deleteOverlay('.$row['AllowanceID'].')">Delete</button>
            </td>';
    echo '</tr>';
  }

  $_SESSION['Filter'] = '*';
  $_SESSION['Month'] = date('Y-m');
  session_write_close();

  return true;
}

if(isPostRequest()) {
  $data = sanitizeUserInput($_POST);

  if(isset($data['filter'])) {
    session_start();
    if($data['filter'] != '*') $_SESSION['Filter'] = (int)$data['filter'];
    else $_SESSION['Filter'] = $data['filter'];

    displayCategory($conn);
    session_write_close();
  } else if(isset($data['month'])) {
    session_start();
    $_SESSION['Month'] = $data['month'];

    displayCategory($conn);
    session_write_close();
  } else if(isset($data['confirm'])) {
    $allowance = array(
      'category' => $data['category'] ?? '',
      'desc' => $data['desc'] ?? '',
      'amount' => $data['amount'] ?? ''
    );

    // Validate each input is not empty
    foreach($allowance as $key => $value) {
      if(!validateInput((string)$value)) {
        showMessage("Invalid input for $key.");
        redirectTo('../client/allowance.php');
        exit();
      }
    }

    if(!addAllowance($conn, $allowance)) {
      showMessage('There was an error recording the allowance. Please try again.');
      redirectTo('../client/allowance.php');
      exit();
    }

    showMessage('Allowance was successfully recorded!!');
  } else if(isset($data['edit'])) {
    $allowance = array(
      'id' => $data['id'] ?? '',
      'category' => $data['category'] ?? '',
      'desc' => $data['desc'] ?? '',
      'amount' => $data['amount'] ?? ''
    );

    // Validate each input is not empty
    foreach($allowance as $key => $value) {
      if(!validateInput((string)$value)) {
        showMessage("Invalid input for $key.");
        redirectTo('../client/allowance.php');
        exit();
      }
    }

    
    $totalAllowanceExpectOne = getTotalAllowanceExcept($conn, $allowance['id']);
    if(!$totalAllowanceExpectOne) {
      $totalAllowanceExpectOne = 0;
    }

    $totalAllowance = $totalAllowanceExpectOne + $allowance['amount'];

    $totalExpense = getTotalExpense($conn);
    if(!$totalExpense) {
      $totalExpense = 0;
    }

    if($totalAllowance < $totalExpense) {
      showMessage('The change cannot compensate to the current total expenses. Please try again.');
      redirectTo('../client/allowance.php');
      exit();
    }

    if(!editAllowance($conn, $allowance)) {
      showMessage('There was an error editing the allowance. Please try again.');
      redirectTo('../client/allowance.php');
      exit();
    }

    showMessage('Allowance was successfully edited!!');
  } else if(isset($data['delete'])) {

    $totalAllowance = getTotalAllowanceExcept($conn, $data['id']);
    if(!$totalAllowance) {
      $totalAllowance = 0;
    }

    $totalExpense = getTotalExpense($conn);
    if(!$totalExpense) {
      $totalExpense = 0;
    }
    
    if($totalAllowance < $totalExpense) {
      showMessage('The change cannot compensate to the current total expenses. Please try again.');
      redirectTo('../client/allowance.php');
      exit();
    }

    if(!deleteAllowance($conn, $data['id'])) {
      showMessage('There was an error deleting the expense. Please try again.');
      redirectTo('../client/allowance.php');
      exit();
    }

    showMessage('Allowance was successfully removed!!');
  }

  redirectTo('../client/allowance.php');
  exit();
} else {
  // Handle non-POST requests
  showMessage('Something went wrong.');
  redirectTo("../client/allowance.php");
  exit();
}