define(function(){

    return {
        getMeta:function(name){
            var meta = this.meta.filter(function(item){return item.name==name})[0];
            return meta ? meta.content : undefined;
        },
        getTag:function(name){
            var tag =  _.filter(this.tag,function(item){return item.name==name})[0];
            return tag ? tag.name : undefined;
        }
    }
})