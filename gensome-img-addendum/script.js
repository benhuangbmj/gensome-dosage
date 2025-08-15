jQuery(document).ready(function ($) {
    $('.select-addendum').on('click', function (e) {
        e.preventDefault();

        const button = $(this);
        const productId = button.data('product-id');
        const row = button.closest('tr');

        // Open the WordPress Media Library
        const mediaFrame = wp.media({
            title: 'Select Addendum Image',
            button: {
                text: 'Use this image',
            },
            multiple: false,
        });

        mediaFrame.on('select', function () {
            const attachment = mediaFrame.state().get('selection').first().toJSON();
            const imageUrl = attachment.url;

            // Send AJAX request to save the image
            $.ajax({
                url: gensomeImgAddendum.ajax_url,
                method: 'POST',
                data: {
                    action: 'save_addendum_image',
                    nonce: gensomeImgAddendum.nonce,
                    product_id: productId,
                    image_url: imageUrl,
                },
                success: function (response) {
                    if (response.success) {
                        alert(response.data.message);
                        // Update the Selected Image URL column
                        row.find('.selected-image-url').text(imageUrl);
                    } else {
                        alert(response.data.message);
                    }
                },
                error: function () {
                    alert('An error occurred while saving the addendum.');
                },
            });
        });

        mediaFrame.open();
    });
});