/* Ace Functions */

function setAceOptions(optionName, optionValue) {
    options = {};
    options[optionName] = optionValue;

    console.log('setting option: ' + optionName + ' to value: ' + optionValue );

    editor.setOptions(options);
}

/* Ace style functions */

function setAceTabSize(size) {
    tabOverride = parseInt( editorSettings['tabOverride'] );

    if(tabOverride) {
        size = parseInt( editorSettings['tabSize'] )
    }

    setAceOptions('tabSize', parseInt( size ) );
}

function setAceMode(mode) {
    setAceOptions('mode', 'ace/mode/' + mode)
}

function setAceTheme(theme) {
    setAceOptions('theme', 'ace/theme/' + theme);
}

function setAceLineNumbers(state) {
    setAceOptions( 'showLineNumbers', parseInt( state ) );
}

function setAcePrintMargin(state) {
    setAceOptions( 'showPrintMargin', parseInt( state ) );
}

function setAcePrintMarginColumn(number) {
    setAceOptions ( 'printMarginColumn', parseInt( number ) );
}

function setAceType(modeType) {
    type = modeType.substring( modeType.lastIndexOf('/') +1 ); //get the 'basename' of ace mode type
    switch(type) {
        case 'javascript':
            setAceMode(modeType);
            setAceTabSize(4);
            break;
        case 'css':
            setAceMode(modeType);
            setAceTabSize(2);
            break;
        case 'plain_text':
            setAceMode(modeType);
            setAceTabSize(4);
            break;
        default:
            //console.log('Unknown mode type: ' + type);
            break;
    }
}

function setAceFontSize(size) {  
    if(size == 'default') {
        size = editorSettings['fontSize'];
    }

    setAceOptions( 'fontSize', parseInt( size ) );
}

/* Ace content functions */

function getAceContent() {
    return editor.session.getValue();
}

function setAceContent(text) {
    editor.setValue(text);
}

function isAceDisabled() {
    return editor.getReadOnly();
}

function setAceDisabled() {
    setAceOptions( 'readOnly', true);
}

function setAceEnabled() {
    setAceOptions( 'readOnly', false);
}

/* Temporary content storage functions */

function setTemporaryContent(content) {
    if( ! ( isAceDisabled() ) ) {
        jQuery('#edit_file_temporary_textarea').val(content);
    }
}

function getTemporaryContent() {
    value = jQuery('#edit_file_temporary_textarea').val();
    if(value) {
        return value;
    } else {
        return null;
    }
}

/* File upload handling functions */

function handleUpload(fileName) {
    jQuery('#new_file_upload_file_name').val( fileName ); //Set file name of the selected file as the value of the #new_file_upload_file_name input element
    jQuery('#new_file_upload_reset_button').css( { display: 'inline-block' } ); //Show reset button
    
    baseFileType = fileName.substring( fileName.lastIndexOf('.') ); //Get the selected file's type
    baseFileName = fileName.substring(0, fileName.lastIndexOf('.') );

    message = 'Please click the "Save File" button to upload the selected file to be able to edit it.';
    messageCancel = '\n\nTo cancel the upload click the "X" mark next to the uploaded file\'s name to continue editing.';

    switch(baseFileType) {
        case '.js':
            fileType = 'javascript';
            break;
        case '.css':
            fileType = 'css';
            break;
        default:
            fileType = false;
            break;
    }

    //Set the file name (without extension) to the input name element
    jQuery('#new_file_name').val(baseFileName)

    //Check if the file type exists in the new_file_type drop down 
    var exists = jQuery("#new_file_type option[value='" + fileType + "']").length !== 0;
    if(!exists) {
        jQuery('#new_file_type').val('plain_text');
        message = 'INFO: The selected file type is not supported!\nPlease change the file type in the drop down above manually, otherwise it will NOT be saved.\nWhen you have changed the file type click the "Save File" button to continue and upload the file.';
        message += messageCancel;
    } else {
        jQuery('#new_file_type').val(fileType); //Set file type drop down to the option corresponding to the selected file's type
        message += messageCancel;
    }

    /*Check if ace is disabled (i.e. a file is already selected when this function triggers).
        we don't want to overwrite the temporary content with our message to the user.

        ...that'd be awkward when we restore the content should the user cancel the upload. */

    if( ! ( isAceDisabled() ) ) {
        setAceDisabled();

        /* Save the content that was entered before a file was selected for upload
            to the temporary textarea so we can restore it on upload canceled */

        var tmpContent = getAceContent();
        setTemporaryContent(tmpContent);
    }

    setAceContent(message);
    setAceFontSize(20);    
}

function handleUploadCanceled() {
    jQuery('#new_file_upload').val(''); //reset file upload
    jQuery('#new_file_upload_file_name').val(''); //blank input element holding selected file's name (with extension)
    jQuery('#new_file_name').val(''); //blank input element holding selected file's name (without extension)

    setAceFontSize('default'); //set default Ace font size
    setAceContent( getTemporaryContent() ); //restore previous editor content
    setAceType( editorSettings['mode'] ); //set ace editor type mode back to default
    jQuery('#new_file_type').val( editorSettings['mode'] ); //set drop down type to default

    setAceEnabled(); //enable editor again
}

