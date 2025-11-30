$(document).ready(function () {
    $(window).on('scroll', function () {
        $('.stat-number').each(function () {
            var element = $(this);
            var bottomOfWindow = $(window).scrollTop() + $(window).height();

            // Check if the element is visible
            if (bottomOfWindow > element.offset().top) {

                // Run animation
                element.prop('counter', 0).animate({
                    counter: element.attr('data-target')
                }, {
                    duration: 2000,
                    step: function (now) {
                        element.text(Math.ceil(now));
                    }
                });
            }
        });
    });

    // Trigger scroll once on load in case the elements are already visible
    $(window).trigger('scroll');
});