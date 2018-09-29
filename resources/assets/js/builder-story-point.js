$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press shift + n for new
        if(e.which === 78 && e.shiftKey ) {
            
            $('.more-button').click();

        }

    });

    $('.more-button').on('click', function() {
        var storyPointWindow = $('#new-story-point-window');
        if(storyPointWindow.length) {
            storyPointWindow.modal();
            setTimeout(function() {
                storyPointWindow.find('input[name="story_point_type"]').attr('autofocus', true).focus();
            }, 500);
        }
        storyPointWindow.modal();
    });

    // Catch datalist change event
    $('#new-story-point-window').find('input[name="story_point_type"]').bind('change', function () {
        // Find selected value
        var that = $(this);
        $('#new-story-point-window').find('option').each(function() {

           if($(this).val() == that.val()) {
               // Set the hidden input field to the value in the data field
               $('input[name="chosen_story_point_type"]').val($(this).data('value')).trigger('change');

               return false; // Stop loop
           }

        });

        return;
   });

   // Register when a new story point type is chosen
   $('input[name="chosen_story_point_type"]').on('change', function() {
        alert($(this).val()+' blabla');
   });

});