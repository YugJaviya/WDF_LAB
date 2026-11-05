function toggleSidebar() {

    let sidebar = document.getElementById("sidebar");

    let main = document.getElementById("mainContent");

    let menuButton = document.getElementById("menuButton");

    let footer = document.querySelector("footer");


    sidebar.classList.toggle("hide");

    main.classList.toggle("full");

    footer.classList.toggle("full");


    /*
       If sidebar is hidden,
       show the menu button.
    */

    if (sidebar.classList.contains("hide")) {

        menuButton.style.display = "block";

    }

    else {

        menuButton.style.display = "none";

    }

}