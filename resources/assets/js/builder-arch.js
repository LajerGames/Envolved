$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press shift + f to search
        if(e.which === 70 && e.shiftKey ) {
            
            var searchWindow = $('#search-window');
            if(searchWindow.length) {
                searchWindow.modal();
                setTimeout(function() {
                    searchWindow.find('input.form-control').attr('autofocus', true).focus();
                }, 500);
            }

        }

    });

    // Catch datalist change event
    $('#search-window').find('input.form-control').bind('change', function () {
         // Find selected value
         var that = $(this),
             storyID = '',
             tabID = '',
             chosenID = '';
         $('#search-window').find('option').each(function() {
             
            if($(this).val() == that.val()) {
                tabID = $(this).data('tab-id');
                chosenID = $(this).data('id');
                storyID = $(this).data('story-id');

                return false;
            }

         });

         location.href = '/stories/'+storyID+'/builder/'+tabID+'?highlight='+chosenID;
    });

    $('.arch-enter').on('click', function() {
        var storyID = $(this).data('story-id'),
            archID = $(this).data('id');

        location.href = '/stories/'+storyID+'/builder/arch/'+archID;
    });

    // Story arch "burger menu"....thingy
    $('.arch-options-menu').on('click', function(e) {
        $(this).siblings('.arch-options-container').show();
        e.stopPropagation();
    });










    // Region: Scrollbar
    if($('.scroller-right').length && $('.scroller-left').length) {
        /** OVERFLOW ON SCROLLBAR */

        var itemPositions = []; //lets make this global for simplicity
        var currentPosition = 0;

        var widthOfList = function() {

            itemPositions = []; //just in case we need to reset it
            var marginWidth = 28;
            var wrapperWidth = $('.wrapper').innerWidth() - marginWidth;
            var itemsWidth = 0;
            itemPositions.push(0); //left most positions

            $('.list li').each(function() {

                var itemWidth = $(this).outerWidth(true);
                itemsWidth += itemWidth;

                if (itemsWidth > wrapperWidth) 
                {
                
                itemPositions.push( itemsWidth - wrapperWidth ); //push in the left offset to move to the right (or left)
                }

            });

            return itemsWidth;

        };

        // Used to check whether to show either arrow
        var reAdjust = function() {

            if (($('.wrapper').outerWidth()) < widthOfList()) {
                $('.scroller-right').css('visibility', 'visible');

                // Can we go further to the left?
                if (getLeftPosi() < 0) {
                    $('.scroller-left').css('visibility', 'visible');
                } else {
                    $('.scroller-left').css('visibility', 'hidden');
                }

                // Can we go further to the right?
                if(Math.ceil(($('.wrapper').outerWidth() - getLeftPosi())) > widthOfList()) {
                    $('.scroller-right').css('visibility', 'hidden');
                } else {
                    $('.scroller-right').css('visibility', 'visible');
                }

            } else {
                
                // Check if we've scrolled left, if we have, then scroll back
                if(getLeftPosi() < 0) {
                    $('.list').animate({
                        left:"0px"
                    }, 'fast', reAdjust);
                }

                $('.scroller-right').css('visibility', 'hidden');
                $('.scroller-left').css('visibility', 'hidden');
            }

        };

        var moveRight = function() {

            if (currentPosition + 1 < itemPositions.length) {
                $('.list').animate({
                    left: "-" + itemPositions[++currentPosition] + "px"
                }, 'slow', reAdjust);
            }

        }

        var moveLeft = function() {

            if (currentPosition - 1 >= 0) {
                $('.list').animate({
                    left:"-"+itemPositions[--currentPosition] + "px"
                }, 'slow', reAdjust);
            }

        }

        //gets the current left scroll position of the list
        var getLeftPosi = function(){
            return $('.list').position().left;
        };

        reAdjust();
        // Readjust each time window size changes
        $(window).on('resize', function(e) {

            reAdjust();

        });

        $('.scroller-right').click(function() {

            moveRight();

        });

        $('.scroller-left').click(function() {

            moveLeft();

        });
    }

    // End region
});