jQuery(document).ready(function() {
    //Initialize overview-table as a DataTable

    if(typeof(overviewSettings) != 'undefined' ) {
        jQuery('#overview_table').dataTable({
            "order": [[ overviewSettings['order_column'], overviewSettings['order_type'] ]]
        });

        //add sorting_[order_type] to thead first row cell [order_column]
        var order_column_thead = jQuery('#overview_table > thead')[0].rows[0].cells[overviewSettings['order_column']]
        jQuery(order_column_thead).addClass('sorting_' + overviewSettings['order_type'] );
    }
});