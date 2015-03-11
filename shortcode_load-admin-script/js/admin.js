jQuery(document).ready(function() {

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
	})

	jQuery('#overview_filter').keyup(function() {
		var value = jQuery(this).val().toLowerCase();
		searchFileBlocks(value);
	})
})

function hideElement(element) {
	jQuery(element).hide();
}

function showElement(element) {
	jQuery(element).show();
}

/*
* Returns an array with objects consisting of
* the element with class shortcode-load-file-block
* and the text in the 'span' element.
*/

function getFileBlocks() {
	var fileBlocks = [];
	jQuery('.shortcode-load-file-block').each(function() {
		var blockText = jQuery(this).find('span').text();
		var blockObj = {parent:this,text:blockText};

		fileBlocks.push(blockObj);
	})

	return fileBlocks;
}

/*
* Search each object in the fileBlocks array returned by getFileBlocks()
* for the string supplied and hide/show elements accordingly 
*
* searchFileBlocks('nameOfMyFile')
*/

function searchFileBlocks(string) {
	var fileBlocks = getFileBlocks()
	for (var i = fileBlocks.length - 1; i >= 0; i--) {
		var currentBlock = fileBlocks[i];
		var text = currentBlock['text'].toLowerCase();
		var parent = currentBlock['parent'];
		
		if (text.indexOf(string) > -1) {
			showElement(parent);
		} else {
			hideElement(parent);
		}
	};
}