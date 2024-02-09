document.addEventListener('DOMContentLoaded', function() {
    if (typeof flash_sale_time_remaining !== 'undefined') {
        var countdownElement = document.getElementById('flash-sale-countdown-timer');

        // Update the countdown timer format here
        var countdownFormat = flash_sale_countdown_format || 'HH:mm:ss';
        // var endDate = new Date(Date.now() + flash_sale_time_remaining * 1000);
        var endDate = Date.now() + flash_sale_time_remaining * 1000;

        function updateCountdown() {
            var now = new Date().getTime();
            var distance = endDate - now;
            if (distance > 0) {
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdownElement.innerHTML = days.toString() + ' hari ' + hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
            } else {
                countdownElement.innerHTML = 'Waktu flash sale telah habis';
                // Add any actions you want to perform when the sale expires.
            }
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
});

jQuery(document).ready(function($) {
    // Function to update the discount table based on selected products
    function updateDiscountTable() {
        var selectedProductIds = $('.flash-sale-products-select').val() || [];
        var discountTable = $('.flash-sale-products-discount-table');

        discountTable.find('.flash-sale-product-row').each(function() {
            var productId = $(this).data('product-id');
            if (!selectedProductIds.includes(productId)) {
                $(this).remove();
            }
        });

        selectedProductIds.forEach(function(productId) {
            if (!discountTable.find('.flash-sale-product-row[data-product-id="' + productId + '"]').length) {
                var productName = $('.flash-sale-product-option[value="' + productId + '"]').text();
                var newRow = '<tr class="flash-sale-product-row" data-product-id="' + productId + '">' +
                    '<th scope="row">' + productName + '</th>' +
                    '<td><input type="number" name="flash_sale_products_discount[' + productId + ']" min="0" max="100" step="1" value="0" class="small-text" /> %</td>' +
                    '</tr>';
                discountTable.append(newRow);
            }
        });
    }

    $('.flash-sale-products-select').on('change', function() {
        updateDiscountTable();
    });

    updateDiscountTable();
});