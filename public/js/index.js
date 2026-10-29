const loginButton = document.querySelector("#login-button");
const registerButton = document.querySelector("#register-button");

function handleLoginClick(event) {
  event.preventDefault();
  window.location.href = "/hotel-booking-system/public/pages/auth/login.php";
}

function handleRegisterClick(event) {
  event.preventDefault();
  window.location.href = "/hotel-booking-system/public/pages/auth/register.php";
}

loginButton.addEventListener("click", handleLoginClick);
registerButton.addEventListener("click", handleRegisterClick);
