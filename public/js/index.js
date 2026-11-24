const loginButton = document.querySelector("#login-button");
const registerButton = document.querySelector("#register-button");
const roomDetailsDialog = document.querySelector("#room-details-dialog");
const guestBookingForm = document.querySelector("#guest-booking-form");
const bookingMessage = document.querySelector("#booking-message");
const galleryMainImage = document.querySelector("#gallery-main-image");
const galleryPreviousButton = document.querySelector("#gallery-previous");
const galleryNextButton = document.querySelector("#gallery-next");
const galleryThumbnails = document.querySelector("#gallery-thumbnails");

let galleryImages = [];
let currentImageIndex = 0;

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
// Hozzáadjuk az eseménykezelőket a login és register gombokhoz
if (loginButton) {
  loginButton.addEventListener("click", handleLoginClick);
}

if (registerButton) {
  registerButton.addEventListener("click", handleRegisterClick);
}

// Szoba részletek dialógus bezárása
document.querySelector("#close-room-details").addEventListener("click", () => {
  roomDetailsDialog.close();
});

// Galéria renderelése és kezelése
function renderGallery() {
  galleryThumbnails.textContent = "";
  galleryMainImage.hidden = galleryImages.length === 0;
  galleryPreviousButton.disabled = galleryImages.length < 2;
  galleryNextButton.disabled = galleryImages.length < 2;

  if (galleryImages.length === 0) {
    galleryMainImage.removeAttribute("src");
    galleryMainImage.alt = "";
    return;
  }

  currentImageIndex =
    (currentImageIndex + galleryImages.length) % galleryImages.length;
  galleryMainImage.src = galleryImages[currentImageIndex];
  galleryMainImage.alt = `Szoba kép ${currentImageIndex + 1}`;

  galleryImages.forEach((imagePath, index) => {
    const thumbnailButton = document.createElement("button");
    thumbnailButton.type = "button";
    thumbnailButton.className = "room-gallery-thumbnail";
    thumbnailButton.setAttribute("aria-label", `Kép ${index + 1}`);
    thumbnailButton.setAttribute(
      "aria-pressed",
      String(index === currentImageIndex),
    );
    thumbnailButton.addEventListener("click", () => {
      currentImageIndex = index;
      renderGallery();
    });

    const thumbnailImage = document.createElement("img");
    thumbnailImage.src = imagePath;
    thumbnailImage.alt = "";
    thumbnailButton.append(thumbnailImage);
    galleryThumbnails.append(thumbnailButton);
  });
}

// Szoba részletek gomb eseménykezelője
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

    document.querySelector("#booking-room-id").value = button.dataset.roomId;
    document.querySelector("#booking-check-in").value = button.dataset.checkIn;
    document.querySelector("#booking-check-out").value =
      button.dataset.checkOut;
    document.querySelector("#booking-guest-count").value =
      button.dataset.guestCount;
    bookingMessage.textContent = "";

    const imagePrefix = button.dataset.imagePrefix;
    galleryImages = imagePrefix
      ? Array.from(
          { length: 5 },
          (_, index) =>
            `/hotel-booking-system/public/assets/images/rooms/${imagePrefix}-${String(index + 1).padStart(2, "0")}.png`,
        )
      : [];
    currentImageIndex = 0;
    renderGallery();
    roomDetailsDialog.showModal();
  });
});
// Galéria navigáció gomb eseménykezelői
galleryPreviousButton.addEventListener("click", () => {
  currentImageIndex =
    (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
  renderGallery();
});

galleryNextButton.addEventListener("click", () => {
  currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
  renderGallery();
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
