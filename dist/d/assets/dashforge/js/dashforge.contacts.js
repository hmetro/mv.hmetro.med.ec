$(function() {
    "use strict";

    $('[data-toggle="tooltip"]').tooltip();
    // set active contact from list to show in desktop view by default
    if (window.matchMedia("(min-width: 992px)").matches) {
        $(".contact-list .media:first-of-type").addClass("active");
    }
    const contactSidebar = new PerfectScrollbar(".contact-sidebar-body", {
        suppressScrollX: true,
    });
    new PerfectScrollbar(".contact-content-body", {
        suppressScrollX: true,
    });
    new PerfectScrollbar(".contact-content-sidebar", {
        suppressScrollX: true,
    });
    $(".contact-navleft .nav-link").on("shown.bs.tab", function(e) {
        contactSidebar.update();
    });

    // going back to contact list
    // for mobile interaction only
    $("#contactContentHide").on("click touch", function(e) {
        e.preventDefault();
        $("body").removeClass("contact-content-show contact-options-show");
        $("body").addClass("contact-content-visible");
        $("#mainMenuOpen").removeClass("d-none");
        $(this).addClass("d-none");
    });
    $("#contactOptions").on("click", function(e) {
        e.preventDefault();
        $("body").toggleClass("contact-options-show");
    });
    $(window).resize(function() {
        $("body").removeClass("contact-options-show");
    });

    // Set conetnt logs
    $(".off-canvas-menu").on("click", function(e) {
        e.preventDefault();
        var target = $(this).attr("href");
        $(".content-logs").addClass("show");
    });

    $(".off-canvas .close").on("click", function(e) {
        e.preventDefault();
        $(this).closest(".off-canvas").removeClass("show");
    });

    $(document).on("click touchstart", function(e) {
        e.stopPropagation();

        // closing of sidebar menu when clicking outside of it
        if (!$(e.target).closest(".off-canvas-menu").length) {
            var offCanvas = $(e.target).closest(".off-canvas").length;
            if (!offCanvas) {
                $(".off-canvas.show").removeClass("show");
            }
        }
    });
});