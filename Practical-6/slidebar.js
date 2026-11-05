// Practical 4: accessible DOM interactions for the Student Portal.
(() => {
    const body = document.body;
    const menuButton = document.getElementById("menuButton");
    const closeButton = document.getElementById("closeButton");
    const sidebar = document.getElementById("sidebar");

    // Restore the saved theme before wiring its control.
    const themeButtons = document.querySelectorAll(".theme-toggle");
    let savedTheme = "light";
    try { savedTheme = localStorage.getItem("studentPortalTheme") || "light"; } catch (error) { /* Storage can be disabled. */ }
    body.classList.toggle("dark-theme", savedTheme === "dark");
    const updateThemeButtons = () => themeButtons.forEach((button) => {
        const isDark = body.classList.contains("dark-theme");
        button.setAttribute("aria-pressed", String(isDark));
        button.setAttribute("aria-label", isDark ? "Switch to light theme" : "Switch to dark theme");
        button.textContent = isDark ? "☀️ Light mode" : "🌙 Dark mode";
    });
    updateThemeButtons();
    themeButtons.forEach((button) => button.addEventListener("click", () => {
        const isDark = body.classList.toggle("dark-theme");
        try { localStorage.setItem("studentPortalTheme", isDark ? "dark" : "light"); } catch (error) { /* Theme still works for this visit. */ }
        updateThemeButtons();
    }));

    // On narrow screens, start with the navigation collapsed.
    if (sidebar && window.matchMedia("(max-width: 700px)").matches) body.classList.add("sidebar-closed");
    const setSidebar = (isOpen) => {
        if (!sidebar) return;
        body.classList.toggle("sidebar-closed", !isOpen);
        sidebar.inert = !isOpen;
        sidebar.setAttribute("aria-hidden", String(!isOpen));
        if (menuButton) menuButton.setAttribute("aria-expanded", String(isOpen));
        if (isOpen && closeButton) closeButton.focus();
        else if (menuButton) menuButton.focus();
    };
    if (sidebar) {
        const initiallyOpen = !body.classList.contains("sidebar-closed");
        sidebar.inert = !initiallyOpen;
        sidebar.setAttribute("aria-hidden", String(!initiallyOpen));
    }
    menuButton?.addEventListener("click", () => setSidebar(body.classList.contains("sidebar-closed")));
    closeButton?.addEventListener("click", () => setSidebar(false));
    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && sidebar && !body.classList.contains("sidebar-closed") && window.matchMedia("(max-width: 700px)").matches) setSidebar(false);
    });

    // FAQ accordion: update the visible answer and its accessible state together.
    document.querySelectorAll(".faq-question").forEach((button) => button.addEventListener("click", () => {
        const answer = document.getElementById(button.getAttribute("aria-controls"));
        const expanded = button.getAttribute("aria-expanded") === "true";
        button.setAttribute("aria-expanded", String(!expanded));
        if (answer) answer.hidden = expanded;
    }));

    // Content slider with wraparound navigation.
    const slides = Array.from(document.querySelectorAll(".event-slide"));
    if (slides.length) {
        let current = 0;
        const position = document.getElementById("slidePosition");
        const showSlide = (index) => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                slide.hidden = i !== current;
                slide.setAttribute("aria-label", `${i + 1} of ${slides.length}`);
            });
            if (position) position.textContent = `${current + 1} of ${slides.length}`;
        };
        document.getElementById("previousSlide")?.addEventListener("click", () => showSlide(current - 1));
        document.getElementById("nextSlide")?.addEventListener("click", () => showSlide(current + 1));
        showSlide(0);
    }

    // Native dialog supplies Escape handling and keeps focus within the modal.
    const dialog = document.getElementById("eventDialog");
    if (dialog instanceof HTMLDialogElement) {
        document.querySelectorAll(".slide-details").forEach((button) => button.addEventListener("click", () => {
            document.getElementById("dialogTitle").textContent = button.dataset.eventTitle || "Event details";
            document.getElementById("dialogDate").textContent = button.dataset.eventDate || "To be announced";
            document.getElementById("dialogVenue").textContent = button.dataset.eventVenue || "To be announced";
            dialog.showModal();
        }));
        document.getElementById("closeEventDialog")?.addEventListener("click", () => dialog.close());
        dialog.addEventListener("click", (event) => { if (event.target === dialog) dialog.close(); });
    }

    // Dismiss the dashboard reminder without removing unrelated notifications.
    document.getElementById("dismissNotice")?.addEventListener("click", () => {
        document.getElementById("dashboardNotice")?.remove();
    });
})();

