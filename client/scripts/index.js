function togglePage(page_part){
  if(page_part == 'login') {
    var login_part = document.getElementById('login');
    login_part.style.display = "none";

    var welcom_part = document.getElementById('welcome');
    welcom_part.style.float = "right";

    var welcom_msg = document.getElementById('welcome-msg');
    welcom_msg.style.marginLeft = "0%";
    welcom_msg.style.marginRight = "25%";

    var welcom_part = document.getElementById('signup');
    welcom_part.style.display = "flex";
  } else if(page_part == 'signup') {
    var login_part = document.getElementById('login');
    login_part.style.display = "flex";

    var welcom_part = document.getElementById('welcome');
    welcom_part.style.float = "left";
    
    var welcom_msg = document.getElementById('welcome-msg');
    welcom_msg.style.marginLeft = "25%";
    welcom_msg.style.marginRight = "0%";

    var welcom_part = document.getElementById('signup');
    welcom_part.style.display = "none";
  }
}