jQuery(document).ready(function() {

	//Check for change in revision dropdown and redirect to URL with &revision=value param
	jQuery('#edit_file_revisions_select').change(function() {
		var revision = this.value;
		var urlBase = window.location;

	    var idParam = location.search.split('id=')[1];
		var argsParam = '?page=shortcode_load&tab=tab_edit&id=';

		console.log(urlBase + argsParam);

		var newUrl = urlBase + argsParam + idParam + "&revision=" + revision;

		location.href = newUrl;
	})
})