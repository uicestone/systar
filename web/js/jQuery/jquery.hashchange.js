/*杨中科同学修改的hashchange插件
 *http://www.cnblogs.com/rupeng/archive/2011/01/15/1936322.html
 */
(function($) {

	$.fn.extend({
		hashchange: function(callback) {
			this.bind('hashchange', callback);

			/*if (location.hash)//if location.hash is not empty,fire the event when load,make ajax easy
			{
				callback();
			}*/
		},
		openOnClick: function(href) {
			if (href === undefined || href.length == 0)
				href = '#';
			return this.click(function(ev) {
				if (href && href.charAt(0) == '#') {
					// execute load in separate call stack
					window.setTimeout(function() { $.locationHash(href) }, 0);
				} else {
					window.location(href);
				}
				ev.stopPropagation();
				return false;
			});
		}
	});

	// IE 8 introduces the hashchange event natively - so nothing more to do
	if ($.browser.msie && document.documentMode && document.documentMode >= 8) {
		$.extend({
			locationHash: function(hash) {
				if (!hash)//get hash value
				{
					if (location.hash.charAt(0) == '#') {
						return location.hash.substr(1, location.hash.length-1);
					}
					return location.hash;
				}
				if (!hash) hash = '#';
				else if (hash.charAt(0) != '#') hash = '#' + hash;
				location.hash = hash;
			}
		});
		return;
	}

	var curHash;
	// hidden iframe for IE (earlier than 8)
	var iframe;

	$.extend({
		locationHash: function(hash) {
			if (!hash)//get hash value
			{
				if (location.hash.charAt(0) == '#') {
					return location.hash.substr(1, location.hash.length - 1);
				}
				return location.hash;
			}
			if (curHash === undefined) return;

			if (!hash) hash = '#';
			else if (hash.charAt(0) != '#') hash = '#' + hash;

			location.hash = hash;

			if (curHash == hash) return;
			curHash = hash;

			if ($.browser.msie) updateIEFrame(hash);
			$.event.trigger('hashchange');
		}
	});

	$(document).ready(function() {
		curHash = location.hash;
		if ($.browser.msie) {
			// stop the callback firing twice during init if no hash present
			if (curHash == '') curHash = '#';
			// add hidden iframe for IE
			iframe = $('<iframe />').hide().get(0);
			$('body').prepend(iframe);
			updateIEFrame(location.hash);
			setInterval(checkHashIE, 100);
		} else {
			setInterval(checkHash, 100);
		}
	});
	$(window).unload(function() { iframe = null });

	function checkHash() {
		var hash = location.hash;
		if (hash != curHash) {
			curHash = hash;
			$.event.trigger('hashchange');
		}
	}

	if ($.browser.msie) {
		// Attach a live handler for any anchor links
		$('a[href^=#]').live('click', function() {
			var hash = $(this).attr('href');
			// Don't intercept the click if there is an existing anchor on the page
			// that matches this hash
			if ($(hash).length == 0 && $('a[name=' + hash.slice(1) + ']').length == 0) {
				$.locationHash(hash);
				return false;
			}
		});
	}

	function checkHashIE() {
		// On IE, check for location.hash of iframe
		var idoc = iframe.contentDocument || iframe.contentWindow.document;
		var hash = idoc.location.hash;
		if (hash == '') hash = '#';

		if (hash != curHash) {
			if (location.hash != hash) location.hash = hash;
			curHash = hash;
			$.event.trigger('hashchange');
		}
	}

	function updateIEFrame(hash) {
		if (hash == '#') hash = '';
		var idoc = iframe.contentWindow.document;
		idoc.open();
		idoc.close();
		if (idoc.location.hash != hash) idoc.location.hash = hash;
	}

})(jQuery);