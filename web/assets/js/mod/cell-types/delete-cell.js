define(function(require,exports,module){

    var DeleteCell = Backgrid.Cell.extend({
        render: function () {
            this.$el.empty();
            this.$el.css("width",20);
            this.$el.html('<a href="javascript:;" class="red"><i class="icon-trash bigger-130"></i></a>');
            this.delegateEvents();
            return this;
        },
        enterEditMode: function () {
            var model = this.model;
            var column = this.column;


            var row_id = this.model.get("id");
            console.log(this);
            row = this.model.collection.where({ id: row_id });
            console.log(row);
            if(confirm("sure?")){
                // grid.removeRow(row);
                this.model.destroy();
            }
        }
    });

    module.exports = DeleteCell;

});