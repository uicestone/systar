/* Load this script using conditional IE comments if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-close' : '&#x78;',
			'icon-list' : '&#x54;&#x6f;&#x64;&#x6f;',
			'icon-checkmark' : '&#x44;&#x6f;&#x6e;&#x65;',
			'icon-play' : '&#x3e;',
			'icon-plus' : '&#x2b;',
			'icon-minus' : '&#x2d;',
			'icon-home' : '&#x48;&#x6f;&#x6d;&#x65;',
			'icon-envelop' : '&#x4d;&#x61;&#x69;&#x6c;',
			'icon-unlocked' : '&#x55;&#x6e;&#x6c;&#x6f;&#x63;&#x6b;&#x65;&#x64;',
			'icon-lock' : '&#x4c;&#x6f;&#x63;&#x6b;&#x65;&#x64;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
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