$(document).ready(function () {

    $("#navbarSupportedContent a").removeClass("active");

    let uri = window.location.pathname;

    if (uri.includes("new")) {
        $("#newLink").addClass("active");
    } else if (uri.includes("register")) {
        $("#registerLink").addClass("active");
    } else if (uri.includes("login")) {
        $("#loginLink").addClass("active");
    } else {
        $("#homeLink").addClass("active");
    }
});

