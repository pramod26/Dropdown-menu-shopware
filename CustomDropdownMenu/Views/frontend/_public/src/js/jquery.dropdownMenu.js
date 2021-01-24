$.plugin('customDropdownMenu', {

   defaults: {
       contentCls: 'has--content',
       tabDetail: '.tab-menu--product',
       tabCrossSelling: '.tab-menu--cross-selling'
   },
   
   init: function() {
	    var me = this;
	    var isiPad = navigator.userAgent.match(/iPad/i) != null;
        var navigationTop = 0;
        var param = decodeURI((RegExp('(?:action|jumpTab)=(.+?)(&|$)').exec(location.search) || [null, null])[1]);
        me.tabMenuProduct = $('body').find(me.opts.tabDetail).data('plugin_swTabMenu');
        me.$tabMenuCrossSelling = $('body').find(me.opts.tabCrossSelling);
        if($('.navigation--list-wrapper').length && param !== 'rating'){
        	navigationTop = parseInt($('.navigation--list-wrapper').offset().top);
        }

        $(document).ready(function(){
        	if (param !== 'rating') {
        		$(this).scrollTop(0);
        	}
        	else{
        	    var $tab = $('[data-tabName="' + param + '"]'),
        	        index = $tab.index() || 1;
        	        me.jumpToTab(index, $tab);
        	}
            if (('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0) || (navigator.userAgent.toLowerCase().match( /windows phone os 7/i ))){
            	$( '.navigation-main li:has(ul)' ).doubleTapToGo();
            }
            
            $('.navigation-main a[title]').mouseover(function () {
                $this = $(this);
                $this.data('title', $this.attr('title'));
                // Using null here wouldn't work in IE, but empty string will work just fine.
                $this.attr('title', '');
            }).mouseout(function () {
                $this = $(this);
                $this.attr('title', $this.data('title'));
            });
        });

        $(window).scroll(function () {
        	if (param !== 'rating') {
	    		if ($('.navigation-main').css('position') == 'fixed'){
	        	     $('.navigation-main .menu--level-1').css('margin-top','0px');
	    		}
	    		else{
	    			var newNavigationTop = parseInt(-($(this).scrollTop()));
	    			$('.navigation-main .menu--level-1').css('margin-top',(newNavigationTop) + 'px');
	    		}
        	}
        	if ($(".navigation-main .container").data('sticky')){
        		if(($(".header-main").offset().top+$(".header-main").outerHeight())-$(".page-wrap").offset().top<$(window).scrollTop()){
        			$(".navigation-main").addClass("sticky");
        		}
        		else{
        			$(".navigation-main").removeClass("sticky");
        		}
        	}
    	});
        if($('.navigation--list-wrapper').length){
	    	var docWidth = $(window).width(); 
	    	var containerWidth = $('.container').width(); 
	    	var liLastOffset = $('.navigation--list-wrapper ul:first > li').last().offset();
	        var menuWidth = liLastOffset.left+$('.navigation--list-wrapper ul:first > li').last().outerWidth();
	        menuWidth = Math.ceil(menuWidth)+1;
	        if(docWidth>containerWidth){
	        	docWidth = containerWidth;
	        }
	        if(menuWidth<docWidth){
	        	if(!isiPad){
	        		$(".navigation-main .container").attr("data-menu-scroller", 'false');
	        		$(".js--menu-scroller--arrow").remove();
	        		$(".navigation-main .container").removeClass('js--menu-scroller');
	        		$(".navigation--list").removeClass('js--menu-scroller--list');
	        		$(".navigation--entry").removeClass('js--menu-scroller--item');
	        		$(".navigation--list").attr('style','');
	        	}
	        }
	        var cachedWidth = $(window).width();
	        $(window).resize(function() {
	    		 docWidth = $(window).width();
	    		 menuWidth = liLastOffset.left+$('.navigation--list-wrapper ul:first > li').last().outerWidth();
	    		 menuWidth = Math.ceil(menuWidth)+1;
	    		 if(docWidth>containerWidth){
	 	        	docWidth = containerWidth;
	 	         }
	    		 if(menuWidth<docWidth){
	    			 if(!isiPad){
		                 $(".navigation-main .container").attr("data-menu-scroller", 'false');
		                 $(".js--menu-scroller--arrow").remove();
		                 $(".navigation-main .container").removeClass('js--menu-scroller');
		                 $(".navigation--list").removeClass('js--menu-scroller--list');
		                 $(".navigation--entry").removeClass('js--menu-scroller--item');
		                 $(".navigation--list").attr('style','');
	    			 }
	             }
	    		 else{
	    			 if($(window).width() !== cachedWidth){
	    				 window.location.href=window.location.href;
	    			 }
	    		 } 
	    	});
	
	    	$(".navigation-main li").hover(function(){
	    		var dropdownList =  $(this);
	            var dropdownOffset = $(this).offset();
	            var offsetLeft = dropdownOffset.left;
	            var dropdownWidth = dropdownList.width();
	            var subDropdown =  $(this).find('ul');
	            var subDropdownWidth = subDropdown.width();
	            var levels =  $(this).children("ul").length;
	
	            var isDropdownVisible = (offsetLeft + dropdownWidth <= docWidth);
	            var isSubDropdownVisible = (offsetLeft + dropdownWidth + (subDropdownWidth * levels) <= docWidth);
	            
	            if(isiPad){
	            	$('.product-slider--container.is--horizontal').css('overflow','visible');
	            	$('.product-slider--container.is--vertical').css('overflow','visible');
	            	$('.emotion--wrapper').css('overflow','visible');
	            	$('.content').css('overflow','hidden');
	            	$('.navigation--list.container').attr('style','');
	            	$('.page-wrap').addClass('ipadMenuFix');
	            	$('.navigation-main').css('z-index','9999');
	                $(".js--menu-scroller--list").css('overflow','hidden');
	            }
	            
	            $('.navigation-main li ul').css('display', '');
	            if (!isDropdownVisible || (!isSubDropdownVisible && levels > 0)) {
	            	$(this).addClass('pullLeft');
	            	docWidth = $(window).width();
	                var toLeft = docWidth-subDropdownWidth;
	                if(offsetLeft<toLeft){
	                	$(this).find('.menu--level-1').css('left',(offsetLeft) + 'px');
	                }
	                else{
	                	$(this).find('.menu--level-1').css('left',(toLeft) + 'px');
	                }
	            } else {
	                $(this).removeClass('pullLeft');
	                $(this).find('.menu--level-1').css('left',(offsetLeft) + 'px');
	            }
	    	},
	    	 function(){ 
	    		 	if(isiPad){
	    		 		$('.product-slider--container.is--horizontal').css('overflow','hidden');
	    		 		$('.product-slider--container.is--vertical').css('overflow','hidden');
	    		 		$('.emotion--wrapper').css('overflow','hidden');
	    		 		$('.content').css('overflow','show');
	    		 		$(".js--menu-scroller--list").css('overflow','show');
	    		 		$(".js--menu-scroller--list").css('overflow-x','scroll');
	    		 	}
	    	    }
	    	);
	    	
	    	$(".js--menu-scroller--arrow").on('touchstart', function () {
	    		$('.navigation-main li ul').hide();
	    	});
        }
   },
   
   jumpToTab: function (tabIndex, jumpTo) {
       var me = this;
       if (!$('body').hasClass('is--ctl-blog')) {
           me.tabMenuProduct.changeTab(tabIndex);
       }
       
       if (!jumpTo || !jumpTo.length) {
           return;
       }

       $('html, body').animate({
           scrollTop: $(jumpTo).offset().top
       }, 0);
   },

   destroy: function() {
     me._destroy();
   }
});
$(function() {
	StateManager.addPlugin('*[data-dropdownMenu="true"]','customDropdownMenu');
});