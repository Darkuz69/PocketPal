function showOverlay() {
  document.querySelector('.side-navbar').classList.add('blur');
  document.querySelector('.main-content').classList.add('blur');

  document.getElementById('over-lay').classList.add('active');
}

function editOverlay(id, desc, cat_id, amount) {
  document.querySelector('input[id="edit-id"]').value = id;
  document.querySelector('input[id="edit-desc"]').value = desc;
  document.querySelector('select[id="edit-cat"]').value = cat_id;
  document.querySelector('input[id="edit-amount"]').value = amount;

  document.querySelector('.side-navbar').classList.add('blur');
  document.querySelector('.main-content').classList.add('blur');

  document.getElementById('edit-overlay').classList.add('active');
}

function deleteOverlay(id) {
  document.querySelector('input[id="del-id"]').value = id;

  document.querySelector('.side-navbar').classList.add('blur');
  document.querySelector('.main-content').classList.add('blur');

  document.getElementById('delete-overlay').classList.add('active');
}

function closeOverlay() {
  document.querySelector('.side-navbar').classList.remove('blur');
  document.querySelector('.main-content').classList.remove('blur');

  document.getElementById('over-lay').classList.remove('active');
  document.getElementById('edit-overlay').classList.remove('active');
  document.getElementById('delete-overlay').classList.remove('active');
}

function displayChangeCategory(str, state) {
  if(str == "") {
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if(this.readyState == 4 && this.status == 200) {
        var table = document.getElementById('table');
        while(table.rows.length > 1) {
          table.deleteRow(1);
        }

        table.innerHTML += this.responseText;
      }
    };

    if(state == "expense") xmlhttp.open("POST", "http://localhost/PocketPal/server/expenses.php", true);
    else if(state == "allowance") xmlhttp.open("POST", "http://localhost/PocketPal/server/allowance.php", true);
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlhttp.send("filter=" + encodeURIComponent(str));
  }
}

function displayChangeMonth(str) {
  if(str == "") {
    return;
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
      if(this.readyState == 4 && this.status == 200) {
        var table = document.getElementById('table');
        while(table.rows.length > 1) {
          table.deleteRow(1);
        }

        table.innerHTML += this.responseText;
      }
    };

    if(state == "expense") xmlhttp.open("POST", "http://localhost/PocketPal/server/expenses.php", true);
    else if(state == "allowance") xmlhttp.open("POST", "http://localhost/PocketPal/server/allowance.php", true);
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlhttp.send("month=" + encodeURIComponent(str));
  }
}