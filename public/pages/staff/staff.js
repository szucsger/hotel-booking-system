/* Receptionist Staff Page */
// A recepciós oldal HTML-elemei, amelyeket a JavaScript kezel.
const currentDay = document.querySelector("#current-day");
const timeOfDay = document.querySelector("#time-of-day");
const shiftType = document.querySelector("#shift-type");
const dayShiftTime = document.querySelector("#day-shift-time");
const messagesCard = document.querySelector(".messages-card");
const searchInput = document.querySelector("#search-input");
const arrivalCards = document.querySelectorAll(".arrival-card");
const bookingModal = document.querySelector("#booking-modal");
const newBookingForm = document.querySelector("#new-booking-form");
let bookingWasCreated = false;
const bookingEndpoint = newBookingForm
  ? newBookingForm.getAttribute("action")
  : window.location.pathname;
const newBookingMessage = document.querySelector("#new-booking-message");
const checkInDateInput = document.querySelector("#check-in-date");
const checkOutDateInput = document.querySelector("#check-out-date");
const bookingGuestCountInput = document.querySelector("#guest-count");
const bookingRoomSelect = document.querySelector("#room-id");
const availableRoomMessage = document.querySelector("#available-room-message");
const searchGuestModal = document.querySelector("#search-guest-modal");
const modifyRoomStatusModal = document.querySelector(
  "#modify-room-status-modal",
);
const closeBookingModalButton = document.querySelector("#close-booking-modal");
const closeSearchGuestModalButton = document.querySelector(
  "#close-search-guest-modal",
);
const searchGuestForm = document.querySelector("#search-guest-form");
const searchGuestInput = document.querySelector("#search-guest-name");
const closeModifyRoomStatusModalButton = document.querySelector(
  "#close-modify-room-status-modal",
);

// A mai dátum frissítése a fejlécben.
document.addEventListener("DOMContentLoaded", function () {
  if (!currentDay) {
    return;
  }
  function updateDate() {
    let now = new Date();
    let options = {
      year: "numeric",
      month: "long",
      day: "numeric",
    };
    currentDay.textContent = now.toLocaleDateString("hu-HU", options);
  }
  updateDate();
  setInterval(updateDate, 1000);
});

// Köszönés beállítása a böngésző helyi ideje alapján.
document.addEventListener("DOMContentLoaded", function () {
  function updateTimeOfDay() {
    let now = new Date();
    let hours = now.getHours();
    if (hours < 12) {
      timeOfDay.textContent = "Jó reggelt!";
    } else if (hours < 18) {
      timeOfDay.textContent = "Jó napot!";
    } else {
      timeOfDay.textContent = "Jó estét!";
    }
  }
  updateTimeOfDay();
  setInterval(updateTimeOfDay, 1000);
});

// A napszakhoz tartozó műszak megjelenítése.
function updateShiftInfo() {
  if (!shiftType || !dayShiftTime) {
    return;
  }
  let now = new Date();
  let hours = now.getHours();
  if (hours >= 8 && hours < 16) {
    shiftType.textContent = "Nappali műszak";
    dayShiftTime.textContent = "8:00-16:00";
  } else if (hours >= 16 && hours < 24) {
    shiftType.textContent = "Esti műszak";
    dayShiftTime.textContent = "16:00-24:00";
  } else {
    shiftType.textContent = "Éjszakai műszak";
    dayShiftTime.textContent = "0:00-8:00";
  }
}
document.addEventListener("DOMContentLoaded", function () {
  updateShiftInfo();
  setInterval(updateShiftInfo, 1000);
});

// Csak a már kirenderelt érkezőkártyákat szűri, nem kér új adatot a szervertől.
function filterArrivalCards() {
  const searchText = searchInput.value.trim().toLocaleLowerCase("hu-HU");

  arrivalCards.forEach(function (card) {
    const cardText = card.textContent.toLocaleLowerCase("hu-HU");
    card.hidden = !cardText.includes(searchText);
  });
}

if (searchInput) {
  searchInput.addEventListener("input", filterArrivalCards);
}

function resetSearchGuestModal() {
  if (searchGuestForm) {
    searchGuestForm.reset();
  }
  if (searchGuestInput) {
    searchGuestInput.value = "";
  }

  const searchGuestResults = searchGuestModal.querySelector(
    ".guest-search-results",
  );
  if (searchGuestResults) {
    searchGuestResults.remove();
  }
}

// A korábbi foglalási visszajelzés eltüntetése a modal következő megnyitásakor.
function clearBookingMessage() {
  if (!newBookingMessage) {
    return;
  }

  newBookingMessage.textContent = "";
  newBookingMessage.classList.remove("alert-success", "alert-danger");
  newBookingMessage.hidden = true;
}

function showBookingMessage(message, isSuccess) {
  newBookingMessage.textContent = message;
  newBookingMessage.classList.toggle("alert-success", isSuccess);
  newBookingMessage.classList.toggle("alert-danger", !isSuccess);
  newBookingMessage.hidden = false;
}

// Minden új szobakeresés sorszámot kap, így egy lassabb, régi válasz nem írja
// felül az újabb dátumválasztás eredményét.
let roomAvailabilityRequest = 0;

