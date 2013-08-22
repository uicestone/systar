var components=[
	/**
	 * 导航菜单
	 * 这个可以先忽略，直接放在后台渲染
	 */
	{
		"type":"nav",
		"data":[
			{
				"name":"日程",//显示名称
				"href":"#calendar",//链接
				"addhref":"#",//＋号的链接
				"subnav":[
					{
						"name":"任务",
						"href":"#taskboard",
					},
					{
						"name":"列表",
						"href":"#schedule/list",
					}
				]
			}
		]
	},
	/**
	 * 列表
	 * 后端表达：用到的数据是哪些collection哪些model
	 */
	{
		"type":"list",
		"data":{
			"collection":"people",//这个列表要用到people collection的数据，用它展开主循环,
			"collection":"people.meta",//子表也用这种形式，这样下面写element.name, elememt.content就是meta的键和值
			"limit":25,
			"order_by":"id desc",
			"fields":[
				{
					"heading":"名称",
					"cell":{"data":"<%=element.abbreviation%>","class":"ellipsis","title":"<%=element.name%>"}//element为collection展开循环的一行，有没有更可行的表达方式
				},
				{
					"heading":"类型",
					"cell":{"data":"<%=element.type%>"}
				},
				{
					"heading":"电话",
					"cell":{"data":"<%=element.phone%>"}
				},
				{
					"heading":"电邮",
					"cell":{"data":"<%=element.email%>"}
				}
			],
			"search":[
				{
					"field":"名称",
					"queryKey":"name"
				}
			]
		}
	},
	/**
	 * 表单
	 */
	{
		"type":"fields",
		"data":[
			{
				"name":"name",
				"type":"text",
				"value":"<%=element.name%>",//element为当前页面查看的对象，如/#people/1即people model中id=1的元素
				"label":"姓名"
			},
			{
				"name":"id_card",
				"type":"text",
				"value":"<%=element.name%>",
				"label":"身份证号"
			},
		]
	},
	/*
	 * 日历
	 */
	{
		"type":"calendar"
	},
	/**
	 * 任务板
	 */
	{
		"type":"taskboard"
	},
	/**
	 * 统计图表
	 */
	{
		"name":"stats",
		"data":{
			
		}
	},
	/**
	 * 文件
	 */
	{
		"type":"document"
	},
	/**
	 * html 模版
	 */
	"<div><%=person.name%></div>"
]