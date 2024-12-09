<?php
require_once('../server/utils/conn.php');
require_once('../server/utils/utils.php');
require_once('../server/check_login.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PocketPal Dashboard</title>
  <link rel="stylesheet" href="styles/essential.css">
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
    <div class="navbar-link">
      <span id="logo">📋</span>
      <a href="expenses.php"><h3>Expenses</h3></a>
    </div>
    <div id="active" class="navbar-link">
      <span id="logo">🪙</span>
      <a href=""><h3>Allowance</h3></a>
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
    <div class="page-name" style="background-color: blue;">
      <span id="btn">
        <button onclick="showOverlay()" style="color: red;">
          <span style="border: 2px solid red;"><h2>+</h2></span>
          <h2>Add Allowance</h2>
        </button>
      </span>
      <h1>👛 Allowance Tracker</h1>
      Manage and track your personal allowance.
    </div>

    <div class="main">
      <div id="main" class="panel">
        <form action="../server/allowance.php" class="category-container" method="POST">
          <select name="filter">
            <option value="*">All Items</option>
            <?php
            $stmt = $conn->prepare('SELECT * FROM AllowanceSource');
            if(!$stmt->execute()) {
              showMessage("Execution failed: " . $stmt->error);
              $stmt->close();
              return false;
            }

            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
              echo '<option value="'.$row['SourceID'].'" title="'.$row['Description'].'">'.$row['SourceName'].'</option>';
            }
            ?>
          </select>
          <input type="month" name="month" value="<?=$_SESSION['Month']?>">
          <input type="submit" name="confirm-filter" value="Activate Filter" id="right">
        </form>
        <div class="expense-table">
          <table>
            <tr>
              <th><h3>Date</h3></th>
              <th><h3>Category</h3></th>
              <th><h3>Description</h3></th>
              <th><h3>Amount</h3></th>
              <th><h3>Action</h3></th>
            </tr>
            <?php
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
            ?>
          </table>
        </div>
      </div>
      <div id="summary" class="panel">
        <div class="summary-card">
          Total Allowance (All Time)
          <?php
          $stmt = $conn->prepare('SELECT SUM(AllowanceAmount) AS Total FROM Allowance WHERE AccountID=?');
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
          Highest Allowance (All Time)
          <?php
          $stmt = $conn->prepare('SELECT * FROM Allowance WHERE AccountID=? ORDER BY AllowanceAmount DESC LIMIT 1');
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
              $date = new DateTime($result['AllowanceDate']);

              echo $date->format('F j, Y') . ' | '. $result['Description'] . ' | ₱ ' . $result['AllowanceAmount'];
            }
            ?>
          </h2>
        </div>
        <div class="summary-card" style="height: 15%">
          Top Allowance Source (All Time)
          <?php 
          $stmt = $conn->prepare('SELECT AllowanceSource.SourceName, COUNT(*) AS Quantity FROM Allowance INNER JOIN AllowanceSource ON Allowance.SourceID = AllowanceSource.SourceID WHERE AccountID=? GROUP BY Allowance.SourceID ORDER BY Quantity DESC LIMIT 1');
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
  
          $result = $stmt->get_result()->fetch_assoc()['SourceName'] ?? 'None';
          ?>
          <h2><?=$result?></h2>
        </div>
      </div>
    </div>
  </div>
  <form action="../server/allowance.php" method="POST" id="delete-overlay" class="overlay">
    <div class="input-ttl">
      <h1>Delete Allowance??</h1>
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

  <form action="../server/allowance.php" method="POST" id="edit-overlay" class="overlay">
    <div class="input-ttl">
      <h1>Edit Allowance</h1>
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

  <form action="../server/allowance.php" id="over-lay" class="overlay" method="POST">
    <div class="input-ttl">
      <h1>Add Allowance</h1>
    </div>
    <div class="input-form">
      <label>Description<br></label>
      <input name="desc" type="text" required>
    </div>
    <div class="input-form">
      <label>Category<br></label>
      <select name="category" required>
      <?php
      $stmt = $conn->prepare('SELECT * FROM AllowanceSource');
      if(!$stmt->execute()) {
        showMessage("Execution failed: " . $stmt->error);
        $stmt->close();
        return false;
      }

      $result = $stmt->get_result();
      while($row = $result->fetch_assoc()) {
        echo '<option value="'.$row['SourceID'].'" title="'.$row['Description'].'">'.$row['SourceName'].'</option>';
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
      <button id="add" style="background-color: green;">
        <span>+</span>
        Confirm Allowance
      </button>
      <button onclick="closeOverlay()" style="background-color: red;">
        <span>×</span>
        Cancel Allowance
      </button>
    </div>
  </form>
  
  <script src="scripts/all-exp.js"></script>

</body>
</html>