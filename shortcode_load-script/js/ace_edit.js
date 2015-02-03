//Register a listen event on any changes made with editor
editor.getSession().on('change', contentChanged)

//Get the new data and save it to the temporary textarea
function contentChanged() {
	var content = editor.session.getValue();
	
}