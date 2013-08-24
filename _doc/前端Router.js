/**
 * 这一组方法根据uri、客户端数据、组件表达式component 渲染界面
 * component.type确定调哪个方法
 * 读取component.data，结合当前的uri，决定需要调用什么数据
 * 查找本地数据，决定需要同步什么数据
 * 根据
 */
var parseComponents={
	plain:function(data){
		data="<div><%=people.name%></div>";
	},
	nav:function(){},
	list:function(){},
	fields:function(){},
	calendar:function(){},
	taskboard:function(){},
	stats:function(){},
	document:function(){}
};