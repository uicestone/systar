define(function(){

    return {
        getMeta:function(name){
            var meta;
            if(!this.meta){return;}
            this.meta.filter(function(item){return item.name==name})[0];
            return meta ? meta.content : undefined;
        },
        getTag:function(name){
            var tag;
            if(!this.tag){return;}
            tag =  _.filter(this.tag,function(item){return item.name==name})[0];
            return tag ? tag.name : undefined;
        }
    }
})