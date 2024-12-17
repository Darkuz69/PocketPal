<?php
require_once('../server/utils/conn.php');
require_once('../server/utils/utils.php');
require_once('../server/check_login.php');
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PocketPal Dashboard</title>
  <link rel="stylesheet" href="styles/essential.css">
  <link rel="stylesheet" href="styles/expenses.css">
  <link rel="icon" href="images/favicon.png">

</head>
<body>
  
  <div class="side-navbar">
    <div class="navbar-ttl">
      <h1>PocketPal</h1>
    </div>
    <div class="navbar-link">
      <span id="logo">🏠</span>
      <a href="home.php"><h3>Home</h3></a>
    </div>
    <div id="active" class="navbar-link">
      <span id="logo">📋</span>
      <a href=""><h3>Expenses</h3></a>
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
    <div class="page-name" style="background-color: red;">
      <span id="btn">
        <button onclick="showOverlay()" style="color: blue;">
          <span style="border: 2px solid blue;"><h2>+</h2></span>
          <h2>Add Expense</h2>
        </button>
      </span>
      <h1>💸 Expenses Tracker</h1>
      Manage and track your spending.
    </div>

    <div class="main">
      <div id="main" class="panel">
        <div class="category-container">
          <select name="filter" onchange="displayChangeCategory(this.value, 'expense')">
            <option value="*">All Items</option>
            <?php
            $stmt = $conn->prepare('SELECT * FROM ExpenseCategory');
            if(!$stmt->execute()) {
              showMessage("Execution failed: " . $stmt->error);
              $stmt->close();
              return false;
            }

            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
              echo '<option value="'.$row['ExpenseCategoryID'].'" title="'.$row['Description'].'">'.$row['CategoryName'].'</option>';
            }
            ?>
          </select>
          <input type="month" name="month" value="<?=$_SESSION['Month']?>" onchange="displayChangeMonth(this.value, 'expense')">
        </div>
        <div class="expense-table">
          <table id="table">
            <tr>
              <th><h3>Date</h3></th>
              <th><h3>Category</h3></th>
              <th><h3>Description</h3></th>
              <th><h3>Amount</h3></th>
              <th><h3>Action</h3></th>
            </tr>
            <?php
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

            ?>
          </table>
        </div>
      </div>
      <div id="summary" class="panel">
        <div class="summary-card">
          Total Expenses (All Time)
          <?php
          $stmt = $conn->prepare('SELECT SUM(ExpenseAmount) AS Total FROM Expense WHERE AccountID=?');
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

          $total = $stmt->get_result()->fetch_assoc()['Total'] ?? 0;
          ?>
          <h1>₱ <?=number_format($total, 2)?></h1>
        </div>
        <div class="summary-card" style="height: 22%;">
          Highest Expense (All Time)
          <?php
          $stmt = $conn->prepare('SELECT * FROM Expense WHERE AccountID=? ORDER BY ExpenseAmount DESC LIMIT 1');
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
          <h2>
            <?php
            if(!$result) echo 'None';
            else {
              $date = new DateTime($result['ExpenseDate']);

              echo $date->format('F j, Y') . ' | '. $result['Description'] . ' | ₱ ' . $result['ExpenseAmount'];
            }
            ?>
          </h2>
        </div>
        <div class="summary-card" style="height: 15%">
          Top Expense Category (All Time)
          <?php 
          $stmt = $conn->prepare('SELECT ExpenseCategory.CategoryName, COUNT(*) AS Quantity FROM Expense INNER JOIN ExpenseCategory ON Expense.ExpenseCategoryID = ExpenseCategory.ExpenseCategoryID WHERE AccountID=? GROUP BY ExpenseCategory.ExpenseCategoryID ORDER BY Quantity DESC LIMIT 1');
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
  
          $result = $stmt->get_result()->fetch_assoc()['CategoryName'] ?? 'None';

          ?>
          <h2><?=$result?></h2>
        </div>
      </div>
    </div>
  </div>

  <form action="../server/expenses.php" method="POST" id="delete-overlay" class="overlay">
    <div class="input-ttl">
      <h1>Delete Expense??</h1>
    </div>
    <input type="hidden" id="del-id" name="id" required>
    <div class="btn-container">
      <input type="hidden" name="delete" value="1">
      <button type="submit" id="add" style="background-color: green;">
        <span>+</span>
        Delete
      </button>
      <button type="button" onclick="closeOverlay()" style="background-color: red;">
        <span>×</span>
        Cancel
      </button>
    </div>
  </form>

  <form action="../server/expenses.php" method="POST" id="edit-overlay" class="overlay">
    <div class="input-ttl">
      <h1>Edit Expense</h1>
    </div>
    <input type="hidden" id="edit-id" name="id" required>
    <div class="input-form">
      <label>Description<br></label>
      <input type="text" id="edit-desc" name="desc" required>
    </div>
    <div class="input-form">
      <label>Category<br></label>
      <select id="edit-cat" name="category" required>
      <?php
      $stmt = $conn->prepare('SELECT * FROM ExpenseCategory');
      if(!$stmt->execute()) {
        showMessage("Execution failed: " . $stmt->error);
        $stmt->close();
        return false;
      }

      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['ExpenseCategoryID'].'" title="'.$row['Description'].'">'.$row['CategoryName'].'</option>';
      }
      ?>
      </select>
    </div>
    <div class="input-form">
      <label>Amount<br></label>
      <input type="number" id="edit-amount" name="amount" min="1" step="0.01" required>
    </div>
    <div class="btn-container">
      <input type="hidden" name="edit" value="1">
      <button type="submit" id="add" style="background-color: green;">
        <span>+</span>
        Confirm Edit
      </button>
      <button type="button" onclick="closeOverlay()" style="background-color: red;">
        <span>×</span>
        Cancel Edit
      </button>
    </div>
  </form>

  <form action="../server/expenses.php" method="POST" id="over-lay" class="overlay">
    <div class="input-ttl">
      <h1>Add Expense</h1>
    </div>
    <div class="input-form">
      <label>Description<br></label>
      <input type="text" name="desc" required>
    </div>
    <div class="input-form">
      <label>Category<br></label>
      <select name="category" required>
      <?php
      $stmt = $conn->prepare('SELECT * FROM ExpenseCategory');
      if(!$stmt->execute()) {
        showMessage("Execution failed: " . $stmt->error);
        $stmt->close();
        return false;
      }

      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['ExpenseCategoryID'].'" title="'.$row['Description'].'">'.$row['CategoryName'].'</option>';
      }
      ?>
      </select>
    </div>
    <div class="input-form">
      <label>Amount<br></label>
      <input type="number" name="amount" min="1" step="0.01" required>
    </div>
    <div class="btn-container">
      <input type="hidden" name="confirm" value="1">
      <button type="submit" id="add" style="background-color: green;">
        <span>+</span>
        Confirm Expense
      </button>
      <button type="button" onclick="closeOverlay()" style="background-color: red;">
        <span>×</span>
        Cancel Expense
      </button>
    </div>
  </form>

  <script src="scripts/all-exp.js"></script>

</body>
</html>

<?php
?>