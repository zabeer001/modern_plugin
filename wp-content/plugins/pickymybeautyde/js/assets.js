document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".wp-block-group").forEach(function (el) {
        el.classList.remove("has-global-padding", "is-layout-constrained", "wp-block-group-is-layout-constrained");
    });
   
});



