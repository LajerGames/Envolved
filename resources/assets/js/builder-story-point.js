$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press ctrl + shift + x for new
        if(e.which === 24 && e.shiftKey && e.ctrlKey) {

            // Use x to add story point only if button is not disabled
            let moreButton = $('.more-button');
            if(!moreButton.hasClass('disabled')) {
                moreButton.click();
            }

        }

        // Press ctrl + shift + a to add a new story-point to story-point
        if(e.which === 1 && e.shiftKey && e.ctrlKey) {
            
            $('div.story-point-shadow-container.active').find('a.add-story-point-to-this').click();

        }

        // Press ctrl + shift + u to add a new story-point to story-point
        if(e.which === 21 && e.shiftKey && e.ctrlKey) {
            
            $('div.story-point-shadow-container.active').find('a.update-story-point').click();

        }

        // Press ctrl + shift + l to make open story point transparent
        if(e.which === 12 && e.shiftKey && e.ctrlKey) {

            $('div.story-point-shadow-container.active').find('a.story-point-container-top-opacity-icon-container').click();

        }

        // Press ctrl + shift + k to delete open story point
        if(e.which === 11 && e.shiftKey && e.ctrlKey) {

            if($('#delete-story-point').is(':visible')) {
                $('#delete-story-point').find('button.btn-danger').click();
            } else {
                $('div.story-point-shadow-container.active').find('a.story-point-oprions-container-delete-story-point').click();
            }

        }

        // Press ctrl + shift + e to open options for active story point
        if(e.which === 5 && e.shiftKey && e.ctrlKey) {

            $('div.story-point-shadow-container.active').find('a.story-point-options-menu').click();

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

                    // Decide whether or not we need to update the storypoint form.

                    // Find the story point container that we've added a "leads-to" to.
                    var storyPointContainer = $('div.panel-body').find('div[data-story-point-id="' + parentStoryPointID + '"]'),
                        storyPointType      = storyPointContainer.data('story-point-type');

                    // Now some story-point-types may need to have their specialized input refreshed on new "leads-to". Find out if this is one of them
                    switch(storyPointType) {
                        case "condition" :
                        case "text_outgoing" :
                        case "phone_call_outgoing_voice" :

                            // Find the appropriate container
                            var storyPointSpecializedInputContainer = storyPointContainer.find('div.story-point-form-specialized-input'),
                                generatedID                         = storyPointContainer.find('form').data('generated-id');
                            
                            // This one needs to - let's make sure that happens.
                            updateStoryPointSpecializedInput(parentStoryPointID, storyPointSpecializedInputContainer, generatedID);

                            break;
                        default :
                            // All other story-point-types needs to have their Add story-point button removed
                            storyPointContainer.find('a.add-story-point-to-this').hide();
                            break;
                    }

                    updateStoryPointLeadsTo(parentStoryPointID);
                }

                // Let's look through all of the elements and see if we can find the glyphicon-fire - if we can, then make sure that the add-story-point in the top right corner is disabled, otherwise - enable it
                storyPointStart = $('div.panel-body').find('div.story-point-container-top > span.glyphicon.glyphicon-fire');

                if(storyPointStart !== undefined) {
                    $('.add-story-point.btn-success').addClass('disabled');
                } else {
                    $('.add-story-point.btn-success').removeClass('disabled');
                }

                // Close the modal
                $('#new-story-point-window').modal('hide');

                // Reset the search fields.
                resetSearchFields();
            }
        );

   }

   // Update the form input that makes each 
   function updateStoryPointSpecializedInput(storyPointID, storyPointSpecializedInputContainer, generatedID) {

        var container = storyPointSpecializedInputContainer;

        $.post(
            '/update-story-point-specialized-input',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_point_id: storyPointID,
                    generated_id: generatedID
                }
            },
            function (data) {

                // Now we've recieved a new rendition of the specialized form input
                var parsedData = JSON.parse(data);

                // Now update the specialized container with the new input
                container.html(parsedData);

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

                var parsedData = data.length > 0 ? JSON.parse(data) : '';
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
                    var storyPointID = parsedData.story_point_id,
                    html = parsedData.story_point_html;
                
                    // TODO: Remove the old story_point_container

                    checkContainerSize('points');

                    panelBody.append(html);
                }
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

    // Story point "burger menu"....thingy
    $('div.panel-body').on('click', 'a.story-point-options-menu', function(e) {

        let menuOptionsContainer = $(this).siblings('.story-point-options-menu-options-container');

        if(menuOptionsContainer.css('display') == 'block') {
            menuOptionsContainer.hide();
        } else {
            menuOptionsContainer.show();
        }

        e.stopPropagation();
    });

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

        // Set z-index up
        adjustZIndex(storyPointContainer.find('div.story-point-container-middle-and-bottom'), 2);

        // Show
       toggleIconOpacity(storyPointContainer.find('a.story-point-options-menu'), 'show', animationTime);

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

   function adjustZIndex(element, indexno) {
       element.css('z-index', indexno);
   }

   // close all story point forms, also inactivate everything else
   function closeStoryPointForms() {
        var animationTime = 500,
            // Get all story-point-containers that has a shadow container that has the class active
            storyPointsToClose = $('div.panel-body').find('div.story-point-container div.story-point-shadow-container.active').closest('div.story-point-container');

        // At first loop through it all and close all other story points
       storyPointsToClose.each(function() {
           var  formContainer = $(this).find('div.story-point-form-container')
                formContainer.hide(animationTime);

           // Hide the toggleIconOpacity
           toggleIconOpacity($(this).find('a.story-point-options-menu'), 'hide', animationTime);

           toggleStoryPointOpacity($(this), false, animationTime);

           // Make sure active classes removed
           $(this).find('div.story-point-shadow-container').removeClass('active');
       });

       // Then wait for animation time and remove all remove all z-indexes and make sure opacity is reset back to 1
       setTimeout(function() {

           storyPointsToClose.each(function() {
               adjustZIndex($(this).find('div.story-point-container-middle-and-bottom'), 1);
           });

       }, animationTime);


   }

    $('div.panel-body').on('click', 'a.story-point-container-top-opacity-icon-container', function() {

        var iconContainer = $(this),
            makeStoryPointTransparent = iconContainer.css('opacity') == 1;

        toggleStoryPointOpacity($(this).closest('div.story-point-container'), makeStoryPointTransparent, 500);

    });

    $('div.panel-body').on('click', 'a.story-point-oprions-container-delete-story-point', function() {

        // Story point container and data
        let storyPointContainer = $(this).closest('div.story-point-container'),
            storyPointLeadsTo   = storyPointContainer.find('.story-pointleads-to-container'),
            storyPointID        = storyPointContainer.data('story-point-id');

        let isDeleteStoryPointWindowSmall = (storyPointLeadsTo.find('a').length == 0),
            deleteStoryPointWindow = $('#delete-story-point'),
            deleteStoryPointWindowModal = deleteStoryPointWindow.find('.modal-dialog'),
            deleteStoryPointWindowConfirmInput = deleteStoryPointWindow.find('input[name=confirm]'),
            deleteStoryPointWindowOverviewContainer = deleteStoryPointWindow.find('.delete-story-point-overview');

        // If this story point leads nowhere, we will make it easier to delete by not forcing the user to type confirm first.
        // Also we will make the confirmation window much smaller
        if(isDeleteStoryPointWindowSmall) {

            // Story point leads nowhere

            // Make it small
            deleteStoryPointWindowModal.removeClass('modal-lg').addClass('modal-sm');

            // Disable and hide the confirm input
            deleteStoryPointWindowConfirmInput.prop('disabled', true).hide().val('');

            // Remove all text in the overview container
            deleteStoryPointWindowOverviewContainer.html("");

        } else {

            // story point leads somewhere

            // Make it big
            deleteStoryPointWindowModal.removeClass('modal-sm').addClass('modal-lg');

            // Disable and hide the confirm input
            deleteStoryPointWindowConfirmInput.prop('disabled', false).show().val('');

            // Remove all text in the overview container
            deleteStoryPointWindowOverviewContainer.html("<h2>Please hold, we're calculating...</h2>");

        }

        if(deleteStoryPointWindow.length) {
            deleteStoryPointWindow.modal();
            setTimeout(function() {
                deleteStoryPointWindow.find('input.form-control').attr('autofocus', true).focus();
            }, 500);
        }

        $.post(
            '/get-story-points-to-delete-via-story-point-id',
            {
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: $('#story_id').val(),
                    story_point_id: storyPointID
                }
            },
            function (markup) {

                let decodedMarkup = JSON.parse(markup);

                let headline = isDeleteStoryPointWindowSmall ? "<h4>Delete</h4>" : "<h2>This action will delete</h2>";

                deleteStoryPointWindowOverviewContainer.html(headline+decodedMarkup);


                // What happens when we click the delete button? Depends on the story point
                deleteStoryPointWindow.find('button.btn-danger').off().on('click', function() {

                    // Can we click the button?
                    let canDelete = true;
                    if(!isDeleteStoryPointWindowSmall) {

                        if(deleteStoryPointWindowConfirmInput.val() != "CONFIRM") {

                            deleteStoryPointWindowConfirmInput.focus();

                            canDelete = false;

                            alert("Please type CONFIRM in the input field, to confirm this action");
                        }

                    }

                    if(canDelete) {

                        // Remove all text in the overview container
                        deleteStoryPointWindowOverviewContainer.html("<h4>Deletion in progress...</h4>");

                        $.post(
                            '/delete-story-points-via-story-point-id',
                            {
                                _token: $('meta[name=csrf-token]').attr('content'),
                                _method : 'POST',
                                data : {
                                    story_id: $('#story_id').val(),
                                    story_point_id: storyPointID
                                }
                            },
                            function (data) {

                                let decodedData = JSON.parse(data);

                                // Now go through the deleted story points and remove them from the board
                                if(Object.keys(decodedData.deleted).length > 0) {

                                    for(var property in decodedData.deleted) {

                                        // Find the storypoint containers and remove them
                                        $('div.panel-body').find('div[data-story-point-id="' + decodedData.deleted[property] + '"]').remove();
                                    }

                                }

                                // Loop through the affected rows and ensure that their leads to is updated
                                if(Object.keys(decodedData.affected).length > 0) {

                                    for(var property in decodedData.affected) {

                                        // Find the storypoint containers and remove them
                                        updateStoryPointLeadsTo(decodedData.affected[property]);
                                    }

                                }

                                // Did we delete the story point starting arch
                                if(decodedData.deleted_arch_starting_point) {
                                    $('div.panel-body').find('.add-story-point').removeClass('disabled')
                                    $('#story_arch_start_story_point_id').val(0);
                                }

                                deleteStoryPointWindowOverviewContainer.html("<h4>Done</h4>");

                                deleteStoryPointWindow.modal('hide');

                            }
                        );

                    }

                });


            }
        );

    });

    function toggleStoryPointOpacity(storyPointContainer, makeTransparent, animationTime) {

       var storyPointOpacity    = makeTransparent ? 0.25 : 1,
           iconOpacity          = makeTransparent ? 0.50 : 1;

        // Update story point body opacity
        storyPointContainer.find('div.story-point-container-middle-and-bottom').fadeTo(animationTime, storyPointOpacity);

        // Update icon container opacity
        storyPointContainer.find('a.story-point-container-top-opacity-icon-container').fadeTo(animationTime, iconOpacity);
    }

    function toggleIconOpacity(iconContainer, newStatus, animationTime) {

       if(newStatus == 'hide') {
           iconContainer.hide(animationTime);
       } else {
           iconContainer.show(animationTime);
       }

    }

   // Update a story-point
   $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.update-story-point', function() {

        var storyID         = $('#story_id').val(),
            that            = $(this),
            form            = that.closest('form'),
            storyPointID    = that.closest('div.story-point-container').data('story-point-id')

       // Say that we're updating
       that.attr('disabled', true);
       that.html('wait...');

       $.ajax({
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                _method: 'POST'
            },
            url: '/save-story-point-form',
            method: 'POST',
            data: new FormData(form[0]),
            dataType: 'JSON',
            contentType: false,
            cache: false,
            processData: false,
            success: function (data) {

                // BUG: The data should be JSON encoded... but somehow it's not... I don't wanna mess with it anymore... It seems to work, so it's fine for now
                var parsedData = data;

                that.closest('div.story-point-container').find('div.story-point-container-top span.story-point-container-top-name').html(parsedData);

                // Now are there any special functions here?
                var runJS = form.find('input.run-js-on-save');
                if(runJS !== undefined) {
                    switch(runJS.data('js-function')) {
                        case 'addLeadsToToStoryPoint' :
                                addLeadsToToStoryPoint(storyPointID, runJS.data('id'), runJS.data('type'), runJS.data('action'));
                            break;
                        case 'refreshStoryPoint' :
                            let content                             = $('div.container'),
                                storyPointContainer                 = content.find('div[data-story-point-id="' + storyPointID + '"]'),
                                storyPointSpecializedInputContainer = storyPointContainer.find('div.story-point-form-container div.story-point-phone-call-voice-incomming-updatable-section'),
                                generatedID                         = storyPointContainer.find('form').data('generated-id');

                            updateStoryPointSpecializedInput(storyPointID, storyPointSpecializedInputContainer, generatedID);
                            break;
                    }
                }

                that.attr('disabled', false);
                that.html('Update');

            }
        });

        /*
        $.post(
            '/save-story-point-form',
            {
                _token: $('meta[name=csrf-token]').attr('content'),
                _method: 'POST',
                type        : 'POST',
                cache       : false,
                contentType : false,
                processData : false,
                data: {
                    story_id: storyID,
                    story_point_id: storyPointID,
                    data: form.serialize()
                },
                dataType: 'JSON'
            },
            function (data) {

                var parsedData = JSON.parse(data);

                that.closest('div.story-point-container').find('div.story-point-container-top span.story-point-container-top-name').html(parsedData);

                // Now are there any special functions here?
                var runJS = form.find('input.run-js-on-save');
                if(runJS !== undefined) {
                    switch(runJS.data('js-function')) {
                        case 'addLeadsToToStoryPoint' :
                                addLeadsToToStoryPoint(storyPointID, runJS.data('id'), runJS.data('type'), runJS.data('action'));
                            break;
                    }
                }

                that.attr('disabled', false);
                that.html('Update');

            }
        );
*/
   });

   // Add story to focused story-point
   $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.add-story-point-to-this', function() {
       
        var storyID         = $('#story_id').val(),
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
        STORY POINT SPECIAL TYPE JAVASCRIPT
   */

   // Variable

   // Catch datalist change event (Works for everywhere where you can choose a variable)
   $('div.panel-body').on('change', '.story-point-variable-choose-variable', function () {

        var selectecVariable    = $(this);
        var storyPointForm      = selectecVariable.closest('form');
        var generatedID         = storyPointForm.data('generated-id');
        
        storyPointForm.find('option').each(function() {

            if($(this).val() == selectecVariable.val()) {

                // Find the correct chosen-variable input
                var formGroupContainer = selectecVariable.closest('div.form-group-container'); 
                
                // Set the hidden input field to the value in the data field
                formGroupContainer.find('.story-point-variable-choosen-variable').val($(this).data('id'));

                // Now create the field selectecVariable will contain the value
                $.post(
                    '/update-story-point-variable-input',
                    { 
                        _token: $('meta[name=csrf-token]').attr('content'),
                        _method : 'POST',
                        data : {
                            story_id: $('#story_id').val(),
                            variable_type: $(this).data('type'),
                            generated_id: generatedID,
                            input_name: formGroupContainer.data('input-name'),
                            input_id: formGroupContainer.data('input-id')
                        }
                    },
                    function (data) {
                        var html = JSON.parse(data);

                        // Find the correct span
                        var valueInputContainer = formGroupContainer.find('.story-point-variable-value-input');

                        // In the form - remove the current value-input and add the new one
                        valueInputContainer.html(html);
        
                    }
                );

                // change operator options value
                $.post(
                    '/update-story-point-variable-refresh-operators',
                    {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        _method : 'POST',
                        data : {
                            variable_type: $(this).data('type')
                        }
                    },
                    function (data) {

                        var optionsHtml = JSON.parse(data);

                        formGroupContainer.find('select.story-point-variable-choose-variable-operator').html(optionsHtml);

                    }
                );

                return false; // Stop loop
            }

        });

        return;
    });
    
    // Variable Condition - on change variable - set operator-options accordingly
    $('div.panel-body').on('change', '.story-point-variable-condition-choose-variable', function () {
        
        var selectecVariable    = $(this);
        var storyPointForm      = selectecVariable.closest('form');
        var generatedID         = storyPointForm.data('generated-id');
        
        storyPointForm.find('option').each(function() {

            if($(this).val() == selectecVariable.val()) {

                // Find the correct chosen-variable input
                var formGroupContainer = selectecVariable.closest('div.form-group-container'); 

                $.post(
                    '/update-story-point-variable-condition-choose-operator',
                    { 
                        _token: $('meta[name=csrf-token]').attr('content'),
                        _method : 'POST',
                        data : {
                            type: $(this).data('type'),
                            number: formGroupContainer.data('number'),
                            generated_id: generatedID
                        }
                    },
                    function (data) {
                        var html = JSON.parse(data);

                        // Find the correct span
                        var valueInputContainer = formGroupContainer.find('.story-point-variable-condition-operator-section');

                        // In the form - remove the current value-input and add the new one
                        valueInputContainer.html(html);
        
                    }
                );

                return false; // Stop loop
            }

        });

    });

    // Leads to chooser (Used in multiple story-point types)
    $('div.panel-body').on('change', '.story-point-choose-leads-to', function () {
        
        var selectecVariable    = $(this);
        var storyPointForm      = selectecVariable.closest('form');
        
        storyPointForm.find('option').each(function() {

            if($(this).val() == selectecVariable.val()) {

                selectecVariable.closest('div.form-group-container').find('.story-point-chosen-leads-to').val($(this).data('id'));

                return false; // Stop loop
            }

        });

    });

    // Redirect - change type
    $('div.panel-body').on('change', '.story-point-redirect-select-type', function () {

        // Get some relevant values
        var thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            storyPointID = thisFormContainer.find('input[name="story_point_id"]').val(),
            selectedIDInput = thisFormContainer.find('.story-point-redirect-selected-id'),
            runJSOnSave = thisFormContainer.find('.run-js-on-save');

        $.post(
            '/update-story-point-redirect',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_point_id: storyPointID,
                    type: $(this).val()
                }
            },
            function (data) {

                var html = JSON.parse(data);

                // Insert new data into the datalist
                destinationDatalist.html(html);

                // Set the hidden field that saves the ID of the chosen destination to nothing
                thisFormContainer.find('.story-point-redirect-selected-id').val('');

                // Set the chosen destination field to ''
                thisFormContainer.find('.story-point-redirect-choose-destination').val('');

                // Set some important values on the run JS on save input - to ''
                runJSOnSave.data('id', '');
                runJSOnSave.data('type', '');
            }
        );

    });

    // Redirect - choose destination
    $('div.panel-body').on('change', '.story-point-redirect-choose-destination', function () {
        
        var selectedDestination = $(this),
            thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            selectedIDInput = thisFormContainer.find('.story-point-redirect-selected-id'),
            runJSOnSave = thisFormContainer.find('.run-js-on-save');
        
        destinationDatalist.find('option').each(function() {

            if($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                // Set some important values on the run JS on save input - to make sure we do the right thing after save
                runJSOnSave.data('id', $(this).data('id'));
                runJSOnSave.data('type', thisFormContainer.find('.story-point-redirect-select-type option:selected').val());

                return false; // Stop loop
            }

        });

    });

    // Start new thread - choose destination
    $('div.panel-body').on('change', '.story-point-start-new-thread-selected-id', function () {


        var selectedDestination = $(this),
            thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            selectedIDInput = thisFormContainer.find('.story-point-start-new-thread-selected-id'),
            runJSOnSave = thisFormContainer.find('.run-js-on-save');

        destinationDatalist.find('option').each(function() {

            if($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                // Set some important values on the run JS on save input - to make sure we do the right thing after save
                runJSOnSave.data('id', $(this).data('id'));

                return false; // Stop loop
            }

        });

    });

    // Redirect - update leads-to
    var addLeadsToToStoryPoint = function(storyPointID, ref, type, action) {

        action = action === undefined ? 'replace' : action;

        $.post(
            '/handle-story-point-reference',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method: 'POST',
                data: {
                    story_point_id: storyPointID,
                    ref: ref,
                    type: type,
                    action: action
                }
            },
            function (data) {
                
                // Update storypoint leads to
                updateStoryPointLeadsTo(storyPointID);

            }
        );
    }

    // Text, incomming/outgoing and voice, incomming/outgoing - choose sender
    $('div.panel-body').on('change', '.story-point-interlocutor-name', function () {

        var selectedDestination = $(this),
            storyPointID = selectedDestination.data('storypoint-id');
            thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            selectedIDInput = thisFormContainer.find('.story-point-interlocutor-id');
        
            destinationDatalist.find('option').each(function() {

                if($(this).val() == selectedDestination.val()) {

                    // Set the appropriate value in the hidden ID field
                    var characterID = $(this).data('id');
                    selectedIDInput.val(characterID);

                    // When that's done, then let's find the settings for this user
                    $.post(
                        '/get-character-and-storypoint-settings',
                        { 
                            _token: $('meta[name=csrf-token]').attr('content'),
                            _method: 'POST',
                            data: {
                                story_point_id: storyPointID,
                                character_id: characterID
                            }
                        },
                        function (data) {
                            
                            var parsedData = JSON.parse(data);

                            // Update fields
                            timeToReplyInput = thisFormContainer.find('.story-point-text-time-to-reply');
                            if(timeToReplyInput !== undefined) {
                                timeToReplyInput.val(parsedData.text_time_to_reply);
                            }

                            timeBeforeRead = thisFormContainer.find('.story-point-text-time-before-read');
                            if(timeBeforeRead !== undefined) {
                                timeBeforeRead.val(parsedData.text_time_before_read);
                            }

                            timeToRead = thisFormContainer.find('.story-point-text-time-to-read');
                            if(timeToRead !== undefined) {
                                timeToRead.val(parsedData.text_time_to_reply);
                            }
            
                        }
                    );

                    return false; // Stop loop
                }

            });

    });

    // Phone call incomming voice and outgoing choose hang-up-arch and after-arch-action
    $('div.panel-body').on('change', '.choose-story-point-phone-call-hang-up-options', function () {


        var selectedDestination = $(this),
            thisFormGroupContainer = $(this).closest('.form-group'),
            destinationDatalist = thisFormGroupContainer.find('datalist'),
            selectedIDInput = thisFormGroupContainer.find('.story-point-phone-call-hang-up-options-selected-id');

        destinationDatalist.find('option').each(function() {

            if($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                return false; // Stop loop
            }

        });

    });

    // Phone number choose new story arch for text and call
    $('div.panel-body').on('change', '.choose-new-story-arch-phone-number', function () {


        var selectedDestination = $(this),
            thisFormGroupContainer = $(this).closest('.form-group'),
            destinationDatalist = thisFormGroupContainer.find('datalist'),
            selectedIDInput = thisFormGroupContainer.find('.story-point-phone-call-number-change-arch-selected-id');

        destinationDatalist.find('option').each(function() {

            if($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                return false; // Stop loop
            }

        });

    });

    // Phone number choose new story arch for text and call
    $('div.panel-body').on('change', '.choose-story-point-start-watcher-selected-arch-options', function () {


        var selectedDestination = $(this),
            thisFormGroupContainer = $(this).closest('.form-group'),
            destinationDatalist = thisFormGroupContainer.find('datalist'),
            selectedIDInput = thisFormGroupContainer.find('.story-point-starstory-point-options-ment-watcher-selected-arch-id');

        destinationDatalist.find('option').each(function() {

            if($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                return false; // Stop loop
            }

        });

    });


    // News item, insert - choose article
    $('div.panel-body').on('change', '.story-point-insert-news-item-name', function () {
        
        var selectedDestination = $(this),
            thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            selectedIDInput = thisFormContainer.find('.story-point-insert-news-item-id');
        
        destinationDatalist.find('option').each(function() {

            if($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                return false; // Stop loop
            }

        });

    });

});