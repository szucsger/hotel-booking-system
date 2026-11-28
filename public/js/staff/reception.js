import { postApiRequest } from "../api-client.js";

/* Recepciós oldal interakciói */
// A recepciós oldal HTML-elemei, amelyeket a JavaScript kezel.
const currentDay = document.querySelector("#current-day");
const timeOfDay = document.querySelector("#time-of-day");
const shiftType = document.querySelector("#shift-type");
const dayShiftTime = document.querySelector("#day-shift-time");
const searchInput = document.querySelector("#search-input");
const arrivalCards = document.querySelectorAll(".arrival-card");
const bookingModal = document.querySelector("#booking-modal");
const newBookingForm = document.querySelector("#new-booking-form");
let bookingWasCreated = false;
// Az űrlap action attribútuma tartalmazza a központi API-végpont címét.
// A getAttribute kell, mert a name="action" gomb felülírná a form.action tulajdonságot.
const apiEndpoint = newBookingForm.getAttribute("action");
const newBookingMessage = document.querySelector("#new-booking-message");
const checkInDateInput = document.querySelector("#check-in-date");

const checkOutDateInput = document.querySelector("#check-out-date");
const bookingGuestCountInput = document.querySelector("#guest-count");
const bookingRoomSelect = document.querySelector("#room-id");
const availableRoomMessage = document.querySelector("#available-room-message");
const searchGuestModal = document.querySelector("#search-guest-modal");
const searchGuestResults = document.querySelector("#guest-search-results");
const searchGuestResultList = document.querySelector(
  "#guest-search-result-list",
);
const searchGuestMessage = document.querySelector("#guest-search-message");
const modifyRoomStatusForm = document.querySelector("#modify-room-status-form");
const roomStatusMessage = document.querySelector("#room-status-message");
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

// A modal bezárásakor töröljük az előző keresést, hogy újra üresen nyíljon meg.
function resetSearchGuestModal() {
  if (searchGuestForm) {
    searchGuestForm.reset();
  }
  if (searchGuestInput) {
    searchGuestInput.value = "";
  }

  if (searchGuestResults) {
    searchGuestResults.hidden = true;
  }
  if (searchGuestResultList) {
    searchGuestResultList.replaceChildren();
  }
  if (searchGuestMessage) {
    searchGuestMessage.textContent = "";
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

// A szerver válaszát a modalban, a siker vagy hiba állapotának megfelelően mutatja.
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
  formData.set("action", "reception.available_rooms");
  formData.set("check_in_date", checkInDate);
  formData.set("check_out_date", checkOutDate);
  formData.set("guest_count", guestCount);
  formData.set(
    "csrf_token",
    newBookingForm.querySelector('input[name="csrf_token"]').value,
  );

  try {
    const result = await postApiRequest(apiEndpoint, formData);

    if (requestNumber !== roomAvailabilityRequest) {
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

    const submitButton = newBookingForm.querySelector("#new-booking-submit");
    if (submitButton) {
      submitButton.disabled = true;
    }

    const formData = new FormData(newBookingForm);
    // A PHP ezzel különbözteti meg az új foglalás mentését a szobakereséstől.
    formData.set("action", "reception.new_booking");

    try {
      const result = await postApiRequest(apiEndpoint, formData);
      showBookingMessage(
        result.message || "A foglalási kérés feldolgozása nem sikerült.",
        true,
      );

      // Siker után ürítjük az adatokat, a modal és az üzenet nyitva marad.
      bookingWasCreated = true;
      newBookingForm.reset();
      updateAvailableRooms();
    } catch (error) {
      showBookingMessage(error.message, false);
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
      }
    }
  });
}

// A vendégkeresés eredményét az API-tól kérjük le, és biztonságosan szövegként jelenítjük meg.
if (
  searchGuestForm &&
  searchGuestResults &&
  searchGuestResultList &&
  searchGuestMessage
) {
  searchGuestForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    searchGuestResults.hidden = true;
    searchGuestResultList.replaceChildren();
    searchGuestMessage.textContent = "";

    const formData = new FormData(searchGuestForm);
    formData.set("action", "reception.search_guest");

    try {
      const result = await postApiRequest(apiEndpoint, formData);

      result.guests.forEach(function (guest) {
        const article = document.createElement("article");
        article.className = "guest-result";

        const name = document.createElement("h4");
        name.textContent = guest.name;
        article.append(name);

        const email = document.createElement("p");
        email.textContent = guest.email;
        article.append(email);

        const phone = document.createElement("p");
        phone.textContent = guest.phone_number || "";
        article.append(phone);

        searchGuestResultList.append(article);
      });

      searchGuestResults.hidden = false;
      searchGuestMessage.textContent = result.message;
    } catch (error) {
      searchGuestMessage.textContent = error.message;
    }
  });
}

// A check-in, check-out és szobastátusz-űrlapokat újratöltés nélkül küldjük az API-nak.
document
  .querySelectorAll("form[data-reception-action]")
  .forEach(function (form) {
    form.addEventListener("submit", async function (event) {
      event.preventDefault();

      const submitButton = form.querySelector("#modify-room-status-submit");
      if (submitButton) {
        submitButton.disabled = true;
      }

      try {
        const formData = new FormData(form);
        await postApiRequest(apiEndpoint, formData);
        window.location.reload();
      } catch (error) {
        if (form === modifyRoomStatusForm && roomStatusMessage) {
          roomStatusMessage.textContent = error.message;
          roomStatusMessage.classList.remove("alert-success");
          roomStatusMessage.classList.add("alert-danger");
          roomStatusMessage.hidden = false;
        } else {
          window.alert(error.message);
        }
      } finally {
        if (submitButton) {
          submitButton.disabled = false;
        }
      }
    });
  });

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

// A keresőablak megnyitásakor eltávolítjuk az előző keresés eredményét.
document
  .querySelector("#open-search-guest-modal-button")
  .addEventListener("click", function () {
    resetSearchGuestModal();
    searchGuestModal.showModal();
  });

// A szobastátusz-ablakot a szerver visszajelzéseivel együtt jelenítjük meg.
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
// A keresőablak bezárásakor is töröljük az előző keresés eredményét.
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
