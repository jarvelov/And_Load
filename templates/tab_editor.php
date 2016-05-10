<?php $this->layout('template', array('active_tab' => 'tab_editor')); ?>

<div class="row">
    <div class="col-xs-12">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
                <a href="#" data-target="#editor-tab" role="tab" data-toggle="tab"><span class="fa fa-pencil"></span> File: $name</a>
            </li>
            <li role="presentation">
                <a href="#" data-target="#info-tab" role="tab" data-toggle="tab"><span class="fa fa-info"></span> Info</a>
            </li>
            <li role="presentation">
                <a href="#" data-target="#options-tab" role="tab" data-toggle="tab"><span class="fa fa-cog"></span> Options</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div role="tabpanel" class="tab-pane active fade in" id="editor">
                
            </div>
            <div role="tabpanel" class="tab-pane fade" id="options-tab">
                <div class="row">
                    <div class="col-xs-6">
                        <form method="post" action="options.php" class="form">
                            <div class="input-group">
                              <input type="text" class="form-control" aria-label="...">
                              <div class="input-group-btn">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action <span class="caret"></span></button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                  <li><a href="#">Rename</a></li>
                                </ul>
                              </div><!-- /btn-group -->
                            </div><!-- /input-group -->
                            <button id="delete" class="btn btn-danger pull-right" name="delete">
                                <span class="fa fa-times"></span> Delete file
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div role="tabpanel" class="tab-pane fade" id="info-tab">
                <div class="row">
                    <div class="col-xs-12">
                        <h4>Info</h4>
                    </div>
                    <div class="col-xs-12">
                        <table class="table table-striped table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Created: </td>
                                    <td>2016-01-01</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

    <button name="submit" id="submit" class="btn btn-success">
        <span class="fa fa-check"></span> Save file
    </button>
</div>