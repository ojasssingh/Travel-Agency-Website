function getValidationError(input) {
    const group = input.closest(".input-group") || input.closest(".field-group") || input.parentElement;
    if (!group) return null;

    let error = group.querySelector(".error-msg");
    if (!error) {
        error = document.createElement("div");
        error.className = "error-msg";
        group.appendChild(error);
    }

    return error;
}

function showError(input, message) {
    const error = getValidationError(input);
    input.classList.add("error");
    if (error) {
        error.innerText = message;
        error.style.display = "block";
    }
}

function clearError(input) {
    const error = getValidationError(input);
    input.classList.remove("error");
    if (error) {
        error.innerText = "";
        error.style.display = "none";
    }
}

function isFieldVisible(input) {
    return !!(input.offsetWidth || input.offsetHeight || input.getClientRects().length);
}

function validateInput(input) {
    if (input.disabled || input.readOnly || input.type === "hidden" || input.type === "checkbox") {
        return true;
    }

    if (!isFieldVisible(input)) {
        clearError(input);
        return true;
    }

    const name = input.name || input.id || "";
    const value = input.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const lettersRegex = /^[A-Za-z ]{2,50}$/;

    if (input.required && value === "") {
        showError(input, "This field is required");
        return false;
    }

    if (value === "" && !input.required) {
        clearError(input);
        return true;
    }

    if (name === "username") {
        if (!/^[a-z ]{3,30}$/.test(value)) {
            showError(input, "3-30 chars, lowercase only, spaces allowed");
            return false;
        }
    } else if (name === "name" || name === "billing_name") {
        if (!lettersRegex.test(value)) {
            showError(input, "Use 2-50 letters and spaces only");
            return false;
        }
    } else if (name === "email" || name === "billing_email") {
        if (!emailRegex.test(value)) {
            showError(input, "Invalid email format");
            return false;
        }
    } else if (name === "password") {
        if (value.length < 6) {
            showError(input, "Min 6 characters required");
            return false;
        }
    } else if (name === "phone" || name === "billing_phone") {
        if (!/^[0-9]{10}$/.test(value)) {
            showError(input, "Enter exactly 10 digits");
            return false;
        }
    } else if (name === "city" || name === "state") {
        if (!lettersRegex.test(value)) {
            showError(input, "Use letters and spaces only");
            return false;
        }
    } else if (name === "dob") {
        const dob = new Date(value);
        const minAgeDate = new Date();
        minAgeDate.setFullYear(minAgeDate.getFullYear() - 16);
        if (Number.isNaN(dob.getTime()) || dob > minAgeDate) {
            showError(input, "You must be at least 16 years old");
            return false;
        }
    } else if (name === "travelers") {
        const travelers = parseInt(value, 10);
        if (Number.isNaN(travelers) || travelers < 1 || travelers > 20) {
            showError(input, "Enter 1 to 20 travelers");
            return false;
        }
    } else if (name === "travel_date_start") {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const start = new Date(value);
        if (Number.isNaN(start.getTime()) || start < today) {
            showError(input, "Select today or a future date");
            return false;
        }
    } else if (name === "travel_date_end") {
        const startInput = input.form ? input.form.querySelector('[name="travel_date_start"]') : null;
        if (startInput && startInput.value && value) {
            const start = new Date(startInput.value);
            const end = new Date(value);
            if (Number.isNaN(end.getTime()) || end <= start) {
                showError(input, "End date must be after start date");
                return false;
            }
        }
    } else if (name === "accommodation_type") {
        if (!["Agency", "Self"].includes(value)) {
            showError(input, "Select accommodation type");
            return false;
        }
    } else if (name === "card_number") {
        const digits = value.replace(/\D/g, "");
        if (digits.length < 12 || digits.length > 19) {
            showError(input, "Enter a valid card number");
            return false;
        }
    } else if (name === "card_name") {
        if (!lettersRegex.test(value)) {
            showError(input, "Enter card holder name");
            return false;
        }
    } else if (name === "card_expiry") {
        if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(value)) {
            showError(input, "Use MM/YY format");
            return false;
        }
    } else if (name === "card_cvc") {
        if (!/^[0-9]{3,4}$/.test(value)) {
            showError(input, "Enter 3 or 4 digits");
            return false;
        }
    } else if (name === "upi_id") {
        if (!/^[a-z0-9._-]{2,}@[a-z]{2,}$/i.test(value)) {
            showError(input, "Enter a valid UPI ID");
            return false;
        }
    } else if (name === "bank_name") {
        if (value === "") {
            showError(input, "Select your bank");
            return false;
        }
    }

    clearError(input);
    return true;
}

function validateDatePair(form) {
    const startDate = form.querySelector('[name="travel_date_start"]');
    const endDate = form.querySelector('[name="travel_date_end"]');
    if (!startDate || !endDate) return true;

    validateInput(startDate);
    return validateInput(endDate);
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("form").forEach((form) => {
        const inputs = form.querySelectorAll("input, select, textarea");

        form.querySelectorAll(".method-tab").forEach((tab) => {
            tab.addEventListener("click", () => {
                inputs.forEach((input) => {
                    if (!isFieldVisible(input)) {
                        clearError(input);
                    }
                });
            });
        });

        inputs.forEach((input) => {
            input.addEventListener("input", () => validateInput(input));
            input.addEventListener("change", () => {
                validateInput(input);
                if (input.name === "travel_date_start" || input.name === "travel_date_end") {
                    validateDatePair(form);
                }
            });
        });

        form.addEventListener("submit", (event) => {
            let isValid = true;
            inputs.forEach((input) => {
                if (!validateInput(input)) {
                    isValid = false;
                }
            });

            if (!validateDatePair(form)) {
                isValid = false;
            }

            if (!isValid) {
                event.preventDefault();
                const firstError = form.querySelector(".error");
                if (firstError) firstError.focus();
            }
        });
    });
});
