const loginButton = document.querySelector("#login-button");
const registerButton = document.querySelector("#register-button");
const roomDetailsDialog = document.querySelector("#room-details-dialog");
const guestBookingForm = document.querySelector("#guest-booking-form");
const bookingMessage = document.querySelector("#booking-message");

// Ha van query string az URL-ben, eltávolítjuk
if (window.location.search) {
  window.history.replaceState(null, "", window.location.pathname);
}

// Login kezelése
function handleLoginClick(event) {
  event.preventDefault();
  window.location.href = "/hotel-booking-system/public/pages/auth/login.php";
}

// Regisztráció kezelése
function handleRegisterClick(event) {
  event.preventDefault();
  window.location.href = "/hotel-booking-system/public/pages/auth/register.php";
}
// Szoba részletek kezelése
document.querySelectorAll(".room-details-button").forEach((button) => {
  button.addEventListener("click", () => {
    document.querySelector("#room-details-title").textContent =
      `Szoba ${button.dataset.roomNumber} – ${button.dataset.roomType}`;
    document.querySelector("#room-details-description").textContent =
      button.dataset.description;
    document.querySelector("#room-details-capacity").textContent =
      button.dataset.capacity;
    document.querySelector("#room-details-bed-type").textContent =
      button.dataset.bedType;
    document.querySelector("#room-details-price").textContent = Number(
      button.dataset.price,
    ).toLocaleString("hu-HU");

    document.querySelector("#booking-room-id").value =
      button.getAttribute("data-room-id");
    document.querySelector("#booking-check-in").value =
      button.getAttribute("data-check-in");
    document.querySelector("#booking-check-out").value =
      button.getAttribute("data-check-out");
    document.querySelector("#booking-guest-count").value =
      button.getAttribute("data-guest-count");
    roomDetailsDialog.showModal();
  });
});

loginButton.addEventListener("click", handleLoginClick);
registerButton.addEventListener("click", handleRegisterClick);

// Szoba részletek dialógus bezárása
document.querySelector("#close-room-details").addEventListener("click", () => {
  roomDetailsDialog.close();
});

// Vendég foglalás kezelése
guestBookingForm.addEventListener("submit", async (event) => {
  event.preventDefault();
  const submitButton = guestBookingForm.querySelector("button[type='submit']");
  const originalButtonText = submitButton.textContent;
  submitButton.disabled = true;
  submitButton.textContent = "Feldolgozás...";

  try {
    const response = await fetch(
      "/hotel-booking-system/public/api/router.php",
      {
        method: "POST",
        body: new FormData(guestBookingForm),
        headers: { Accept: "application/json" },
      },
    );

    const result = await response.json();
    bookingMessage.textContent =
      result.message || "A foglalási kérés feldolgozása nem sikerült";
  } catch {
    bookingMessage.textContent =
      "Nem sikerült elküldeni a foglalási kérelmet. Próbálja újra később.";
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = originalButtonText;
  }
});
