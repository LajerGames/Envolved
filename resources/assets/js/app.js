
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
        }
    });
});
