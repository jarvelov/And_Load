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

function setAceContent(text) {
    editor.setValue(text);
}

function setAceDisabled() {
    editor.setReadOnly(true);
}

function setAceEnabled() {
    editor.setReadOnly(false);
}

//Get the new data and save it to the temporary textarea
function contentChanged() {
    var content = editor.session.getValue();
    var textarea = document.getElementById('edit_file_temporary_textarea');
    textarea.value = content;
}

jQuery(document).ready(function() {
    //Initialize Ace with default settings
    editor = ace.edit("editor");
    setAceTheme( editorSettings['theme'] );
    setAceType( editorSettings['mode'] );

    //Register a listen event on any changes made with editor
    editor.getSession().on('change', contentChanged);

    jQuery('#new_file_type').on('change', function() {
        setAceFontSize('default');
        setAceType(this.value);
    })

    jQuery('#new_file_upload').on('change', function() {
        setAceDisabled();
        setAceContent('The editor has been disabled.\n\nPlease click "Save File" to upload the selected file to edit it.\nTo cancel the upload click the red "X" next to the uploaded file\'s name to continue editing.')
        setAceFontSize(20);

        //Set file name as the value of the #new_file_upload_file_name element
        jQuery('#new_file_upload_file_name').val( jQuery(this).val() );
    });
});