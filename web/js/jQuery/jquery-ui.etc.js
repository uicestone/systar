/*jQueryUI.datepicker.chinese*/
$.datepicker.regional['zh-CN']={closeText:'关闭',prevText:'&#x3c;上月',nextText:'下月&#x3e;',currentText:'今天',monthNames:['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月'],monthNamesShort:['一','二','三','四','五','六','七','八','九','十','十一','十二'],dayNames:['星期日','星期一','星期二','星期三','星期四','星期五','星期六'],dayNamesShort:['周日','周一','周二','周三','周四','周五','周六'],dayNamesMin:['日','一','二','三','四','五','六'],weekHeader:'周',dateFormat:'yy-mm-dd',firstDay:1,isRTL:false,showMonthAfterYear:true,yearSuffix:'年'};$.datepicker.setDefaults($.datepicker.regional['zh-CN']);
/*autocomplete.combobox http://jqueryui.com/demos/autocomplete/#combobox*/
(function($){
	$.widget("ui.combobox",{
		_create: function(){
			$(this.element).change(function(){
				input.val($(this).children("option:first").html());
			});
			var input,
			self = this,
			select = this.element.hide(),
			selected = select.children(":selected"),
			value = selected.val() ? selected.text() : "",
			wrapper = this.wrapper = $("<span>")
				.addClass("ui-combobox")
				.insertAfter(select);

			input = $("<input>")
				.appendTo(wrapper)
				.val(value)
				.addClass("ui-state-default ui-combobox-input")
				.autocomplete({
					delay: 0,
					minLength: 0,
					source: function(request, response){
						var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
						response(select.children("option").map(function(){
							var text = $(this).text();
							if (this.value && (!request.term || matcher.test(text)))
								return{
									label: text.replace(
										new RegExp(
											"(?![^&;]+;)(?!<[^<>]*)(" +
											$.ui.autocomplete.escapeRegex(request.term) +
											")(?![^<>]*>)(?![^&;]+;)", "gi"
										), "<strong>$1</strong>"),
									value: text,
									option: this
								};
						}));
					},
					select: function(event, ui){
						ui.item.option.selected = true;
						self._trigger("selected", event,{
							item: ui.item.option
						});
						select.change();
					},
					change: function(event, ui){
						if (!ui.item){
							var matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex($(this).val()) + "$", "i"),
								valid = false;
							select.children("option").each(function(){
								if ($(this).text().match(matcher)){
									this.selected = valid = true;
									return false;
								}
							});
							if (!valid){
								// remove invalid value, as it didn't match anything
								$(this).val("");
								select.val("");
								input.data("autocomplete").term = "";
								return false;
							}
						}
					}
				})
				.addClass("ui-widget ui-widget-content ui-corner-left");

			input.data("autocomplete")._renderItem = function(ul, item){
				return $("<li></li>")
					.data("item.autocomplete", item)
					.append("<a>" + item.label + "</a>")
					.appendTo(ul);
			};

			$("<a>")
				.attr("tabIndex", -1)
				.attr("title", "Show All Items")
				.appendTo(wrapper)
				.button({
					icons:{
						primary: "ui-icon-triangle-1-s"
					},
					text: false
				})
				.removeClass("ui-corner-all")
				.addClass("ui-corner-right ui-combobox-toggle")
				.click(function(){
					// close if already visible
					if (input.autocomplete("widget").is(":visible")){
						input.autocomplete("close");
						return;
					}

					// work around a bug (likely same cause as #5265)
					$(this).blur();

					// pass empty string as value to search for, displaying all results
					input.autocomplete("search", "");
					input.focus();
				});
		},

		destroy: function(){
			this.wrapper.remove();
			this.element.show();
			$.Widget.prototype.destroy.call(this);
		}
	});
})(jQuery);
/*
 *添加scrollstart和scrollstop事件
 *http://james.padolsey.com/javascript/special-scroll-events-for-jquery/
 */
(function(){
 
    var special = jQuery.event.special,
        uid1 = 'D' + (+new Date()),
        uid2 = 'D' + (+new Date() + 1);
 
    special.scrollstart = {
        setup: function() {
 
            var timer,
                handler =  function(evt) {
 
                    var _self = this,
                        _args = arguments;
 
                    if (timer) {
                        clearTimeout(timer);
                    } else {
                        evt.type = 'scrollstart';
                        jQuery.event.handle.apply(_self, _args);
                    }
 
                    timer = setTimeout( function(){
                        timer = null;
                    }, special.scrollstop.latency);
 
                };
 
            jQuery(this).bind('scroll', handler).data(uid1, handler);
 
        },
        teardown: function(){
            jQuery(this).unbind( 'scroll', jQuery(this).data(uid1) );
        }
    };
 
    special.scrollstop = {
        latency: 300,
        setup: function() {
 
            var timer,
                    handler = function(evt) {
 
                    var _self = this,
                        _args = arguments;
 
                    if (timer) {
                        clearTimeout(timer);
                    }
 
                    timer = setTimeout( function(){
 
                        timer = null;
                        evt.type = 'scrollstop';
                        jQuery.event.handle.apply(_self, _args);
 
                    }, special.scrollstop.latency);
 
                };
 
            jQuery(this).bind('scroll', handler).data(uid2, handler);
 
        },
        teardown: function() {
            jQuery(this).unbind( 'scroll', jQuery(this).data(uid2) );
        }
    };
 
})();

/*自定义的默认选项*/
var highchartsOptions={
	chart: {
		backgroundColor:null
	},
	title: {
		style:{
			'font-family':'Microsoft Yahei,SimHei'
		}
	},
	xAxis: {
		labels: {
			style: {
				'font-size':'14px',
				'font-family':'Microsoft Yahei,SimHei'
			}
		}
	},
	yAxis: {
		title: {
			text: null
		},
		min:0
	},
	legend: {
		layout: 'vertical',
		align: 'right',
		verticalAlign: 'middle',
		borderWidth: 0
	},
	credits: {
		enabled: false
	}
}
