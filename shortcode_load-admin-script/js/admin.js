jQuery(document).ready(function() {

	//Check for change in revision dropdown and redirect to URL with &revision=value param
	jQuery('#edit_file_revisions_select').change(function() {
		var revision = this.value;
		var urlBase = window.location;

		//URL GET parameters
	    var idParam = location.search.split('id=')[1];
	    var revisionParam = location.search.split('id=')[1];

		var newUrl = urlBase + "&revision=" + revision;

		console.log(newUrl);

		location.href = newUrl;
	})
})