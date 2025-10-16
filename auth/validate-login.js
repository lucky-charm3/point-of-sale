document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const usernameInput = document.querySelector("input[name='username']");
    const passwordInput = document.querySelector("input[name='password']");

    function showError(input, message) {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains("error-msg-js")) {
            existingError.remove();
        }

        const p = document.createElement("p");
        p.classList.add("error-msg-js");
        p.style.color = "#DC3545";
        p.style.fontSize = "14px";
        p.style.fontWeight=100;
        p.style.margin = "5px 0 0 0";
        p.textContent = message;
        input.parentNode.insertBefore(p, input.nextSibling);
    }

    function clearError(input) {
        const existingError = input.nextElementSibling;
        if (existingError && existingError.classList.contains("error-msg-js")) {
            existingError.remove();
        }
    }

    usernameInput.addEventListener("input", () => clearError(usernameInput));
    passwordInput.addEventListener("input", () => clearError(passwordInput));

    form.addEventListener("submit", function(e) {
        let hasError = false;

        if(usernameInput.value.trim() === "") {
            showError(usernameInput, "Username cannot be empty.");
            hasError = true;
        } else if(usernameInput.value.length < 3) {
            showError(usernameInput, "Username must be at least 3 characters.");
            hasError = true;
        }

        if(passwordInput.value.trim() === "") {
            showError(passwordInput, "Password cannot be empty.");
            hasError = true;
        } else if(passwordInput.value.length < 6) {
            showError(passwordInput, "Password must be at least 6 characters.");
            hasError = true;
        }

        if(hasError) {
            e.preventDefault();
        }
    });
});
