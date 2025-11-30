$(document).ready(function () {
    // Handle Remove Button Click
    $('.remove-btn').on('click', function (e) {
        e.preventDefault();

        var $item = $(this).closest('.cart-item');
        $item.fadeOut(400, function () {
            // Remove from DOM
            $(this).remove();

            // Recalculate totals
            updateCartTotal();
            updateItemCount();
        });
    });

    // Function to Calculate Totals
    function updateCartTotal() {
        var subtotal = 0;

        // Loop through visible price elements
        $('.cart-item:visible .price').each(function () {
            var price = parseFloat($(this).data('price'));
            subtotal += price;
        });

        var tax = subtotal * 0.10;
        var total = subtotal + tax;

        $('#subtotal-price').text('$' + subtotal.toFixed(2));
        $('#tax-price').text('$' + tax.toFixed(2));
        $('#total-price').text('$' + total.toFixed(2));
    }

    // Function to Update Item Count Text
    function updateItemCount() {
        var count = $('.cart-item:visible').length;
        var $title = $('.cart-title p');
        var $container = $('.cart-container');

        if (count === 1) {
            $title.text(count + " Course in Cart");
        } else {
            $title.text(count + " Courses in Cart");
        }

        if (count === 0) {
            $container.html('<p style="padding:40px; text-align:center;">Your cart is empty.</p>');
        }
    }

});