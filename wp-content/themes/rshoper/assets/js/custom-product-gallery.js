(function($) {
    $(document).ready(() => {
        function addImage() {
            const productImageFull = currentProductMainImage;
            $('.woocommerce-product-gallery figure').prepend(`
                <div data-thumb="${productImageFull.thumbnail}" class="woocommerce-product-gallery__image flex-active-slide">
                    <a href="${productImageFull.image_url}">
                        <img width="510" height="612" src="${productImageFull.image_url}" class="attachment-shop_single size-shop_single" alt="" title="14" />
                    </a>
                </div>
            `)
        }
        addImage();
        $('form.variations_form').on('wc_additional_variation_images_frontend_image_swap_done_callback', () => addImage());
    });
})(jQuery)
