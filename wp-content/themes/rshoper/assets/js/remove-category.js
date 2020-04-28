(function($) {
    $(document).ready(() => {
        const btnHtml = `
            <a href="#" 
                id="categoryCleanupBtn" 
                class="page-title-action" 
                title="Remove Pending category from products"
            >Category cleanup</a>
        `;
        const afterBtnElem = $('#wp__notice-list-uncollapsed');
        afterBtnElem.before(btnHtml);

        const categoryCleanupBtn = $('#categoryCleanupBtn');
        categoryCleanupBtn.css({
            'top': '9px',
            'float': 'right'
        });
        categoryCleanupBtn.on('click', event => {
            event.preventDefault();

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'category_cleanup'
                },
                success: function(data){
                    window.location.reload();
                }
            });
            
        });
    });
})(jQuery)