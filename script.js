var plugin_qscripttags = {
	/**
	 * Enable or disable tooltips on all links.
	 *
	 * @param {Boolean} on
	 */
	toggleTooltips: function(on) {
		var textElt = document.querySelector('.plugin-qscripttags__admintext');
		textElt.value = textElt.value
			.split(/\r?\n/)
			.map(function(line) {
				if (/^\s*$/.test(line)) {
					return line;
				}

				var parts = line.split(/\s*,\s*/);

				// Enable all
				if (on) {
					parts[3] = 'tt';
				}
				// Disable all
				else if (parts.length >= 4) {
					parts.length = 3;
				}

				return parts
					.join(', ')
					// Remove excess commas
					.replace(/,+$/, '');
			})
			.join('\n');
	}
};
