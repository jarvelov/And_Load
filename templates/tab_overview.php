<?php $this->layout('template'); ?>

<h1>Overview</h1>

    <div class="row">
        <?php
            if(sizeof($files) > 0 ):
        ?>
        <div id="overview_container" class="col-xs-12">

            <p id="container_help_text"><span id="help-title">Tip!</span><span id="help_text">Click the name of the file in the table to view/edit it.</span></p>';

            <?php
                $options_default = get_option( 'and_load_default_options' );

                $overview_default_table_order_column = $options_default['overview_default_table_order_column'];
                $overview_table_order_type = $options_default['overview_default_table_sort'];

            ?>
            <table class="table table-striped table-bordered table-hover datatables" data-ajax='{"url":"/wp-json/v2/and_load/files"}' data-order='[' . $overview_default_table_order_column . ',' . $overview_table_order_type . ']'>
                <thead>
                    <?php
                    foreach ($header as $value) {
                        echo '<th class="' . $value . '">' . ucfirst($value) . '</th>';
                    }
                    ?>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>2</td>
                        <td>3</td>
                        <td>4</td>
                        <td>5</td>
                        <td>6</td>
                    </tr>
                </tbody>
            </table>

        <?php
            else:
        ?>
            <div id="overview_get_started">
                <h2>No scripts or styles created yet!</h2>
                <p>Add a script with the <strong><a href="?page=and_load&amp;tab=tab_edit">"Editor"</a></strong> tab above.</p>
                <p>For more info and help check out the <strong><a href="?page=and_load&amp;tab=tab_help">Help</a></strong> tab</p>
            </div>
        <?php
            endif;
        ?>
        </div>
    </div>
