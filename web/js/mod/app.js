// Router的逻辑迁移过来，整个app的入口
define(function(require){
    
var TplEngine = require("template-engine");
var ViewHelpers = require("view-helpers");
var Grid = require("grid");
var MainContainer = $(".page-content .row-fluid .span12");

TplEngine.loadAll(function(Templates){



var Workspace = Backbone.Router.extend({
    routes: {
        ':resource/:num':'edit',
        ':resource':'list'
    },

    list:function(name){
        var tplname = [name,"list"].join("/");
        var template = Templates[tplname];
        
        $.getJSON(name, {}, function(data){
            console.log(data);
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


            MainContainer.append(wrap);


        }).error(function(){
            console.log("err");
        });

    }
});

var syssh = new Workspace();
Backbone.history.start(); 






















});
});