function scrollErrorIntoView() {
    params = window.location.search.split('&');
    for (var i = params.length - 1; i >= 0; i--) {
        if( params[i].indexOf('error_id') >= 0 ) {
            error_id = params[i].split('=')[1];
            error_position = jQuery('#' + error_id).offset();
            error_position['top'] -= error_position['top'] / 100;
            jQuery('html, body').animate({scrollTop: error_position.top}, "slow");

            return true;
        }
    }

    return false; //no scroll was done
}

jQuery(document).ready(function() {
    scrollErrorIntoView();
});