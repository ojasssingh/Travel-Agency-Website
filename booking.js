// ================= DOM =================

const form = document.getElementById("booking-form");
const destination = document.getElementById("destination");
const travelDateStart = document.getElementById("travel_date_start");
const travelers = document.getElementById("travelers");
const hamburger = document.getElementById("hamburger");

// ================= DATE HANDLING =================

let today = new Date().toISOString().split("T")[0];
if (travelDateStart) {
    travelDateStart.setAttribute("min", today);
}

// ================= EVENTS =================

if (hamburger) {
    hamburger.addEventListener("click", function () {
        alert("Menu clicked!");
    });
}

if (form) {
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
        "\nDate: " + travelDateStart.value +
        "\nTravelers: " + travelers.value
    );

    // ❌ If user cancels → STOP
    if (!confirmBooking) {
        event.preventDefault();
        return;
    }

    // ✅ If confirmed → form goes to PHP (no alert here)
});
}

// ================= VALIDATION =================

function validateForm() {
    let valid = true;

    document.querySelectorAll(".error").forEach(e => e.innerText = "");

    if (destination.value === "") {
        showError(destination, "Select destination");
        valid = false;
    }

    let today = new Date().toISOString().split("T")[0];
    if (travelDateStart.value === "" || travelDateStart.value < today) {
        showError(travelDateStart, "Select today or a future date");
        valid = false;
    }

    if (travelers.value === "" || travelers.value <= 0 || travelers.value > 1000) {
        showError(travelers, "Enter valid number of travelers");
        valid = false;
    }

    return valid;
}

// ================= ERROR DISPLAY =================

function showError(input, message) {
    input.nextElementSibling.innerText = message;
}
