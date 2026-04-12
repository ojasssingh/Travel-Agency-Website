const subscriptionEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

function validateSubscriptionEmail(input, showFeedback = false) {
    const normalizedEmail = input.value.trim().toLowerCase();

    if (showFeedback || document.activeElement !== input) {
        input.value = normalizedEmail;
    }

    let message = "";

    if (normalizedEmail === "") {
        message = "Email is required.";
    } else if (normalizedEmail.length > 150) {
        message = "Email address must be 150 characters or fewer.";
    } else if (!subscriptionEmailPattern.test(normalizedEmail)) {
        message = "Enter a valid email address like name@email.com.";
    }

    input.setCustomValidity(message);

    if (showFeedback && message !== "") {
        input.reportValidity();
    }

    return message === "";
}

document.querySelectorAll(".subscribe-form").forEach((form) => {
    const emailInput = form.querySelector('input[name="email"]');

    if (!emailInput) {
        return;
    }

    emailInput.addEventListener("input", () => {
        validateSubscriptionEmail(emailInput);
    });

    emailInput.addEventListener("blur", () => {
        validateSubscriptionEmail(emailInput, true);
    });

    form.addEventListener("submit", (event) => {
        if (!validateSubscriptionEmail(emailInput, true)) {
            event.preventDefault();
        }
    });
});
