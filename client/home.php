<?php
require_once("../server/check_login.php");
session_start();
$stmt = $conn->prepare('SELECT SUM(ALlowanceAmount) AS Total FROM Allowance WHERE AccountID=? AND DATE_FORMAT(AllowanceDate, "%Y-%m")=?');

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

$total = $stmt->get_result()->fetch_assoc()['Total'] ?? 0;

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

$expense = $stmt->get_result()->fetch_assoc()['Total'] ?? 0;

$allowance = $total - $expense;

$allowancePercent = $total > 0 ? ($allowance / $total) * 100 : 0;
$expensePercent = $total > 0 ? ($expense / $total) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PocketPal Dashboard</title>
  <link rel="stylesheet" href="styles/home.css">
  <link rel="stylesheet" href="styles/essential.css">
  <link rel="icon" href="images/favicon.png">

</head>
<body>

  <div class="side-navbar">
    <div class="navbar-ttl">
      <h1>PocketPal</h1>
    </div>
    <div id="active" class="navbar-link">
      <span id="logo">🏠</span>
      <a href=""><h3>Home</h3></a>
    </div>
    <div class="navbar-link">
      <span id="logo">📋</span>
      <a href="expenses.php"><h3>Expenses</h3></a>
    </div>
    <div class="navbar-link">
      <span id="logo">🪙</span>
      <a href="allowance.php"><h3>Allowance</h3></a>
    </div>
    <div class="navbar-link">
      <span id="logo">📜</span>
      <a href="history.php"><h3>Financial History</h3></a>
    </div>
    <div class="navbar-link" style="margin-top: 26em;">
      <span id="logo">❌</span>
      <form action="../server/home.php" method="POST">
        <input type="hidden" name="logout" value="1">
        <button type="submit"><h3>Log Out</h3></button>
      </form>
    </div>
  </div>

  <div class="main-content">
    <div class="page-name" style="background-color: gold;">
      <span id="img"><img src="images/favicon.png"></span>
      <h1>🎉 Welcome, <?=$_SESSION['FirstName']?>!!</h1>
      Your financial overview for this month.
    </div>
    <div class="card-container">
      <div class="card-main">
        <span id="logo">💸</span>
        <h4>Allowance Remaining</h4>
        <h1>₱ <?=number_format($allowance, 2)?></h1>
        <div class="progress-bar" id="allowance">
          <div class="progress" id="all-prog" style="width: <?=$allowancePercent?>%;"></div>
        </div>
        <?=round($allowancePercent, 2)?>% of monthly allowance
      </div>
      <div class="card-main">
        <span id="logo">💲</span>
        <h4>Total Expenses</h4>
        <h1>₱ <?=number_format($expense, 2)?></h1>
        <div class="progress-bar" id="expenses">
          <div class="progress" id="exp-prog" style="width: <?=$expensePercent?>%;"></div>
        </div>
        <?=round($expensePercent, 2)?>% of monthly allowance
      </div>
      <div class="card-main">
        <span id="logo">🗓️</span>
        <h4>Today's summary</h4>
        <?php 
        $stmt = $conn->prepare('SELECT SUM(ExpenseAmount) AS Total, COUNT(*) AS Quantity FROM Expense WHERE AccountID=? AND DATE(ExpenseDate)=CURRENT_DATE');
          if(!$stmt->bind_param("i", $_SESSION['AccountID'])){
            showMessage("Binding parameters failed: " . $stmt->error);
            $stmt->close();
            return false;
          }

          if(!$stmt->execute()) {
            showMessage("Execution failed: " . $stmt->error);
            $stmt->close();
            return false;
          }

          $result = $stmt->get_result()->fetch_assoc();
        ?>
        <h1>₱ <?=number_format($result['Total'], 2)?></h1>
        <?=$result['Quantity']?> Transactions
      </div>
    </div>
    <div class="recents-container">
      <h2>Recent Expenses</h2>
      <table>
        <?php
        $stmt = $conn->prepare('SELECT Expense.Description, Expense.ExpenseAmount, Expense.ExpenseDate, ExpenseCategory.CategoryName FROM Expense INNER JOIN ExpenseCategory ON Expense.ExpenseCategoryID = ExpenseCategory.ExpenseCategoryID WHERE AccountID=? ORDER BY ExpenseDate DESC LIMIT 5');
        
        if(!$stmt->bind_param("i", $_SESSION['AccountID'])){
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
        while($row = $result->fetch_assoc()) {
          $date = new DateTime($row['ExpenseDate']);
          
          echo '<tr>';
          echo '  <td>';
          echo '    <h3>'.$row['Description'].'</h3>';
          echo '    '.$row['CategoryName'].' | '.$date->format('F j, Y @ h:i A').'';
          echo '  </td>';
          echo '  <td id="right">';
          echo '    <h2>₱ '.number_format($row['ExpenseAmount'], 2).'</h2>';
          echo '  </td>';
          echo '</tr>';
        }
        ?>
      </table>
    </div>
  </div>

</body>
</html>

<?php
session_write_close();
?>