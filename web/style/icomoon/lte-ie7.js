/* Use this script if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-plus' : '&#x21;',
			'icon-minus' : '&#x22;',
			'icon-checkmark' : '&#x23;',
			'icon-x' : '&#x24;',
			'icon-file-pdf' : '&#x27;',
			'icon-file-word' : '&#x28;',
			'icon-file-excel' : '&#x29;',
			'icon-play' : '&#xe10a;',
			'icon-first' : '&#xe000;',
			'icon-last' : '&#xe001;',
			'icon-arrow-right' : '&#xe002;',
			'icon-arrow-left' : '&#xe003;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; i < els.length; i += 1) {
		el = els[i];
		attr = el.getAttribute('data-icon');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};