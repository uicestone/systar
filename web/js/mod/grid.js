define(function(require){

    /**
     * 带标签的单元格
     */
    var TagCell = require("cell-types/tag-cell");

    /**
     * 删除按钮所在单元格
     */
    var DeleteCell = require("cell-types/delete-cell");

    /**
     * 头部添加行单元格
     */
    var HeaderAddCell = require("cell-types/header-add-cell");

    var cellTypeMap = {
        "tags":TagCell,
        "delete":DeleteCell
    };

    function Grid(name,result,el){
        var data = result.data;
        var total = result.total;
        if(!data){throw "`data` should be specified in /"+name;}
        if(!total){throw "`total` should be specified in /"+name;}
        var Row = Backbone.Model.extend({
            initialize:function(){
                Backbone.Model.prototype.initialize.apply(this, arguments);
                this.on("change", function (model, options) {
                  if (options && options.save === false) return;
                  model.save(null,{
                    emulateJSON:true
                  });
                });
            },
            urlRoot: name
        });

        /**
         * 可分页Rows
         */
        // TODO: parse config from template
        var settings = JSON.parse(el.attr("data-args"));
        var Rows = Backbone.PageableCollection.extend({
          model: Row,
          parse:function(resp){
            return resp.data;
          },
          mode:"server",
          state:_.extend({
            firstPage: 0,
            currentPage: 0,
            totalRecords: total
          },settings),
          url: name
        });

        /**
         * rows 实例
         * @param  {[type]} item [description]
         * @return {[type]}      [description]
         */
        var rows = new Rows(data.filter(function(item){
            return !item.hidden;
        }));
        


        /**
         * 由th生成符合BackGrid要求的columns
         */
        var editable = el.attr("data-editable") == "true";
        var columns = _.map(el.find("th").get(),function(el){
            el = $(el);
            var celltype = el.attr("data-cell");
            if(cellTypeMap[celltype]){
              celltype = cellTypeMap[celltype];
            }
            
            return {
                name:el.attr("data-name"),
                label:el.attr("data-label"),
                cell:celltype,
                editable:editable
            };
        });

        /**
         * grid 实例
         * @type {Backgrid}
         */
        var grid = new Backgrid.Grid({
          columns: columns,
          collection: rows
        });

        grid.on("add",function(){
          console.log("add");
        });

        /**
         * 插入多余一行
         */

        /**
         * 如果需要有删除按钮，则容器上会有data-removable属性， 
         * 为grid添加列，并添加删除事件。
         */
        if(el.attr("data-removable")){
            grid.insertColumn({
                name:"funcCell",
                label:"",
                cell:DeleteCell,
                headerCell:HeaderAddCell,
                editable:false
            });

            grid.collection.on("remove",function(model){
                console.log("remove",model);
            });
        }

        return grid;
    }

    return Grid;
    
});