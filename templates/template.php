<div class="wrap">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="<?php echo ($active_tab == 'tab_overview') ? 'active' : '' ?>">
            <a href="#tab_overview" class="tab_overview" data-toggle="tab">
                <span class="fa fa-list"></span> Overview</span>
            </a>
        </li>
        <li role="presentation">
            <a href="#" data-toggle="tab">
                <span class="fa fa-list"></span> Files</span>
            </a>
        </li>
        <li role="presentation">
            <a href="#" data-toggle="tab">
                <span class="fa fa-list"></span> Packages</span>
            </a>
        </li>
        <li role="presentation" class="<?php echo ($active_tab == 'tab_settings') ? 'active' : '' ?>">
            <a href="#tab_settings" class="tab_settings">
                <span class="fa fa-cogs"></span> Settings
            </a>
        </li>
        <li role="presentation" class="<?php echo ($active_tab == 'tab_editor') ? 'active' : '' ?>">
            <a href="#tab_editor" class="tab_editor">
                <span class="fa fa-pencil"></span> Editor</a>
        </li>
        <li role="presentation" class="<?php echo ($active_tab == 'tab_help') ? 'active' : '' ?>">
            <a href="#tab_help" class="tab_help">
                <span class="fa fa-question"></span> Help
            </a>
        </li>
    </ul>

    <?= $this->section('content'); ?>
</div><!-- end wrap -->