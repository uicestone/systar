/**
 * 客户自动完成
 *	根据字符串列出匹配客户id和客户名
 */
new{
	url:'/client/autocomplete',
	get:{
		type:'客户'//'客户','相对方','联系人' ,client.type
	},
	post:{
		term:'张三'//请求匹配的字符串
	},
	response:[
		{
			id:'132',//client.id
			name:'张三丰'//客户简称
	}
	//,...
	]
}
/**
 * 获得客户的来源律师
 */
new {
	url:'/client/getsourcelawyer',
	post:{
		client_name:'张三'//客户名
	},
	response:'王律师'//对应的来源律师名称
	
}
/**
 * 写入互评分数和评语
 */
new{
	url:'/evaluation/scorewrite',
	get:{
		staff:'10'//职员id
	},
	post:{
		indicator:20,//评分项id evaluation_indicator.id
		field:'score',//score表的field名:score,comment
		value:'10'//score表对应field的值，分数或评语内容
	},
	response:'10.0'//数据库实际写入的请求字段的值
}

/**
 * 
 */
'/misc/editable'
'/misc/getHtml'
'/misc/getSelectOption'
'/misc/getSession'
'/misc/setSession'
'/schedule/addtotaskboard'
'/schedule/deletefromtaskboard'
'/schedule/listwrite'
'/schedule/readcalendar'
'/schedule/settaskboardsort'
'/schedule/writecalendar'
