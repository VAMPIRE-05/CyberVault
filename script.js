
const passwordInput = document.getElementById("password");
const meterFill = document.getElementById("meterFill");
const strengthText = document.getElementById("strengthText");

function checkPassword() {
    if (!passwordInput) return;

    const password = passwordInput.value;
    let score = 0;

    if (password.length >= 8) score++;
    if (password.length >= 12) score++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++;
    if (/\d/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    const levels = [
        { text: "Enter a sample password.", width: "0%" },
        { text: "Very weak", width: "20%" },
        { text: "Weak", width: "40%" },
        { text: "Moderate", width: "60%" },
        { text: "Strong", width: "80%" },
        { text: "Very strong", width: "100%" }
    ];

    if (password.length === 0) {
        meterFill.style.width = "0%";
        strengthText.textContent = levels[0].text;
        return;
    }

    const level = levels[score];
    meterFill.style.width = level.width;
    strengthText.textContent = "Strength: " + level.text;
}

if (passwordInput) {
    passwordInput.addEventListener("input", checkPassword);
}

const toggleButton = document.getElementById("togglePassword");

if (toggleButton && passwordInput) {
    toggleButton.addEventListener("click", () => {
        passwordInput.type =
            passwordInput.type === "password" ? "text" : "password";
    });
}

const generateButton = document.getElementById("generatePassword");

if (generateButton && passwordInput) {
    generateButton.addEventListener("click", () => {
        const chars =
            "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*";
        let password = "";

        const randomValues = new Uint32Array(16);
        crypto.getRandomValues(randomValues);

        for (const value of randomValues) {
            password += chars[value % chars.length];
        }

        passwordInput.value = password;
        passwordInput.type = "text";
        checkPassword();
    });
}

const copyButton = document.getElementById("copyResult");

if (copyButton) {
    copyButton.addEventListener("click", async () => {
        const result = document.querySelector(".result-panel pre");

        if (!result) return;

        try {
            await navigator.clipboard.writeText(result.innerText);
            copyButton.textContent = "Copied!";
        } catch {
            copyButton.textContent = "Copy unavailable";
        }
    });
}
