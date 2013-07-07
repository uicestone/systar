/* Load this script using conditional IE comments if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon-checkmark' : '&#x63;&#x6f;&#x6d;&#x70;&#x6c;&#x65;&#x74;&#x65;&#x64;',
			'icon-list' : '&#x74;&#x6f;&#x64;&#x6f;',
			'icon-close' : '&#x78;',
			'icon-clock' : '&#x65;&#x6e;&#x72;&#x6f;&#x6c;&#x6c;&#x65;&#x64;'
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