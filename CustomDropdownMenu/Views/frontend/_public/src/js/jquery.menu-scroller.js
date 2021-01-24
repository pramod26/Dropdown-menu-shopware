;(function ($) {
    'use strict';
    
    var msPointerEnabled = window.navigator.msPointerEnabled,
    $body = $('body');
    
    $.overridePlugin('swMenuScroller', {  
    	/**
         * Creates all needed control items and adds plugin classes
         *
         * @public
         * @method initTemplate
         */
        updateScrollBarOffset: function () {
            var me = this,
                $list = me.$list,
                offset;

            offset = me.scrollBarOffset = Math.min(Math.abs($list[0].scrollHeight - $list.height()) * -1, me.scrollBarOffset);
            
            var docWidth = $(window).width();
            var containerWidth = $('.container').width();
            if(docWidth>containerWidth){
	        	docWidth = containerWidth;
	        }
            if($('.navigation--list-wrapper').length > 0){
	        	var liLastOffset = $('.navigation--list-wrapper ul:first > li').last().offset();
	            var menuWidth = liLastOffset.left+$('.navigation--list-wrapper ul:first > li').last().outerWidth();
	            menuWidth = Math.ceil(menuWidth)+1;
	            if(menuWidth>=docWidth){
		            $list.css({
		                'bottom': offset,
		                'margin-top': offset
		            });
	            }
	        }
       
            $.publish('plugin/swMenuScroller/onUpdateScrollBarOffset', [ me, offset ]);
        },
    });
})(jQuery);