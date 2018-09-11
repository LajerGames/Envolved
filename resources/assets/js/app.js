
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example', require('./components/Example.vue'));

const app = new Vue({
    el: '#app'
});

$(document).ready(function() {
    
    // Check if show sidebar button exists
    if ( $( '#sidebarCollapse' ).length ) {
 
        // Do the user actively choose to hide or show the sidebar
        $('#sidebarCollapse').on('click', function() {
            var doShow = false;
            if($('#sidebarCollapse').hasClass('inactive')) {
                doShow = true;
            }

            toggleSidebar(doShow);

            $.post(
                '/toggle-sidebar',
                { 
                    _token: $('meta[name=csrf-token]').attr('content'),
                    _method : 'POST',
                    data : {
                        show: doShow
                },
                function ($rtrn) {
                    console.log($rtrn);
                }
            });
        });

        // Do resizing of the window force toggle the sidebar
        var viewportInSmallMode = $( window ).width() <= 768;
        $( window ).resize(function() {
            var viewportWidth = $( window ).width();

            if(viewportWidth <= 768 && !viewportInSmallMode) {
                // Viewport went from large to small mode, toggle sidebar
                viewportInSmallMode = true;
                toggleSidebar(false);
            }

            if(viewportWidth > 768 && viewportInSmallMode) {
                // Viewport went from small to large mode, toggle sidebar
                viewportInSmallMode = false;
                toggleSidebar(true);
            }

        });

        function toggleSidebar(doShow) {
            if(doShow) {
                $('#sidebarCollapse').find('i').removeClass('glyphicon glyphicon-menu-right');
                $('#sidebarCollapse').find('i').addClass('glyphicon glyphicon-menu-left');
                $('a.navbar-brand').removeClass('brand-margin-left');
                $('#sidebar').removeClass('inactive');
                $('#sidebarCollapse').removeClass('inactive');
            } else {
                $('#sidebarCollapse').find('i').removeClass('glyphicon glyphicon-menu-left');
                $('#sidebarCollapse').find('i').addClass('glyphicon glyphicon-menu-right');
                $('a.navbar-brand').addClass('brand-margin-left');
                $('#sidebar').addClass('inactive');
                $('#sidebarCollapse').addClass('inactive');
            }
        }
     
    }
    // End region

    // Submit form outside <form> tags
    $('.submit-form').on('click', function() {
        var submitFormWithID = $(this).data('submit');

        if(submitFormWithID.length > 0) {
            $('#'+submitFormWithID).submit();
        }
    });

    var canDelete = false;
    $('.btn-delete').closest('form').on('submit', function(e) {
        if(!canDelete) {
            e.preventDefault();
            if(confirm('Delete this?')) {
                canDelete = true;
                $(this).submit();
            }
            return false;
        } else {
            canDelete = false;
        }
    });

    // Ability to overwrite a field onchange in a specific selectbox (req. data-field-to-overwrite on select)
    $('select.overwrite-field-onchange').on('change', function() {
        var chosenName = $(this).val() == 0 ? '' : $(this).find(':selected').text();
        $('#'+$(this).data('field-to-overwrite')).val(chosenName);
    });

    // Any element that has class onload-anchor will be scrolled to onload
    if($(".onload-anchor").length) {
        $([document.documentElement, document.body]).animate({
            scrollTop: $(".onload-anchor").offset().top
        }, 200);
    }

    // Make elements sortable
    $( function() {
        $( ".sortable" ).sortable();
        $( ".sortable" ).disableSelection();
    } );

    // Text messages
    $('div.control-buttons-parent').on('mouseenter', function() {
        $(this).find('.control-buttons').show(100);
    });
    $('div.control-buttons-parent').on('mouseleave', function() {
        $(this).find('.control-buttons').hide(100);
    });
    // End region

    // Speech bubble
    var $speech = $('#speechbubble');
    var tipContent;

    function moving() {
        $('.hastip').mousemove(function(e) {
            $speech.html(tipContent);
            $offset = 20; /*distance from mouse*/
            $speech.css("top", e.pageY);

            if (e.pageX <= ($(window).width() / 2)) {

                $speech.css("left", e.pageX + $offset);
                $speech.addClass('arrowleft');
                $speech.removeClass('arrowright');

            } else {

                $speech.css("left", (e.pageX - $speech.width() - ($offset * 2)));
                $speech.removeClass('arrowleft');
                $speech.addClass('arrowright');

            }
        });
    }

    $('.hastip').hover(showTip, hideTip);

    var showSpeech = '';
    function showTip() {
        tipContent = $(this).data('moretext');
        moving();

        showSpeech = setTimeout(function() {
            $speech.show();
        }, 1000);
    }

    function hideTip() {
        clearTimeout(showSpeech);
        $speech.hide();
    }

    // News

    // Enable adding of sections for articles
    $('div.tiny-section a.add-section-button').on('click', function() {
    
        var type = $('div.tiny-section select.add-section-select option:selected').val();
        $.post(
            '/add-news-section',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    type: type
                }
            },
            function (data) {
                $('#article-parts').append(data);
            }
        );

    });

    // Make sure we can remove added sections in the News module
    $('div.section').on('click', 'a.remove-link', function() {
        if(confirm('Remove section?')) {
            $(this).closest('div.form-group').remove();

            // TODO: Delete image if this is an image-container.

        }
    });

    // End region

    // Settings

    $('form#editor-settings a.add-tab').on('click', function() {
        
        $.post(
            '/add-tab',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST'
            },
            function (data) {
                $('div.tabs-container').append(data);
            }
        );

    });

    $('form#editor-settings').on('click', 'a.remove-link', function() {
        if(confirm('Remove section?')) {
            //$(this).closest('div.form-group').remove();
            alert('check if this is removable by checking if the tab is')
            // TODO: Delete image if this is an image-container.

        }
    });

    // End region

    // Region: Builder

    // Run this once before resize
    checkStoryArchContainerSize();
    $(window).resize(function() {
        
        checkStoryArchContainerSize();
        
    });

    function checkStoryArchContainerSize() {
        // Story arch container
        var storyArchContainer = $('#story-arch-container'),
            storyArchContainerWidth = storyArchContainer.width();

        // change css to elements according to the VP size
        var removeMarginRightFromEveryNth = 0;
        if(storyArchContainerWidth >= 640 && storyArchContainerWidth < 980) {
            removeMarginRightFromEveryNth = 2;
        } else if(storyArchContainerWidth >= 980 && storyArchContainerWidth < 1320) {
            removeMarginRightFromEveryNth = 3;
        } else if(storyArchContainerWidth >= 1320 && storyArchContainerWidth < 1660) {
            removeMarginRightFromEveryNth = 4;
        } else if(storyArchContainerWidth >= 1660 && storyArchContainerWidth < 2000) {
            removeMarginRightFromEveryNth = 5;
        } else if(storyArchContainerWidth >= 2000 && storyArchContainerWidth < 2340) {
            removeMarginRightFromEveryNth = 6;
        } else {
            removeMarginRightFromEveryNth = 0;
        }

        removeRightMargin(removeMarginRightFromEveryNth);
    }

    function removeRightMargin(everyNthChild) {

        var margin = 40;

        if(everyNthChild < 2) {
            $('#story-arch-container > .arch-container').css('margin-right', margin+'px');
            return;
        }

        // First set the setting on all of the elements
        $('#story-arch-container > .arch-container').css('margin-right', margin+'px');
        $('#story-arch-container > .arch-container:nth-child('+everyNthChild+'n)').css('margin-right', '0px');
        $('#story-arch-container > .arch-container').show(100);


    }

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

    // End region
});
