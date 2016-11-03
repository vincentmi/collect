//
// Author : Vincent Mi (miwenshu@gmail.com)
// Web : vnzmi.com
// License: The MIT License (MIT)
// 图片轮播
//
(function( $ ) {

    var defaults = {
        //slide
        'viewpoint' : '.slide-items',
        'slide'  : '.slide-items>ul>li',
        'slideWarp'  : '.slide-items>ul',
        //arrow
        'arrow': '.slide-arrow', 
        'arrowPrev': '.slide-arrow-prev', 
        'arrowNext': '.slide-arrow-next', 

        'slideNum':1,
        'defaultIndex':0

    };

    var methods = {

        init : function(options){
            var settings = $.extend( {} , defaults , options);

            return this.each(function(){
                var that = $(this)
                var viewpoint = $(settings.viewpoint , that);
                var slideWarp = $(settings.slideWarp , that);
                var slides = $(settings.slide , that);

                var slideAmount = slides.eq(0).outerWidth() * settings.slideNum;

                that.attr("slideview-index",settings.defaultIndex);

                viewpoint.css({overflow:"hidden"});

                slideWarp.css({
                    whiteSpace: "nowrap" ,
                    listStyle:"none", 
                    display:"inline-block",
                    position:"relative"}
                    );

                slides.css({display:"inline"});

                slideWarp.css({marginLeft:-500});
            })
        },

        debug : function(){

        }
    }

    $.fn.slideview = function(method) {

        if ( typeof method === 'object' || ! method ) {

            return methods.init.apply(this , arguments);
            
        } else if(methods[method]){
            return methods[method].apply(this,Array.prototype.slice.call( arguments, 1 ))
        }else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.slideview' );
        }    
        
    };
})( jQuery );