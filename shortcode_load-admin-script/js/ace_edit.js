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
        default:
            console.log('Unknown mode type');
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
/*    setAceTheme( editorSettings['theme'] );
    setAceType( editorSettings['mode'] );
    */

    //Register a listen event on any changes made with editor
    editor.getSession().on('change', contentChanged);

    jQuery('#new_file_type').on('change', function() {
        setAceFontSize('default');
        setAceType(this.value);
    })

    jQuery('#new_file_upload').on('change', function() {
        setAceDisabled();
        setAceContent('The editor has been disabled. Please click "Save File" to upload the selected file to edit it or click the "Reset" link to clear the upload and continue editing.')
    });
});