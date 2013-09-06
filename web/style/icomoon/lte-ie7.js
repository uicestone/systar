/* Load this script using conditional IE comments if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-checkmark' : '&#x2713;',
			'icon-clock' : '&#x2610;',
			'icon-list' : '&#x25a4;',
			'icon-close' : '&#x78;',
			'icon-floppy' : '&#x21;',
			'icon-plus' : '&#x22;',
			'icon-minus' : '&#x23;',
			'icon-pencil' : '&#x24;',
			'icon-lock' : '&#x25;',
			'icon-unlocked' : '&#x26;',
			'icon-remove' : '&#x27;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
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