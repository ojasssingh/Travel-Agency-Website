// ================= DOM =================

const form = document.getElementById("bookingForm");
const destination = document.getElementById("destination");
const travelDate = document.getElementById("date");
const passengers = document.getElementById("people");
const hamburger = document.getElementById("hamburger");

// ================= DATE HANDLING =================

let today = new Date().toISOString().split("T")[0];
travelDate.setAttribute("min", today);

// ================= EVENTS =================

if (hamburger) {
    hamburger.addEventListener("click", function () {
        alert("Menu clicked!");
    });
}

form.addEventListener("submit", function (event) {

    let isValid = validateForm();

    // ❌ Stop if invalid
    if (!isValid) {
        event.preventDefault();
        return;
    }

    // ✅ Confirm before sending to PHP
    let confirmBooking = confirm(
        "Confirm Booking?\n\n" +
        "Destination: " + destination.value +
        "\nDate: " + travelDate.value +
        "\nPeople: " + passengers.value
    );

    // ❌ If user cancels → STOP
    if (!confirmBooking) {
        event.preventDefault();
        return;
    }

    // ✅ If confirmed → form goes to PHP (no alert here)
});

// ================= VALIDATION =================

function validateForm() {
    let valid = true;

    document.querySelectorAll(".error").forEach(e => e.innerText = "");

    if (destination.value === "") {
        showError(destination, "Select destination");
        valid = false;
    }

    let today = new Date().toISOString().split("T")[0];
    if (travelDate.value === "" || travelDate.value < today) {
        showError(travelDate, "Select today or a future date");
        valid = false;
    }

    if (passengers.value === "" || passengers.value <= 0 || passengers.value > 1000) {
        showError(passengers, "Enter valid number of people");
        valid = false;
    }

    return valid;
}

// ================= ERROR DISPLAY =================

function showError(input, message) {
    input.nextElementSibling.innerText = message;
}
