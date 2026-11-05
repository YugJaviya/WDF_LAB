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
