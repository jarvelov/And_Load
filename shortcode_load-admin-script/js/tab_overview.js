jQuery(document).ready(function() {
    //Initialize overview-table as a DataTable

    if(typeof(overviewSettings) != 'undefined' ) {
        jQuery('#overview_table').dataTable({
            "order": [[ overviewSettings['order_column'], overviewSettings['order_type'] ]]
        });
    }
});