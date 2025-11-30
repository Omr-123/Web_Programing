$(document).ready(function () {
    $('.hero-content').animate({ opacity: 1, marginTop: '0px' }, 800);

    $('.sidebar .box').each(function(i) {
        $(this).delay(400 + (i * 200)).animate({ opacity: 1 }, 600);
    });

    $('.learn-item').each(function(i) {
        $(this).delay(500 + (i * 100)).animate({ opacity: 1 }, 500);
    });

    $('.curriculum-item').each(function(i) {
        $(this).delay(800 + (i * 150)).animate({ 
            opacity: 1, 
            marginTop: '20px'
        }, 600);
    });

});