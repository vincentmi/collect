//
// Author : Vincent Mi (miwenshu@gmail.com)
// Web : vnzmi.com
// License: The MIT License (MIT)
//
(function( $ ) {
    //检测指定对象的滚动条 进行菜单切换
    $.fn.scrollwatch = function(options) {
        var settings = $.extend( {
            //激活的菜单添加的class
            'active': 'active', //active class name
            //菜单筛选器默认会检查对象中的li标签
            'item'  : 'li', //item class filter
            //菜单元素中指定内容块的属性名称
            'target': 'data-watch', //target attr name,
            //滚动条容器
            'container': document,
            'scrollMargin': 0 ,
            //堆栈菜单,滚动时取消菜单高度补充
            'stack': false
        }, options);

        return this.each(function(){
            var nav = $(this)
            var old = 0

            var valid  = function(obj){

                return obj.attr(settings.target) && $(obj.attr(settings.target)).length > 0
            }

            $(settings.item,nav).bind('click',function(){
                var that = $(this)
                if(!valid(that)){
                    return
                }
                var target = $(that.attr(settings.target))
                var ua  = navigator.appName.toLowerCase();
                if(settings.container == document){
                    var container = $(document.documentElement);
                    var to = target.offset().top;
                    var doExtra = true ;
                }else{
                    var container = $(settings.container)
                    var to = target.offset().top - container.offset().top + container.scrollTop()
                    var doExtra = false
                }

                to = to - settings.scrollMargin;
                if(!settings.stack){
                    to -= nav.height()
                }
                to = parseInt(to)
                //console.log(to,target.offset().top ,container.offset().top ,container.scrollTop());
                container.animate({'scrollTop': to},500,'swing');
                if(doExtra){
                    $(document.body).animate({'scrollTop': to},500,'swing');
                }
            })

            $(settings.container).scroll(function(){
                var menuTop = nav.offset().top + nav.height()
                var menuIndex = null
                var menuIndexAbs = 9999
                var menuItems = []
                var scrollDown = menuTop > old
                old = menuTop
                //console.log(menuTop)
                $(settings.item,nav).each(function(i,obj){
                    var item = $(obj)
                    if(!valid(item)){
                        return
                    }
                    var contentTop = $(item.attr(settings.target)).offset().top ;
                    menuItems.push(item);
                    var off  = Math.abs(menuTop - contentTop);
                    if(off < menuIndexAbs){
                        menuIndex = item
                        menuIndexAbs = off
                    }
                });
                for(var i = 0 ,m=menuItems.length ; i<m;i++){
                    if(menuItems[i] == menuIndex) {
                        menuItems[i].addClass('active')
                    }else{
                        menuItems[i].removeClass('active')
                    }
                }
            })

            $(settings.container).trigger('scroll')
        });
    };
})( jQuery );