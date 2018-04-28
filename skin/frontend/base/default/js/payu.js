PayU = Class.create();

PayU.prototype = {
    initialize: function() {
        jQuery('.payMethodEnable').on('click', '.payMethodLabel', function () {
            jQuery('.payMethod').removeClass('payMethodActive');
            jQuery(this).closest('.payMethod').addClass('payMethodActive');
            jQuery(this).prev().prop('checked', true);
        });
    }
};
