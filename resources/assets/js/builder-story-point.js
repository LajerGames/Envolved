$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press ctrl + shift + x for new
        if(e.which === 24 && e.shiftKey && e.ctrlKey) {
            
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

        // Press shift + f to search
        if(e.which === 6 && e.shiftKey && e.ctrlKey) {
            
            var selectStoryPointWindow = $('#select-story-point');
            if(selectStoryPointWindow.length) {
                selectStoryPointWindow.modal();
                setTimeout(function() {
                    selectStoryPointWindow.find('input.form-control').attr('autofocus', true).focus();
                }, 500);
            }

        }

        // What do we do with the enter key?
        if(e.which == 13) {

            // Check if we have focus in the choose story-point-no
            if($('input[name="select-story-point"]').is(':focus')) {
                
                $('#select-story-point button.btn-primary').click();

            }

        }
    });

    // Select story point number
    function selectStoryPointNumber(storyPointNo) {
        

        if(parseInt(storyPointNo) > 0) {

            var clickInput = $("div.panel-body").find("[data-story-point-no='" + storyPointNo + "']").find('div.story-point-container-top');

            if(clickInput.length > 0) {

                clickInput.click();

            }

        }
    }
    //story-point-leads-to-reference
    $('div.panel-body').on('click', 'a.story-point-leads-to-reference', function() {
        selectStoryPointNumber(parseInt($(this).data('story-point-no')));
    });
    $('#select-story-point button.btn-primary').on('click', function() {

        var storyPointNo = $('input[name="select-story-point"]'),
            storyPointNoVal = storyPointNo.val();

        selectStoryPointNumber(storyPointNoVal);

        storyPointNo.val('');

        // Close window
        $('#select-story-point').modal('hide');
    });

    $('.more-button').on('click', function() {

        setNewStoryPointParentID("");
        openNewStoryPointModal(0);

    });

    function openNewStoryPointModal(parentID) {

        var storyPointWindow = $('#new-story-point-window');

        // Do we show the parent chooser field?
        fieldToFocus = storyPointWindow.find('input[name="story_point_type"]');
        if(parentID == 0) {

            // So, we have no parent ID - that means that we should actually show the parent story point chooser
            // But we might also be in here because this story arch has no start story point and badly wants you to create one
            if(parseInt($('#story_arch_start_story_point_id').val()) == 0) {

                // Okay, we have no start story_point in this story arch for sure, make this the one!
                setNewStoryPointParentID(0);
                
                // Hide the parent story point chooser
                storyPointWindow.find('input[name="story_point_parent"]').hide();

            } else {

                // It seems we have a start story point in this story arch, show the parent chooser to make sure the user chooses a parent
                setNewStoryPointParentID("");

                // Set new field to focus
                fieldToFocus = storyPointWindow.find('input[name="story_point_parent"]');

                // Show the parent story point chooser
                fieldToFocus.show();

            }

        } else {

            // We found a parent ID, set it so we don't need to choose
            setNewStoryPointParentID(parentID);

            // Hide the parent story point chooser
            storyPointWindow.find('input[name="story_point_parent"]').hide();

        }
        
        if(storyPointWindow.length) {
            
            storyPointWindow.modal();

            setTimeout(function() {
                fieldToFocus.attr('autofocus', true).focus();
            }, 500);

        }

    }

    // Catch datalist change event
    $('#new-story-point-window').find('input[name="story_point_type"]').bind('change', function () {

         var storyPointWindow = $('#new-story-point-window');

        // Find selected value
        var that = $(this);
        storyPointWindow.find('option').each(function() {

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

        insertStoryPointAndRenderContainer($(this).val());
        
   });

   /**
    * Insert a story point. Furthermore, create the container for the story point.
    * 
    * @param {string} type 
    */
   function insertStoryPointAndRenderContainer(type) {
        // Get storyID and StoryArchID
        var storyID                 = $('#story_id').val(),
            storyArchID             = $('#story_arch_id').val(),
            parentStoryPointNumber  = parseInt($('#new-story-point-window').find('input[name="story_point_parent"]').val()),
            parentStoryPointID      = parseInt($('#new-story-point-window').find('input[name="parent_id"]').val());
            
        $.post(
            '/insert-story-point',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: storyID,
                    story_arch_id: storyArchID,
                    type: type,
                    parent_number: parentStoryPointNumber,
                    parent_id: parentStoryPointID
                }
            },
            function (storyPointIDs) {
                var storyPoints         = JSON.parse(storyPointIDs);

                if(storyPoints.error !== undefined) {

                    // An error occured - what was it?
                    adviceError(storyPoints.error);

                    resetSearchFields();

                    return;
                }

                var storyPointID        = storyPoints.story_point_id,
                    parentStoryPointID  = storyPoints.parent_story_point_id;

                // Let's render the newly created story point
                renderStoryPoint(storyPointID);

                // Let's update the parent story point's leads-to graphics so it's visible that something has changed
                if(parentStoryPointID > 0) {
                    updateStoryPointLeadsTo(parentStoryPointID);
                }

                // Close the modal
                $('#new-story-point-window').modal('hide');

                // Reset the search fields.
                resetSearchFields();
            }
        );

   }

   // Update story point leads to container
   function updateStoryPointLeadsTo(storyPointID) {
        var storyID             = $('#story_id').val();

        $.post(
            '/update-story-point-leads-to',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: storyID,
                    story_point_id: storyPointID
                }
            },
            function (data) {
                var parsedData = JSON.parse(data);
                // Find the right story point and append
                var storyPoint = $('div[data-story-point-id="' + storyPointID + '"]');

                updateContainer = storyPoint.find('div.story-pointleads-to-container');

                updateContainer.html("");

                updateContainer.append(parsedData);

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
                    parsedData = JSON.parse(data);
                
                if(parsedData == 'error') {
                    // TODO: Error occured - take appropriate action
                } else {
                    console.log(parsedData);
                    var storyPointID = parsedData.story_point_id,
                    html = parsedData.story_point_html;
                
                    // TODO: Remove the old story_point_container

                    panelBody.append(html);
                }

                console.log(html);
            }
        );

   }

   function resetSearchFields() {
        setNewStoryPointParentID('');
        var storyPointWindow = $('#new-story-point-window');
        storyPointWindow.find('input[name="chosen_story_point_type"]').val('');
        storyPointWindow.find('input[name="story_point_parent"]').val('');
        storyPointWindow.find('input[name="story_point_type"]').val('');

        return;
   }

   // 
   $('div.panel-body').on('click', 'div.story-point-container-top', function() {

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

        // Say that we're updating
        that.attr('disabled', true);
        that.html('wait...');

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

                that.attr('disabled', false);
                that.html('Update');

            }
        );

   });

   // Add story to focused story-point
   $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.add-story-point-to-this', function() {
       
        storyID         = $('#story_id').val(),
        that            = $(this),
        storyPointID    = that.closest('div.story-point-container').data('story-point-id');

        openNewStoryPointModal(storyPointID);

   });

   function setNewStoryPointParentID(parentID) {
    $('#new-story-point-window').find('input[name="parent_id"]').val(parentID);
   }

   function adviceError(error) {
       switch(error) {
           case 'no_parent_id' :
                alert('No parent ID');
               break;
       }
   }

   /*
        STORY POINT SPECIAL JAVASCRIPT
   */

   // Variable

   // Catch datalist change event
   $('#app').bind('change', '.story-point-variable-choose-variable', function () {

    // This may seem like a wierd way to find the real input - but there can be only one, since there can be only one storyPoint open at one time
    var selectecVariable = $('#app').find('.story-point-variable-choose-variable');
    var storyPointForm = selectecVariable.closest('form');
    
    storyPointForm.find('option').each(function() {

        if($(this).val() == selectecVariable.val()) {

            // Set the hidden input field to the value in the data field
            storyPointForm.find('.story-point-variable-choosen-variable').val($(this).data('id'));

            // Now create the field selectecVariable will contain the value
            $.post(
                '/update-story-point-variable-input',
                { 
                    _token: $('meta[name=csrf-token]').attr('content'),
                    _method : 'POST',
                    data : {
                        story_id: $('#story_id').val(),
                        variable_type: $(this).data('type'),
                        generated_id: $(this).data('generated-id')
                    }
                },
                function (data) {
                    var html = JSON.parse(data);

                    // Find the correct span
                    var valueInputContainer = storyPointForm.find('.story-point-variable-new-value');

                    // In the form - remove the current value-input and add the new one
                    valueInputContainer.html(html);
                    /*
                    // Find the right story point and append
                    var storyPoint = $('div[data-story-point-id="' + storyPointID + '"]');
    
                    updateContainer = storyPoint.find('div.story-pointleads-to-container');
    
                    updateContainer.html("");
    
                    updateContainer.append(parsedData);*/
    
                }
            );

            return false; // Stop loop
        }

    });

        return;
    });

});