// Constants.
var CSS_LOADING_STYLE = "loading-style";
var CSS_FOLDER_SECTION = ".folder-section";

var ID_CONTENT_PREFIX = "#content-";

var ANIMATION_SPEED = 'fast';

var loadedFolders = new Object;

$(document).ready(function() {

	// Click event for a folder.
	$(CSS_FOLDER_SECTION).click(function(e) {
		// Get the id of the document.
		var id = e.target.id;

		// Check, if the folder content was already loaded, so that it's not loaded twice.
		if (!loadedFolders[id]) {

			// Animating the Loading.....
			$(ID_CONTENT_PREFIX + id).addClass(CSS_LOADING_STYLE);
			$(ID_CONTENT_PREFIX + id).html("Loading");
			var counter = 0;
			var timerId = setInterval(function() {
				var html = "";

				if (counter % 5 == 0) {
					html = "Loading";
				} else {
					html = $(ID_CONTENT_PREFIX + id).html() + ".";
				}
				$(ID_CONTENT_PREFIX + id).html(html);

				counter++;

			}, 150);

			// Ajax request for loading content of a folder.
			var params = {
				folder : id
			};

			$.ajax({
				type : 'POST', //
				url : "", //
				data : params, //
				dataType : "html", //
				success : function(data) {
					// Before loading the content, make sure that it is hidden.
					$(ID_CONTENT_PREFIX + id).hide();

					// Remove the style for loading.
					$(ID_CONTENT_PREFIX + id).removeClass(CSS_LOADING_STYLE);

					// Insert data.
					$(ID_CONTENT_PREFIX + id).html(data);

					// Set the folder's content to loaded.
					loadedFolders[id] = true;
					
					// Slide in the content of the folder with an animation.
					$(ID_CONTENT_PREFIX + id).slideDown(ANIMATION_SPEED);

					// Stop the Timer for animating the Loading....
					clearInterval(timerId);
				}
			});
		}
	});

	// Toggle event for hiding and showing the content of a folder by sliding down and up.
	$(CSS_FOLDER_SECTION).toggle(function(e) {
		var id = e.target.id;
		$(ID_CONTENT_PREFIX + id).slideDown(ANIMATION_SPEED);
	}, function(e) {
		var id = e.target.id;
		$(ID_CONTENT_PREFIX + id).slideUp(ANIMATION_SPEED);
	});

});