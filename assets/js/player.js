$(document).ready(function () {
    $('.lesson-item.locked-item').on('click', function (e) {
        e.preventDefault(); // Stop the link from working
    });

    // Mark as Complete Button
    $('#mark-complete').on('click', function (e) {
        $(this).text('Completed ✔').css('opacity', '0.7');

        // Visual update for the checkbox
        $('.lesson-item.active .checkbox')
            .css('background-color', '#02413b')
            .css('border-color', '#02413b');

        // Save state to LocalStorage
        var currentPage = window.location.pathname;
        localStorage.setItem(currentPage + '-completed', 'true');
    });

    // Check saved state on page load
    var currentPage = window.location.pathname;
    if (localStorage.getItem(currentPage + '-completed') === 'true') {
        $('#mark-complete').text('Completed ✔').css('opacity', '0.7');
        
        $('.lesson-item.active .checkbox')
            .css('background-color', '#02413b')
            .css('border-color', '#02413b');
    }
});