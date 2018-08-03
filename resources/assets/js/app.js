
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
            if($('#sidebarCollapse').hasClass('active')) {
                toggleSidebar(true);
            } else {
                toggleSidebar(false); 
            }
        });

        // Do resizing of the window force toggle the sidebar
        var viewportInSmallMode = $( window ).width() <= 768;
        console.log(viewportInSmallMode)
        console.log('______________________________________________________________________________________')
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
                $('a.navbar-brand').css('margin-left', '');
                $('#sidebar').removeClass('active');
                $('#sidebarCollapse').removeClass('active');
            } else {
                $('#sidebarCollapse').find('i').removeClass('glyphicon glyphicon-menu-left');
                $('#sidebarCollapse').find('i').addClass('glyphicon glyphicon-menu-right');
                $('a.navbar-brand').css('margin-left', '20px');
                $('#sidebar').addClass('active');
                $('#sidebarCollapse').addClass('active');
            }
        }
     
    }

});
