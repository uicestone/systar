define(function(require,exports,module){
    var TagCell = Backgrid.Cell.extend({
        render: function () {
            var $el = this.$el;
            var data = this.model.toJSON();

            $el.empty();
            
            var content = data.content
            if(content.constructor !== Array){
                content = [content];
            }

            _.each(content,function(item){
                var tag = $("<span class=\"label label-info arrowed\">"+item+"</span>");
                $el.append(tag);
            });

            this.delegateEvents();
            return this;
        },
        enterEditMode: function () {
            console.log("here");
            var model = this.model;
            var column = this.column;

            console.log("model",this.model);

            var row_id = this.model.get("id");
            this.trigger("delete",row_id);
            console.log(this);
            // row = grid.collection.where({ id: row_id });
            // if(confirm("sure?")){
            //     grid.removeRow(row);
            //     this.model.destroy();
            // }
        }
    });

    module.exports = TagCell; 
});