/* Listeners */

//Check for change in revision dropdown and redirect to URL with &revision=value param
jQuery('#edit_file_revisions_select').change(function() {
    var revision = this.value;
    var urlBase = window.location.toString();
    var newUrl;

    //URL GET parameters
    var revisionParamBase = location.search.split('revision=')[0];
    var revisionParam = location.search.split('revision=')[1];

    if(typeof(revisionParam) == 'undefined') {
        newUrl = urlBase + '&revision=' + revision;
    } else {
        var tmpUrl = urlBase.substr(0, urlBase.indexOf('&revision=') );
        newUrl = tmpUrl + '&revision=' + revision;
    }

    location.href = newUrl;
});

//Select all text in shortcode input element when user clicks on it
jQuery('#edit_file_shortcode_display').on('focus', function() {
    jQuery(this).select();
});

//Editor file type changed
jQuery('#new_file_type').on('change', function() {
    if( ! ( isAceDisabled() ) ) { //don't trigger if ace editor is disabled
        setAceFontSize('default');
        setAceType(this.value);
    }
})

//Editor font size changed
jQuery('#edit_file_font_size_select').on('change', function() {
    if( ! ( isAceDisabled() ) ) { //don't trigger if ace editor is disabled
        setAceFontSize(this.value);
    }
})

//Editor theme changed
jQuery('#edit_file_theme_select').on('change', function() {
    if( ! ( isAceDisabled() ) ) { //don't trigger if ace editor is disabled
        setAceTheme( this.value );
    }
})

//File has been selected for upload
jQuery('#new_file_upload').on('change', function() {
    fileName = jQuery(this).val();
    handleUpload(fileName);
});

//File upload was canceled
jQuery('#new_file_upload_reset_button').on('click', function() {
    handleUploadCanceled();
    jQuery(this).hide(); //hide reset button
});

//User requested to see more settings, toggle #edit_file_editor_settings_container
jQuery('#edit_file_more_settings_button').on('click', function() {
    toggleSettingsDisplay();
});

//User submitted the form, check if the delete button initiated the submit and prompt for action
jQuery(document).on("click", ":submit", function(event){
    if(this.id == 'delete') {
        if( ! ( jQuery(this).hasClass('confirmed') ) ) {
            event.preventDefault();
            bootbox.dialog({
                title:"Permanently delete file?",
                message:"Are you sure you want to delete this file and <em>all revisions</em> of it? This action is <strong>permanent</strong> and can NOT be reversed!",
                buttons:{
                    cancel:{
                        label:"No, I changed my mind.",
                        className:"btn-default",
                    },
                    confirm:{
                        label:"Yes",
                        className:"btn-danger",
                        callback:function() {
                            jQuery('#delete').addClass('confirmed');
                            jQuery('#delete').click();
                        }
                    }
                }
            })
        }
    }
});

//Logic to hide / show #edit_file_editor_settings_container and add appropriate class to button span to indicate state
function toggleSettingsDisplay() {
    if ( jQuery('#edit_file_more_icon').hasClass('glyphicon-collapse-down') ) {
        jQuery('#edit_file_more_text').text('Less');
        jQuery('#edit_file_more_icon').removeClass('glyphicon-collapse-down');
        jQuery('#edit_file_more_icon').addClass('glyphicon-collapse-up');
        jQuery('#edit_file_editor_settings_container').show();
    } else {
        jQuery('#edit_file_more_text').text('More');
        jQuery('#edit_file_more_icon').removeClass('glyphicon-collapse-up');
        jQuery('#edit_file_more_icon').addClass('glyphicon-collapse-down');
        jQuery('#edit_file_editor_settings_container').hide();
    }
}

/* document ready 'init' function */

jQuery(document).ready(function() {
    //jQuery('input#submit').removeClass('button'); //Style wordpress submit button to remove the 'button' class interfering with bootstrap styling

    //Initialize Ace editor
    if( jQuery('#editor').length > 0 ) {
/*
        editor = ace.edit("editor");
        editor.$blockScrolling = Infinity; //this is needed to prevent the Ace editor from spamming the console (version: 1.1.8)

        //Set Ace default settings
        setAceTheme( editorSettings['theme'] );
        setAceType( editorSettings['mode'] );
        setAceFontSize( editorSettings['fontSize'] );

        setAceLineNumbers( editorSettings['showLineNumbers'] );
        setAcePrintMargin( editorSettings['showPrintMargin'] );
        setAcePrintMarginColumn( editorSettings['printMarginColumn'] );

        //Load content in ace into temporary area should the user save without changing any content.
        setTemporaryContent( getAceContent() );

        //Register a listener to trigger an event on any changes made within the Ace editor
        editor.getSession().on('change', function() {
            setTemporaryContent( getAceContent() );//Get the new data and save it to the temporary textarea
        });
*/
    }

    //If this browser supports mutationObservers automatically resize ace editor on resize
    if (typeof MutationObserver == 'function') { 
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutationRecord) {
                editor.resize();
            });
        });

        var target = document.getElementById('editor');
        observer.observe(target, { attributes : true, attributeFilter : ['style'] });
    }
});
