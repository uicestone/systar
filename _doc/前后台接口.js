var client={
	/**
	 * 客户自动完成
	 *	根据字符串列出匹配客户id和客户名
	 */
	autoComplete:{
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
	},
	
	/**
	 * 获得客户的来源律师
	 */
	getSourceLawyer:{
		url:'/client/getsourcelawyer',
		post:{
			client_name:'张三'//客户名
		},
		response:'王律师'//对应的来源律师名称

	}
}

var evaluation={
	/**
	 * 写入互评分数和评语
	 */
	scoreWrite:{
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

}

var misc={
	/**
	 * 
	 */
	editable:{
		url:'/misc/editable',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	getHtml:{
		url:'/misc/getHtml',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	getSelectedOption:{
		url:'/misc/getSelectOption',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	getSession:{
		url:'/misc/getSession',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	setSession:{
		url:'/misc/setSession',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	}
}

var schedule={
	ajaxEdit:{
		url:'/schedule/ajaxedit/{schedule_id}',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	addToTaskBoard:{
		url:'/schedule/addtotaskboard/{schedule_id}[/{uid}]',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	deleteFromTaskBoard:{
		url:'/schedule/deletefromtaskboard/{{schedule_id}}[/{uid}]',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	listWrite:{
		url:'/schedule/listwrite',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	readCalendar:{
		url:'/schedule/readcalendar',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	setTaskBoardSort:{
		url:'/schedule/settaskboardsort',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	},
	view:{
		url:'/schedule/view/{schedule_id}',
		response:{
			name:'日志标题',
			view:'供dialog中显示的日志视图'
		}
	},
	writeCalendar:{
		url:'/schedule/writecalendar',
		get:{
			
		},
		post:{
			
		},
		response:{
			
		}
	}
}