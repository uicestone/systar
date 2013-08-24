define(function(require,exports,module){
    "use strict";
    var ENV = window.ENV;
    var PRODUCT = "product";
    var store = require("store");
    var TPL = "tpl";


    function storeKey(type,path){
        return [type,path].join(":");
    }

    var TemplateEngine = {
        loadAll:function(done){
            var fetchList = [];
            var tplList = window.templateList;
            var tpl = {};

            // 需要加载，而local storage里没有的模板，放入fetchList
            _.each(tplList,function(name){
                var tpl_content = store.get(storeKey(TPL,name)); 
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
                        store.set(storeKey(TPL,key),tpl_content);
                    });

                    done(tpl);
                });
            }else{
                done(tpl);
            }
        },
        discard:function(name){
            store.remove(storeKey(TPL,name));
        }

    }

    module.exports = TemplateEngine;
});