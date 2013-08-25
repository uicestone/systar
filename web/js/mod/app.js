// Router的逻辑迁移过来，整个app的入口
define(function(require){
"use strict";
$.ajaxSetup({ cache: false});
var TplEngine = require("template-engine");
var ViewHelpers = require("view-helpers");
var Grid = require("grid");
var MainContainer = $(".page-content .row-fluid");

/**
 * 管理多异步请求回调
 * 简单实现
 * @return {[type]} [description]
 */
var MultiAsync = function(alldone){
    this.count = 0;
    this.data = [];
    this.funcs = [];
    this.alldone = alldone;
    return this;
}
MultiAsync.fn = MultiAsync.prototype;
MultiAsync.fn.add = function(func){
    var self = this;
    var current = self.count;
    self.count++;
    self.funcs.push(function(){
        func(done);
    });

    function done(data){
        self.count--;
        self.data[current] = data;
        if(self.count === 0){
            self.alldone.apply(null,self.data);
            self.data = [];
            self.funcs = [];
        }
    }
}

MultiAsync.fn.start = function(){
    _.each(this.funcs,function(func){
        func();
    });
}

TplEngine.loadAll(function(Templates){


var Workspace = Backbone.Router.extend({
    routes: {
        "calendar":"calendar",
        "taskboard":"taskboard",
        "achievement":"achievement",
        "achievement/stuff":"stuffAchievement",
        "schedule":"scheduleStats",
        ":resource/:num":"edit",
        ":resource":"list"
    },



    list:function(name){
        var tplname = [name,"list"].join("/");
        var template = Templates[tplname];
        
        $.getJSON(name, {}, function(result){
            var html = _.template(template,_.extend(result,ViewHelpers));
            var wrap = $(html);

            wrap.find("table").each(function(i,el){
                el = $(el);
                var grid = new Grid(name, result, el);
                var paginator = new Backgrid.Extension.Paginator({
                  collection: grid.collection
                });

                wrap.empty().append(grid.render().$el);
                wrap.append(paginator.render().el);

            });

            MainContainer.empty().append(wrap);
        });

    },

    edit:function(name,id){
        var tplname = [name,"edit"].join("/");

        var template = Templates[tplname];

        $.getJSON([name,id].join("/"),{},function(data){
            var html = _.template(template,_.extend(data,ViewHelpers));
            var wrap = $(html);

            // initialize all datepickers
            wrap.find(".date-picker").datepicker();

            // Grids
            wrap.find("[data-widget-type=table]").each(function(i,el){
                el = $(el);
                var container = el.find(".widget-main");
                if(!data.meta){return;}
                var grid = new Grid("meta", data.meta, el);
                var paginator = new Backgrid.Extension.Paginator({
                  collection: grid.collection
                });

                container.empty().append(grid.render().$el);
                container.append(paginator.render().el);

            });


            MainContainer.empty().append(wrap);


        }).error(function(){
            console.log("err");
        });

    },
    calendar:function(){
        var m = new MultiAsync(function(tpl,data,module){
            MainContainer.html(_.template(tpl,data));
            var calendar_elem = $("#calendar"),
                event_elems = $("#external-events .external-event");

            module.render(data,event_elems,calendar_elem);
        });

        m.add(function(done){
            TplEngine.load("calendar",done);
        });

        m.add(function(done){
            $.getJSON("/calendar",{},done);
        });

        m.add(function(done){
            require.async("calendar",done);
        });
        
        m.start();
        
    }
});

var syssh = new Workspace();
Backbone.history.start(); 




});
});