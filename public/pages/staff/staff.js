/* Receptionist Staff Page */
const currentDay = document.querySelector("#current-day");
const timeOfDay = document.querySelector("#time-of-day");
const shiftType = document.querySelector("#shift-type");
const dayShiftTime = document.querySelector("#day-shift-time");
const messagesCard = document.querySelector(".messages-card");
const searchInput = document.querySelector("#search-input");
const arrivalCards = document.querySelectorAll(".arrival-card");
const bookingModal = document.querySelector("#booking-modal");
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

//get the current day element
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

// get the time of day element
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

// update shift information based on the current time
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

// search functionality for arrival cards
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

// modal functionality
document
  .querySelector("#open-booking-modal-button")
  .addEventListener("click", function () {
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
