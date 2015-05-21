function getParamError() {
    params = window.location.search.split('&');
    for (var i = params.length - 1; i >= 0; i--) {
        if( params[i].indexOf('error_id') >= 0 ) {
            error_id = params[i].split('=')[1];

            return error_id;
        }
    }

    return false; //no error param was found
}

function scrollErrorIntoView(error_id) {
    error_position = jQuery('#' + error_id).offset();
    error_position['top'] -= error_position['top'] / 100;

    jQuery('html, body').animate({scrollTop: error_position.top}, "slow");    
}

function toggleErrorList() {
    if ( jQuery('#show_hide_error_list > span').hasClass('glyphicon-chevron-down') ) {
        jQuery('#show_hide_error_list > span').removeClass('glyphicon-chevron-down');
        jQuery('#show_hide_error_list > span').addClass('glyphicon-chevron-up');
        jQuery('#shortcode_load_error_list').show();
    } else {
        jQuery('#show_hide_error_list > span').removeClass('glyphicon-chevron-up');
        jQuery('#show_hide_error_list > span').addClass('glyphicon-chevron-down');
        jQuery('#shortcode_load_error_list').hide();
    }
}

jQuery('#show_hide_error_list').on('click', function() {
    toggleErrorList();
});

jQuery(document).ready(function() {
    error_id = getParamError();

    if(error_id) {
        toggleErrorList();
        scrollErrorIntoView(error_id);
        jQuery('#' + error_id).addClass('bg-danger');
    }
});