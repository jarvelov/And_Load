/* Ace Functions */

function setAceTabSize(size) {
    editor.getSession().setTabSize(size);
}

function setAceMode(mode) {
    editor.getSession().setMode("ace/mode/" + mode);
}

function setAceType(type) {
    switch(type) {
        case 'javascript':
            setAceMode(type);
            setAceTabSize(4);
            break;
        case 'css':
            setAceMode(type);
            setAceTabSize(2);
            break;
        case 'plain_text':
            setAceMode(type);
            setAceTabSize(4);
            break;
        default:
            console.log('Unknown mode type: ' + type);
            break;
    }
}

function setAceTheme(theme) {
    editor.setTheme("ace/theme/" + theme);
}

function setAceFontSize(size) {  
    if(size == 'default') {
        size = editorSettings['fontSize'];
    }

    jQuery('#editor').css({
        fontSize: size
    });
}

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
    editor.setReadOnly(true);
}

function setAceEnabled() {
    editor.setReadOnly(false);
}

function setTemporaryContent(content) {
    jQuery('#edit_file_temporary_textarea').val(content);
}

function getTemporaryContent() {
    return jQuery('#edit_file_temporary_textarea').val();   
}

function handleUpload(fileName) {
    jQuery('#new_file_upload_file_name').val( fileName ); //Set file name of the selected file as the value of the #new_file_upload_file_name input element
    jQuery('#new_file_upload_reset_button').css( { display: 'inline-block' } ); //Show reset button

    /*Check if ace is disabled (i.e. a file is already selected when this function triggers.
        we don't want to overwrite the temporary content with our message to the user.

        ...that'd be awkward when we restore the content should the user cancel the upload) */

    if( ! ( isAceDisabled() ) ) {
        var tmpContent = getAceContent();

        setAceDisabled();
        setAceContent('The editor has been disabled.\n\nPlease click "Save File" to upload the selected file to edit it.\nTo cancel the upload click the "X" mark next to the uploaded file\'s name to continue editing.')
        setAceFontSize(20);

        setTemporaryContent(tmpContent); //save the content that was entered before a file was selected for upload to the temporary textarea so we can restore it on 
    }
}

function handleUploadCanceled() {
    jQuery('#new_file_upload').val(''); //reset file upload
    jQuery('#new_file_upload_file_name').val(''); //blank input element holding selected file's name

    setAceFontSize('default'); //set default Ace font size
    setAceContent( getTemporaryContent() ); //restore previous editor content
}

//Get the new data and save it to the temporary textarea
function contentChanged() {
    setTemporaryContent( getAceContent() );
}

function setSubmitButtonClass() {
    jQuery('input#submit').removeClass('button');
}

jQuery(document).ready(function() {
    setSubmitButtonClass();

    //Initialize Ace with default settings
    editor = ace.edit("editor");
    editor.$blockScrolling = Infinity; //this is needed to prevent the Ace editor from spamming the console (version: 1.1.8)

    setAceTheme( editorSettings['theme'] );
    setAceType( editorSettings['mode'] );

    //Register a listen event on any changes made with editor
    editor.getSession().on('change', contentChanged);   
});

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
    setAceFontSize('default');
    setAceType(this.value);
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
