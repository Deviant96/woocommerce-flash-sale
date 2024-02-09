jQuery(document).ready(function($) {
    // Function to update the discount table based on selected products
    function updateDiscountTable() {
        var selectedProductIds = $('.flash-sale-products-select').val() || [];

        // Convert the selected product IDs to integers
        selectedProductIds = selectedProductIds.map(function(id) {
            return parseInt(id, 10);
        });

        // Show or hide rows based on selected products
        $('.flash-sale-product-row').each(function() {
            var productId = $(this).data('product-id');
            var isProductSelected = selectedProductIds.includes(productId);

            if (isProductSelected) {
                $(this).show();
            } else {
                $(this).hide();
                // Reset the percentage value to 0 for products that are not selected
                $(this).find('input[type="number"]').val(0);
            }
        });
    }

    $('.flash-sale-products-select').on('change', function() {
        updateDiscountTable();
    });

    updateDiscountTable();
});


jQuery(document).ready(function($) {
    var endDateField = $('#flash_sale_end_date');
    var startDateField = $('#flash_sale_start_date');

    var errorMessage = '<style>.warning-message{color:red;font-style:italic;display:none}</style>';
    endDateField.after(errorMessage);

    // Validate end date
    endDateField.change(function() {
        var startDate = new Date(startDateField.val());
        var endDate = new Date($(this).val());

        if (endDate < startDate) {
            $(this).val('');
            $(this).siblings('.warning-message').show();
        } else {
            $(this).siblings('.warning-message').hide();
        }
    });

    // Validate discount percentage
    $('.flash_sale_percentage_discount').change(function() {
        var percentageDiscount = parseInt($(this).val());

        if (isNaN(percentageDiscount) || percentageDiscount < 0 || percentageDiscount > 100) {
            $(this).siblings('.warning-message').show();
            $(this).val(0);
        } else {
            $(this).siblings('.warning-message').hide();
        }

    });
});


jQuery(document).ready(function($) {
    $('#exclude_flash_sale_products').change(function() {
        if ($(this).is(':checked')) {
            $('#restrict_to_flash_sale_products').prop('checked', false);
        }
    });

    $('#restrict_to_flash_sale_products').change(function() {
        if ($(this).is(':checked')) {
            $('#exclude_flash_sale_products').prop('checked', false);
        }
    });
});