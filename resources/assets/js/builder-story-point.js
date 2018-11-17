$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press shift + n for new
        if(e.which === 78 && e.shiftKey ) {
            
            $('.more-button').click();

        }

        // Press ctrl + shift + a to add a new story-point to story-point
        if(e.which === 1 && e.shiftKey && e.ctrlKey) {
            
            $('div.story-point-shadow-container.active').find('a.add-story-point-to-this').click();

        }

        // Press ctrl + shift + u to add a new story-point to story-point
        if(e.which === 21 && e.shiftKey && e.ctrlKey) {
            
            $('div.story-point-shadow-container.active').find('a.update-story-point').click();

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
        handleStoryPointAndRefreshContainer($(this).val());
        
   });

   /**
    * Insert or update a story point. Furthermore, create or update the container for the story point.
    * 
    * @param {string} type 
    */
   function handleStoryPointAndRefreshContainer(type) {

        // Get storyID and StoryArchID
        var storyID     = $('#story_id').val(),
            storyArchID = $('#story_arch_id').val(),
            editID      = 0,
            number      = 0,
            leads_to    = 0;
            
        $.post(
            '/handle-story-point',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: storyID,
                    story_arch_id: storyArchID,
                    edit_id: editID,
                    type: type,
                    number: number,
                    leads_to: leads_to
                }
            },
            function (storyPointID) {
                renderStoryPoint(storyPointID);

                // Close the modal
                $('#new-story-point-window').modal('hide');

                // Reset the search fields.
                resetSearchFields();
            }
        );

   }

   function renderStoryPoint(storyPointID) {

        var storyID     = $('#story_id').val();

        $.post(
            '/render-story-point-container',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: storyID,
                    story_point_id: storyPointID
                }
            },
            function (data) {
                var panelBody = $('div.panel-body'),
                    parsedData = JSON.parse(data),
                    storyPointID = parsedData.story_point_id,
                    html = parsedData.story_point_html;
                
                // TODO: Remove the old story_point_container

                panelBody.append(html);

                console.log(html);
            }
        );

   }

   function resetSearchFields() {
        $('input[name="chosen_story_point_type"]').val('');
        $('#new-story-point-window').find('input[name="story_point_type"]').val('');

        return;
   }

   // 
   $('div.story-point-shadow-container').on('click', function() {

        var storyID                     = $('#story_id').val(),
            storyPointContainer         = $(this).closest('div.story-point-container'),
            storyPointShadowContainer   = storyPointContainer.find('div.story-point-shadow-container'),
            storyPointID                = storyPointContainer.data('story-point-id'),
            storyPointContentArea       = storyPointContainer.find('div.story-point-container-middle div'),
            animationTime               = 500;

        // Don't proceed if story-point is already active
        if(storyPointShadowContainer.hasClass('active')) {
            return;
        }

        // Make sure container is empty
        storyPointContentArea.html('');
        closeStoryPointForms();

        // Make sure the active class is added
        storyPointShadowContainer.addClass('active');
        
        $.post(
            '/render-story-point-type-form',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method: 'POST',
                data: {
                    story_id: storyID,
                    story_point_id: storyPointID
                }
            },
            function (data) {
                var parsedData = JSON.parse(data);

                // Add the HTML
                storyPointContentArea.append(parsedData.html);

                // Show
                storyPointContentArea.show(animationTime);

                // Focus
                console.log(storyPointContentArea.find('input[name="name"]'));
                storyPointContentArea.find('input[name="name"]').focus();
            }
        );

   });

   // close all story point forms, also inactivate everything else
   function closeStoryPointForms() {
        var animationTime = 500;

       $('div.panel-body').find('div.story-point-container').each(function() {
           var  that = $(this),
                formContainer = $(this).find('div.story-point-form-container');
                formContainer.hide(animationTime);
           
           // Make sure active classes removed
           that.find('div.story-point-shadow-container').removeClass('active');
       });
   }

   // Update a story-point
   $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.update-story-point', function() {

        var storyID         = $('#story_id').val(),
            that            = $(this),
            storyPointID    = that.closest('div.story-point-container').data('story-point-id');

        $.post(
            '/save-story-point-form',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method: 'POST',
                data: {
                    story_id: storyID,
                    story_point_id: storyPointID,
                    data: that.closest('form').serialize()
                }
            },
            function (data) {
                
                var parsedData = JSON.parse(data);

                that.closest('div.story-point-container').find('div.story-point-container-top').html(parsedData);

            }
        );

   });

   // Add story to focused story-point
   $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.add-story-point-to-this', function() {
       console.log('DAV');
   });

});