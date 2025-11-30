$(document).ready(function () {
    $('.course').each(function (index) {
        $(this).delay(150 * index).animate({
            opacity: 1,
        }, 400);
    });
});