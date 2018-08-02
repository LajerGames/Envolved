
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
    if ( $( "#sidebarCollapse" ).length ) {
 
        $("#sidebarCollapse").on("click", function() {

            console.log('blabla')

            $("#sidebar").toggleClass("active");
            $(this).toggleClass("active");
        });
     
    }

});
