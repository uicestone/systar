define(function(require,exports,module){
    "use strict";
    var ENV = window.ENV;
    var PRODUCT = "product";
    var store = require("store");


    function storeKey(path){
        return ["tpl",path].join(":");
    }

    var TemplateEngine = {
        loadAll:function(done){
            var fetchList = [];
            var tplList = window.templateList;
            var tpl = {};

            // 需要加载，而local storage里没有的模板，放入fetchList
            _.each(tplList,function(name){
                var tpl_content = store.get(storeKey(name)); 
                if(!tpl_content || ENV != PRODUCT){
                    fetchList.push(name);
                }else{
                    tpl[name] = tpl_content;
                }
            });

            var fetchPathList = _.map(fetchList,function(key){
                return "tpl/" + key + ".tpl";
            });
            if(fetchPathList.length){
                seajs.use(fetchPathList,function(){
                    _.each(arguments,function(tpl_content,i){
                        var key = fetchList[i];
                        tpl[key] = tpl_content;
                        store.set(storeKey(key),tpl_content);
                    });

                    done(tpl);
                });
            }else{
                done(tpl);
            }
        },
        load:function(name,done){
            var tpl = store.get(storeKey(name));
            if(tpl && ENV == PRODUCT){
                // console.log(tpl);
                done(tpl);
            }else{
                seajs.use("tpl/" + name + ".tpl",function(tpl){
                    store.set(storeKey(name),tpl);
                    done(tpl);
                });
            }
        },
        discard:function(name){
            store.remove(storeKey(name));
        }

    }

    module.exports = TemplateEngine;
});