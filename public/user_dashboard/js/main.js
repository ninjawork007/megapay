(function($) {

    "use strict";

    jQuery(document).ready(function() {

        /*
         * -----------------------------------------------------------------
         *---------------------------Preloader------------------------------
         * -----------------------------------------------------------------
         */

        var fundlyWindow = $(window);
        var pagebody = $('html, body');
       

        fundlyWindow.on("load", function() {

            var preloader = jQuery('.preloader');
            var preloaderArea = jQuery('.preloader-area');
            preloader.fadeOut();
            preloaderArea.delay(200).fadeOut('slow');
            fundlyWindow.scrollTop(0);
        });



        /*
         * -----------------------------------------------------------------
         *-----------------------Scroll Top Events--------------------------
         * -----------------------------------------------------------------
         */



        var scrollTopBtn = $("#scroll-top-area");

        scrollTopBtn.on("click", function(e) {
            e.preventDefault();
            pagebody.animate({
                scrollTop: 0
            }, 2000);
        });

        fundlyWindow.on("scroll", function(e) {
            e.preventDefault();
            var top = fundlyWindow.scrollTop();
            var scrollTopArea = $("#scroll-top-area");
            if (top < 150) {
                scrollTopArea.css('display', 'none');                
                  
               // bg-primary.css('background', 'transparent');
            } else if (top >= 150) {
                scrollTopArea.css('display', 'block');
              //  bg-primary.css('background', '#303030');
            }
        });


        /*
         * -----------------------------------------------------------------
         *--------------------Animation using animate.css-------------------
         * -----------------------------------------------------------------
         */



        var animation1 = jQuery('.animation');

        animation1.waypoint(function() {
            var thisElement = $(this.element);
            var animation = thisElement.attr('data-animation');

            thisElement.css('opacity', '1');
            thisElement.addClass("animated " + animation).delay(2000);
        }, {
            offset: '75%',
        });

        /*
         * -----------------------------------------------------------------
         *------------------------------slicknav----------------------------
         * -----------------------------------------------------------------
         */

       

        //var menu = $("#menu");

        //menu.slicknav({
        //  label: '',
        //  duration: 1000,
        //  easingOpen: "easeOutBounce", //available with jQuery UI
        // });


    });

})(jQuery);