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
                var viewport = $(settings.viewpoint , that);
                var slideWarp = $(settings.slideWarp , that);
                var slides = $(settings.slide , that);

                var slideAmount = slides.eq(0).outerWidth() * settings.slideNum;

                that.attr("slideview-index",settings.defaultIndex);
                that.attr("slideview-viewport",settings.viewpoint);
                that.attr("slideview-slide",settings.slide);
                that.attr("slideview-slideWarp",settings.slideWarp);

                viewport.css({overflow:"hidden"});

                slideWarp.css({
                    whiteSpace: "nowrap" ,
                    listStyle:"none", 
                    display:"inline-block",
                    position:"relative"}
                    );

                slides.css({display:"inline"});

            })
        },

        move : function (index){
          var slides = $(this.attr('slideview-slide') , this);

            var currentIndex = parseInt(this.attr('slideview-index'));

            if(index == 'prev') index = currentIndex -1 ;
            if(index == 'next') index = currentIndex + 1 ;
            console.log(slides.length);
            if(index < 0){
                index = slides.length - 1 ;
            }else if(index > slides.length - 1){
                index = 0 ;
            }else{
                index = parseInt(index)
            }

            var pos1 = $(this.attr('slideview-slideWarp'),this).offset();

            var pos2 = slides.eq(index).offset();

            console.log(pos1,pos2);
            this.attr('slideview-index',index);

            $(this.attr('slideview-slideWarp'),this).animate({left:  0 - (pos2.left - pos1.left)},500,'swing');
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