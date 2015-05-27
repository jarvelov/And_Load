jQuery(document).ready(function() {
    //When print margin is disabled/enabled fade out/in print margin column setting
    jQuery('#editor_default_print_margin').on('change', function() {
        jQuery('#editor_default_print_margin_column_setting').fadeToggle(400)
    });

    //When tab size override is disabled/enabled fade out/in editor_default_tab_size_setting
    jQuery('#editor_default_tab_size_override_setting').on('change', function() {
        jQuery('#editor_default_tab_size_setting').fadeToggle(400)
    });
});