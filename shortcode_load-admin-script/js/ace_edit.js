function setAceTabSize(size) {
    editor.getSession().setTabSize(size);
}

function setAceType(type) {
    editor.getSession().setMode("ace/mode/" + type);
}

function setAceMode(type) {
    switch(type) {
        case 'js':
            setAceType(type);
            setAceTabSize(4);
            break;
        case 'css':
            setAceType(type);
            setAceTabSize(2);
            break;
        default:
            console.log('Unknown mode type');
            break;
    }
}

//Get the new data and save it to the temporary textarea
function contentChanged() {
    var content = editor.session.getValue();
    var textarea = document.getElementById('edit_file_temporary_textarea');
    textarea.value = content;
}

jQuery(document).ready(function() {
    //Register a listen event on any changes made with editor
    editor.getSession().on('change', contentChanged);

    jQuery('#new_file_type').on('change', function() {
        setAceMode(this.value);
    })
});