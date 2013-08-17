define(function(require,exports,module){
    
    var HeaderAddCell = Backgrid.HeaderCell.extend({
        render: function () {
            this.$el.empty();
            this.$el.css("width",20);
            this.$el.html('<a href="javascript:;" class="green"><i class="icon-plus bigger-130"></i></a>');
            this.delegateEvents();
            return this;
        },
        sort: function(columnName, direction, comparator ){
            console.log(this.collection);
            this.collection.create({},{
                wait: true
            });
        }
    });

    module.exports = HeaderAddCell; 

});