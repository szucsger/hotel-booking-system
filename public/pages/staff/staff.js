/* Receptionist Staff Page */
const currentDay = document.querySelector("#current-day");
const timeOfDay = document.querySelector("#time-of-day");
const shiftType = document.querySelector("#shift-type");
const dayShiftTime = document.querySelector("#day-shift-time");
const messagesCard = document.querySelector(".messages-card");

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
