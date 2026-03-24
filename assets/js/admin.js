(function () {
	'use strict';

	function toggleCustomDates() {
		var select = document.querySelector('.wpa-range-select');
		if (!select) {
			return;
		}
		var showCustom = select.value === 'custom';
		document.querySelectorAll('.wpa-custom-date').forEach(function (el) {
			el.style.display = showCustom ? 'inline-block' : 'none';
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var select = document.querySelector('.wpa-range-select');
		if (select) {
			select.addEventListener('change', toggleCustomDates);
		}
		toggleCustomDates();
	});
})();
