
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

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

    // Click anywhere to close .popup-menu
    // TODO: make sure it does not close when we click inside it!
    $(document).click (function (e) {
        if(!$(e.target.closest('div')).hasClass('popup-menu')) {
            $('.popup-menu').hide();
        }
    });

    // Speech bubble
    var $speech = $('#speechbubble');
    var tipContent;

    function moving() {
        $(document).on('mousemove', '.hastip', function(e) {
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

    $(document).on('mouseover', '.hastip', showTip);
    $(document).on('mouseout', '.hastip', hideTip);
    //$(document).on('hover', '.hastip', showTip, hideTip).children();

    var showSpeech = '';
    function showTip() {
        tipContent = $(this).data('moretext');
        moving();

        showSpeech = setTimeout(function() {
            $speech.show();
        }, 750);
    }

    function hideTip() {
        clearTimeout(showSpeech);
        $speech.hide();
    }

    // Character

    $('.character-settings-phone-expandable').on('click', function() {
        $('.character-phone-expandable').toggle(200);

        // Check what glyph is active and make the switcharoo
        var glyphSpan = $(this).find('span.glyphicon');
        if(glyphSpan.hasClass('glyphicon-plus')) {
            glyphSpan.removeClass('glyphicon-plus');
            glyphSpan.addClass('glyphicon-minus');
        }
        else if(glyphSpan.hasClass('glyphicon-minus')) {
            glyphSpan.removeClass('glyphicon-minus');
            glyphSpan.addClass('glyphicon-plus');
        }
    });

    $('.character-settings-text-expandable').on('click', function() {
        $('.character-text-expandable').toggle(200);

        // Check what glyph is active and make the switcharoo
        var glyphSpan = $(this).find('span.glyphicon');
        if(glyphSpan.hasClass('glyphicon-plus')) {
            glyphSpan.removeClass('glyphicon-plus');
            glyphSpan.addClass('glyphicon-minus');
        }
        else if(glyphSpan.hasClass('glyphicon-minus')) {
            glyphSpan.removeClass('glyphicon-minus');
            glyphSpan.addClass('glyphicon-plus');
        }
    });

    // End region

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

    // Remove published time if published is set to no
    $('select#published').on('change', function() {
    
        // Find the closest form, then find the fields within that form to deactivate.
        var form = $(this).closest('form');

        // Deactivate or activate (No, toggle is no good here...)
        var active = $(this).val() == 0;

        // Do the action
        form.find('input#days_ago').val('').attr('disabled', active);
        form.find('input#time').val('').attr('disabled', active);
    });

    // Make sure we can remove added sections in the News module
    $('div.section').on('click', 'a.remove-link', function() {
        if(confirm('Remove section?')) {
            $(this).closest('div.form-group').remove();

            // Do we have an image to clean up?
            var imageName = $(this).closest('div').find('a.image-anchor').data('image-name');
            if(imageName !== undefined) {
                $('div.section').append('<input type="hidden" name="remove_images[]" value="' + imageName + '" />')
            }

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

    // Save



    // End region
});