// Practical 5: real-time registration validation and accessible field feedback.
(() => {
    const form = document.getElementById("registrationForm");
    if (!form) return;

    const status = document.getElementById("registrationStatus");
    const touched = new Set();
    let submitted = false;

    const errorIds = {
        studentName: "studentNameError",
        studentEmail: "studentEmailError",
        mobileNumber: "mobileNumberError",
        newPassword: "passwordError",
        confirmPassword: "confirmPasswordError",
        course: "courseError",
        studyYear: "studyYearError",
        gender: "genderError",
        acceptTerms: "acceptTermsError"
    };

    const valueOf = (name) => {
        const field = form.elements.namedItem(name);
        return field && "value" in field ? field.value.trim() : "";
    };

    const passwordChecks = (password) => ({
        longEnough: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        symbol: /[^A-Za-z0-9]/.test(password)
    });

    const updateStrength = () => {
        const password = document.getElementById("newPassword").value;
        const bar = document.getElementById("strengthBar");
        const label = document.getElementById("passwordStrength");
        const checks = Object.values(passwordChecks(password));
        const score = checks.filter(Boolean).length;
        const level = !password ? "not entered" : score <= 2 ? "weak" : score <= 4 ? "medium" : "strong";
        const colors = { "not entered": "#9aa8b5", weak: "#b42318", medium: "#b7791f", strong: "#16803c" };
        bar.style.width = `${score * 20}%`;
        bar.style.backgroundColor = colors[level];
        label.textContent = `Password strength: ${level}`;
        label.setAttribute("aria-label", `Password strength: ${level}, ${score} of 5 checks passed`);
        return checks.every(Boolean);
    };

    const messageFor = (name) => {
        const value = valueOf(name);
        switch (name) {
            case "studentName":
                return /^[\p{L}][\p{L}\p{M} .'-]{1,49}$/u.test(value)
                    ? "" : "Enter a name of 2–50 characters using letters, spaces, apostrophes, periods, or hyphens.";
            case "studentEmail":
                return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(value) && document.getElementById(name).validity.valid
                    ? "" : "Enter a valid email address, for example student@example.com.";
            case "mobileNumber":
                return /^[6-9][0-9]{9}$/.test(value)
                    ? "" : "Enter a valid 10-digit Indian mobile number starting with 6, 7, 8, or 9.";
            case "newPassword":
                return updateStrength() ? "" : "Use at least 8 characters with uppercase, lowercase, a number, and a symbol.";
            case "confirmPassword":
                return value && value === document.getElementById("newPassword").value
                    ? "" : "The passwords do not match.";
            case "course":
            case "studyYear":
                return value ? "" : "Please select an option.";
            case "gender":
                return form.querySelector('input[name="gender"]:checked') ? "" : "Please select a gender option.";
            case "acceptTerms":
                return document.getElementById(name).checked ? "" : "You must accept the terms to continue.";
            default:
                return "";
        }
    };

    const validateField = (name) => {
        const message = messageFor(name);
        const shouldShow = submitted || touched.has(name);
        const error = document.getElementById(errorIds[name]);
        const fields = name === "gender"
            ? Array.from(form.querySelectorAll('input[name="gender"]'))
            : [document.getElementById(name)];

        fields.forEach((field) => {
            if (field) {
                if (shouldShow && message) field.setAttribute("aria-invalid", "true");
                else field.removeAttribute("aria-invalid");
            }
        });
        if (error) error.textContent = shouldShow ? message : "";
        return !message;
    };

    Object.keys(errorIds).forEach((name) => {
        const fields = name === "gender"
            ? Array.from(form.querySelectorAll('input[name="gender"]'))
            : [document.getElementById(name)];
        fields.filter(Boolean).forEach((field) => {
            field.addEventListener("input", () => {
                touched.add(name);
                validateField(name);
                if (name === "newPassword") {
                    if (touched.has("confirmPassword")) validateField("confirmPassword");
                }
            });
            field.addEventListener("change", () => {
                touched.add(name);
                validateField(name);
            });
        });
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();
        submitted = true;
        status.textContent = "";
        const names = Object.keys(errorIds);
        const valid = names.map(validateField);
        if (valid.includes(false)) {
            status.textContent = "Please correct the highlighted fields and submit again.";
            const firstInvalid = form.querySelector('[aria-invalid="true"]');
            if (firstInvalid) firstInvalid.focus();
            return;
        }
        status.textContent = "Registration details are valid. Your form is ready to submit.";
    });

    form.addEventListener("reset", () => {
        window.setTimeout(() => {
            submitted = false;
            touched.clear();
            Object.values(errorIds).forEach((id) => { document.getElementById(id).textContent = ""; });
            form.querySelectorAll("[aria-invalid]").forEach((field) => field.removeAttribute("aria-invalid"));
            status.textContent = "";
            updateStrength();
        }, 0);
    });
})();
