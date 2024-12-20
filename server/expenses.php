<?php
require_once('utils/utils.php');

function deleteExpense(mysqli $conn, int $id) {
  $stmt = $conn->prepare('DELETE FROM Expense WHERE ExpenseID=?');

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

function editExpense(mysqli $conn, array $data) {
  $stmt = $conn->prepare('UPDATE Expense SET ExpenseCategoryID=?, Description=?, ExpenseAmount=? WHERE ExpenseID=?');

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

  $stmt->close();
  return true;
}

function addExpense(mysqli $conn, array $data) {
  session_start();
  $stmt = $conn->prepare('INSERT INTO Expense(AccountID, ExpenseCategoryID, Description, ExpenseAmount) VALUES(?, ?, ?, ?)');

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

function getTotalAllowance(mysqli $conn) {
  session_start();
  $stmt = $conn->prepare('SELECT SUM(AllowanceAmount) AS Total FROM Allowance WHERE AccountID=? AND DATE_FORMAT(AllowanceDate, "%Y-%m")=?');
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

function getLastAmount(mysqli $conn, int $id) {
  $stmt = $conn->prepare('SELECT ExpenseAmount FROM Expense WHERE ExpenseID=?');
  if(!$stmt->bind_param("i", $id)){
    showMessage("Binding parameters failed: " . $stmt->error);
    $stmt->close();
    return false;
  }
  
  if(!$stmt->execute()) {
    showMessage("Execution failed: " . $stmt->error);
    $stmt->close();
    return false;
  }

  $result = $stmt->get_result();
  $stmt->close();
  return $result->fetch_assoc()['ExpenseAmount'];
}

function displayCategory(mysqli $conn) {
  $query =  "SELECT Expense.ExpenseID, Expense.Description, Expense.ExpenseAmount, Expense.ExpenseDate, Expense.ExpenseCategoryID, ExpenseCategory.CategoryName FROM Expense INNER JOIN ExpenseCategory ON Expense.ExpenseCategoryID = ExpenseCategory.ExpenseCategoryID WHERE AccountID=? AND DATE_FORMAT(ExpenseDate, '%Y-%m')=?";
  if($_SESSION['Filter'] != '*') $query .= " AND Expense.ExpenseCategoryID=?";
  $query .= " ORDER BY Expense.ExpenseDate DESC";

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
    $date = new DateTime($row['ExpenseDate']);

    echo '<tr>';
    echo '  <td>'.$date->format('F j, Y @ h:i A').'</td>';
    echo '  <td>'.$row['CategoryName'].'</td>';
    echo '  <td>'.$row['Description'].'</td>';
    echo '  <td>₱ '.$row['ExpenseAmount'].'</td>';
    echo '  <td>
              <button id="edit" onclick="editOverlay('.$row['ExpenseID'].', \''.$row['Description'].'\', '.$row['ExpenseCategoryID'].', '.$row['ExpenseAmount'].')">Edit</button>
              <button id="delete" onclick="deleteOverlay('.$row['ExpenseID'].')">Delete</button>
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
    $expense = array(
      'category' => $data['category'] ?? '',
      'desc' => $data['desc'] ?? '',
      'amount' => $data['amount'] ?? ''
    );

    // Validate each input is not empty
    foreach($expense as $key => $value) {
      if(!validateInput((string)$value)) {
        showMessage("Invalid input for $key.");
        redirectTo('../client/expenses.php');
        exit();
      }
    }

    $totalAllowance = getTotalAllowance($conn);
    if(!$totalAllowance) {
      showMessage('No allowance record for this month!!. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    $totalExpense = getTotalExpense($conn);
    if(!$totalExpense) {
      $totalExpense = 0;
    }

    if($expense['amount'] > ($totalAllowance - $totalExpense)) {
      showMessage('Not enough money. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    if(!addExpense($conn, $expense)) {
      showMessage('There was an error recording the expense. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    showMessage('Expense was successfully recorded!!');
  } else if(isset($data['edit'])) {
    $expense = array(
      'id' => $data['id'] ?? '',
      'category' => $data['category'] ?? '',
      'desc' => $data['desc'] ?? '',
      'amount' => $data['amount'] ?? ''
    );

    // Validate each input is not empty
    foreach($expense as $key => $value) {
      if(!validateInput((string)$value)) {
        showMessage("Invalid input for $key.");
        redirectTo('../client/expenses.php');
        exit();
      }
    }

    $lastAmount = getLastAmount($conn, $expense['id']);

    if(!$lastAmount) {
      showMessage('There was an error fetching the last expense record. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    $totalAllowance = getTotalAllowance($conn);
    if(!$totalAllowance) {
      showMessage('No allowance record for this month!!. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    $totalExpense = getTotalExpense($conn);
    if(!$totalExpense) {
      $totalExpense = 0;
    }

    if($expense['amount'] > (($totalAllowance + $lastAmount) - $totalExpense)) {
      showMessage('Not enough money. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    if(!editExpense($conn, $expense)) {
      showMessage('There was an error editing the expense. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    showMessage('Expense was successfully edited!!');
  } else if(isset($data['delete'])) {
    if(!deleteExpense($conn, $data['id'])) {
      showMessage('There was an error deleting the expense. Please try again.');
      redirectTo('../client/expenses.php');
      exit();
    }

    showMessage('Expense was successfully removed!!');
  }

  redirectTo('../client/expenses.php');
  exit();
} else {
  // Handle non-POST requests
  showMessage('Something went wrong.');
  redirectTo("../client/expenses.php");
  exit();
}