// A dátumtartományhoz és vendégszámhoz illő szobákat kéri le a PHP-végponttól.
async function updateAvailableRooms() {
  const requestNumber = ++roomAvailabilityRequest;
  bookingRoomSelect.replaceChildren(new Option("Dátumok alapján keresés", ""));
  availableRoomMessage.textContent = "";

  const checkInDate = checkInDateInput.value;
  const checkOutDate = checkOutDateInput.value;
  const guestCount = bookingGuestCountInput.value;

  if (!checkInDate || !checkOutDate || !guestCount) {
    availableRoomMessage.textContent =
      "Válaszd ki az érkezés és távozás dátumát.";
    return;
  }

  if (checkOutDate <= checkInDate) {
    availableRoomMessage.textContent =
      "A távozás dátuma legyen később az érkezésnél.";
    return;
  }

  const formData = new FormData();
  // Az action alapján a PHP tudja, hogy most csak szabad szobákat kérünk.
  formData.set("action", "available_rooms");
  formData.set("check_in_date", checkInDate);
  formData.set("check_out_date", checkOutDate);
  formData.set("guest_count", guestCount);

  try {
    // A fetch háttérben küldi az adatokat, az oldal nem töltődik újra.
    const response = await fetch(bookingEndpoint, {
      method: "POST",
      headers: { Accept: "application/json" },
      body: formData,
    });
    const responseText = await response.text();
    let result;

    // Előbb szövegként olvassuk a választ, hogy a PHP HTML-hibája is naplózható legyen.
    try {
      result = JSON.parse(responseText);
    } catch (error) {
      console.error(
        "Room availability endpoint returned non-JSON:",
        responseText,
      );
      throw new Error(
        "A szerver hibás választ adott (HTTP " + response.status + ").",
      );
    }

    if (requestNumber !== roomAvailabilityRequest) {
      return;
    }

    if (!response.ok || result.success !== true) {
      availableRoomMessage.textContent =
        result.message || "Nem sikerült lekérni a szabad szobákat.";
      return;
    }

    bookingRoomSelect.replaceChildren(new Option("Válassz szobát", ""));

    result.rooms.forEach(function (room) {
      // A szoba ID-ja kerül az option value-ba; ezt küldi majd el a foglalás.
      const option = new Option(
        "Szoba " + room.room_number + " - " + room.room_type,
        room.id,
      );
      bookingRoomSelect.add(option);
    });

    availableRoomMessage.textContent =
      result.rooms.length > 0
        ? result.rooms.length + " szabad szoba található erre az időszakra."
        : result.message;
  } catch (error) {
    if (requestNumber === roomAvailabilityRequest) {
      console.error("Room availability request failed:", error);
      availableRoomMessage.textContent =
        "Nem sikerült lekérni a szabad szobákat: " + error.message;
    }
  }
}

if (
  checkInDateInput &&
  checkOutDateInput &&
  bookingGuestCountInput &&
  bookingRoomSelect
) {
  // Dátum vagy vendégszám változásakor újra kell számolni az elérhető szobákat.
  checkInDateInput.addEventListener("change", updateAvailableRooms);
  checkOutDateInput.addEventListener("change", updateAvailableRooms);
  bookingGuestCountInput.addEventListener("change", updateAvailableRooms);
}

if (newBookingForm && newBookingMessage) {
  newBookingForm.addEventListener("submit", async function (event) {
    // Megakadályozza a hagyományos POST-ot és az oldal teljes újratöltését.
    event.preventDefault();
    clearBookingMessage();

    const submitButton = newBookingForm.querySelector('button[type="submit"]');
    if (submitButton) {
      submitButton.disabled = true;
    }

    const formData = new FormData(newBookingForm);
    // A PHP ezzel különbözteti meg az új foglalás mentését a szobakereséstől.
    formData.set("action", "new_booking");

    try {
      const response = await fetch(bookingEndpoint, {
        method: "POST",
        headers: { Accept: "application/json" },
        body: formData,
      });
      const result = await response.json();

      showBookingMessage(
        result.message || "A foglalási kérés feldolgozása nem sikerült.",
        result.success === true,
      );

      if (result.success) {
        // Siker után ürítjük az adatokat, a modal és az üzenet nyitva marad.
        bookingWasCreated = true;
        newBookingForm.reset();
        updateAvailableRooms();
      }
    } catch (error) {
      showBookingMessage("Nem sikerült kapcsolatba lépni a szerverrel.", false);
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
      }
    }
  });
}

// A foglaló modal minden megnyitáskor tiszta űrlappal indul.
document
  .querySelector("#open-booking-modal-button")
  .addEventListener("click", function () {
    bookingWasCreated = false;
    if (newBookingForm) {
      newBookingForm.reset();
    }
    clearBookingMessage();
    updateAvailableRooms();
    bookingModal.showModal();
  });

document
  .querySelector("#open-search-guest-modal-button")
  .addEventListener("click", function () {
    resetSearchGuestModal();
    searchGuestModal.showModal();
  });

document
  .querySelector("#open-modify-room-status-modal-button")
  .addEventListener("click", function () {
    modifyRoomStatusModal.showModal();
  });

if (closeBookingModalButton) {
  closeBookingModalButton.addEventListener("click", function () {
    bookingModal.close();
  });
}

// Sikeres foglalás után bezáráskor frissítjük a listát és a számlálókat.
if (bookingModal) {
  bookingModal.addEventListener("close", function () {
    if (bookingWasCreated) {
      window.location.reload();
    }
  });
}

if (closeSearchGuestModalButton) {
  closeSearchGuestModalButton.addEventListener("click", function () {
    searchGuestModal.close();
  });
}

if (searchGuestModal) {
  searchGuestModal.addEventListener("close", resetSearchGuestModal);
}

if (closeModifyRoomStatusModalButton) {
  closeModifyRoomStatusModalButton.addEventListener("click", function () {
    modifyRoomStatusModal.close();
  });
}
