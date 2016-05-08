<div id="and_load_help">
    <h4>
        Help and how-to
    </h4>
    <div id="and_load_donation_container">
        <p>
            If you like this plugin then consider donating to support it\'s development. It would mean a lot!
        </p>
        <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=tobias%2ejarvelov%40live%2ese&lc=US&item_name=And%20Load%20Wordpress%20Plugin&currency_code=USbn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted">
            <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" />
        </a>
    </div>
    <div id="and_load_help_getting_started">
        <p>
            Hello there! Cool that you\'re using
            <strong>
                And Load!
            </strong>
        </p>
        <p>
            More examples and documentation are available on the And Load project\'s
            <span class="external_link glyphicon glyphicon-new-window">
            </span>
            <a target="_blank" href="https://github.com/jarveloAnd_Load/blob/master/README.md">
                GitHub
            </a> page.
        </p>
        <p>
            If you need support with the plugin check out the plugin\'s
            <span class="external_link glyphicon glyphicon-new-window">
            </span>
            <a target="_blank" href="https://wordpress.org/support/plugin/and_load" strong>
                support page
            </strong>
        </a>
        </p>
        <p>
            If you think you have found a bug please file a ticket on the project\'s GitHub page and I\'ll look into it as soon as possible
        </p>
    </div>

    <div id="and_load_help_credits" class="and_load_help_section">';
        <p>
            This plugin would not have been possible without the following projects. Much kudos to everyone in the world contributing to the open source software community!
        </p>

        <?php
            $credits = [
                [
                    'name' => 'ace',
                    'project' =>  'http://ace.c9.io',
                    'license' => [
                        'url' => 'http://github.com/ajaxorg/ace/blob/master/LICENSE',
                        'name' => 'BSD'
                    ]
                ], [
                    'name' => 'datatables',
                    'project' =>  'http://www.datatables.net',
                    'license' => [
                        'url' => 'http://www.datatables.net/license/mit',
                        'name' => 'MIT'
                    ]
                ], [
                    'name' => 'minify',
                    'project' => 'http://github.com/matthiasmullie/minify',
                    'license' => [
                        'url' => 'http://github.com/matthiasmullie/minify/blob/master/LICENSE',
                        'name' => 'MIT'
                    ]
                ], [
                    'name' => 'path converter',
                    'project' => 'http://github.com/matthiasmullie/path-converter',
                    'license' => [
                        'url' => 'http://github.com/matthiasmullie/path-converter/blob/master/LICENSE',
                        'name' => 'MIT'
                    ]
                ], [
                    'name' => 'font awesome',
                    'project' => 'http://font-awesome.io',
                    'license' => [
                        'url' => 'http://fortawesome.github.io/Font-Awesome/license',
                        'name' => 'SIL OFL 1.1, MIT'
                    ]
                ], [
                    'name' => 'bootstrap',
                    'project' => 'http://getbootstrap.com',
                    'license' => [
                        'url' => 'http://github.com/twbs/bootstrap/blob/master/LICENSE',
                        'name' => 'MIT'
                    ]
                ], [
                    'name' => 'bootbox',
                    'project' => 'http://bootboxjs.com',
                    'license' => [
                        'url' => 'http://github.com/makeusabrew/bootbox/blob/master/LICENSE.md',
                        'name' => 'MIT'
                    ]
                ]
            ];
        ?>
    </div>

    <div id="and_load_help_debug" class="and_load_help_section">
        <button id="show_hide_error_list" class="btn btn-block btn-default"><span class="glyphicon glyphicon-collapse-down"></span> Error codes and messages</button>
        <ul id="and_load_error_list">
            <?php

                // TODO: move $errors var to a template file and use this file as a layout
                $errors = [
                    [
                        'error'    => 'Could not save file to Wordpress\' <em>uploads</em> folder.',
                        'solution' => 'Check permissions for the web server to write to the wp-content/uploads directory.',
                    ], [
                        'error'    => 'Could not create a new entry in the database when saving file.',
                        'solution' => 'Verify that the <em><?=$table_name?> </em> table exists in the database and that the database user which Wordpress is using to access it has the appropriate permissions.'
                    ], [
                        'error'    => 'Internal error. Database lookup error. No entry with a corresponding ID was found in the database table.',
                        'solution' => 'Verify that a row with ID exists in the <em><?=$table_name?> </em> table. If you followed a link from the overview table then delete the file and save it again.'
                    ], [
                        'error'    => 'Internal error. Invalid file type stored in database. The column "type" for the file\'s row in the database is malformed.',
                        'solution' => 'Delete the file and save it again.'
                    ], [
                        'error'    => 'Internal error. Could not update database record when updating file.',
                        'solution' => 'Verify that Wordpress user has access to the <em><?=$table_name?> </em> database table.'
                    ], [
                        'error'    => 'Internal error. Error in loading minify library files.',
                        'solution' => 'Files may be missing. Reinstall plugin.'
                    ], [
                        'error'    => 'Internal error. Error initializing minify library for JavaScript files.',
                        'solution' => 'Files may be missing. Reinstall plugin.'
                    ], [
                        'error'    => 'Internal error. Error initializing minify library for CSS files.',
                        'solution' => 'Files may be missing. Reinstall plugin.'
                    ], [
                        'error'    => 'Internal error. File type could not be determined when initializing minify library.',
                        'solution' => 'File type may be malformed in database. Delete the file and save a new copy.'
                    ], [
                        'error'    => 'Error minifying file content',
                        'solution' => 'The file might have a syntax error preventing it from being minified. Check the syntax and try saving the file again.'
                    ], [
                        'error'    => 'Internal error. Minify library is not initialized when trying to minify file.',
                        'solution' => 'The File\'s type may be malformed in database. Delete the file and save a new copy.'
                    ], [
                        'error'    => 'Error saving file to path.',
                        'solution' => 'Check permissions for the web server to write to the wp-content/uploads directory.'
                    ], [
                        'error'    => 'General error saving file.',
                        'solution' => 'Check permissions for the web server to write to the wp-content/uploads directory. Reinstall plugin if problem persists for new files.'
                    ], [
                        'error'    => 'Error minifying file.',
                        'solution' => 'Files may be missing. Reinstall plugin.'
                    ], [
                        'error'    => 'Error loading file with specified ID.',
                        'solution' => 'The file might be deleted or no file with that ID has ever been created.'
                    ], [
                        'error'    => 'Error creating directory for files.',
                        'solution' => 'Check permissions for the web server to write to the wp-content/uploads directory.'
                    ], [
                        'error'    => 'Error creating directory for minified files.',
                        'solution' => 'Check permissions for the web server to write to the wp-content/uploads directory.'
                    ], [
                        'error'    => 'Error deleting file.',
                        'solution' => 'Check that the file exists on the server and that the web server has permission to delete files inside the wp-content/uploads directory.'
                    ], [
                        'error'    => 'Error deleting row with specified ID.',
                        'solution' => 'The ID refers to a file that is not referenced in the <em><?=$table_name?> </em> database table. This could mean that it has already been deleted. No additional actio'
                    ], [
                        'error'    => 'Error saving file. Invalid file type.',
                        'solution' => 'The file type was invalid. Make sure to select a file type in the drop down list before saving or uploading a file.'
                    ], [
                        'error'    => 'Error saving file. Invalid file name.',
                        'solution' => 'The file name is invalid, use another name. The name must be at least one character.'
                    ], [
                        'error'    => 'Error saving file. File name can not be blank.',
                        'solution' => 'The file must be at least one character.'
                    ]
                ];

                foreach ($errors as $code => $error) {
                    $html = '<li id="error_id_' . $code . '">';
                    $html .= '<h4>Error #0</h4>';
                    $html .= '<ul><li>' . $error['message'] . '</li>';
                    $html .= '<li>' . $error['solution'] . '</li></ul>';

                    echo $html;
                }
            ?>
        </ul>
    </div>
</div>