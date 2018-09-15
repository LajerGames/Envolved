$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press shift + f to search
        if(e.which === 70 && e.shiftKey ) {
            
            var searchWindow = $('#search-window');
            searchWindow.modal();
            setTimeout(function() {
                searchWindow.find('input.form-control').attr('autofocus', true).focus();
            }, 500);

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

});