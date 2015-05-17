jQuery(document).ready(function() {
    //Initialize overview-table as a DataTable
    jQuery('#overview_table').dataTable({
    	"order": [[ 5, "desc" ]]
    });
});