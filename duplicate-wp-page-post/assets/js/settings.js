(function () {
	'use strict';

	document.addEventListener('click', function (event) {
		var button = event.target.closest('.dpp-settings-notice-dismiss');
		var notice;

		if (!button) {
			return;
		}

		notice = button.closest('.dpp-settings-notice');
		if (notice) {
			notice.remove();
		}
	});
}());
