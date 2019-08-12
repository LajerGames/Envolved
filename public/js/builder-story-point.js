/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 41);
/******/ })
/************************************************************************/
/******/ ({

/***/ 41:
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(42);


/***/ }),

/***/ 42:
/***/ (function(module, exports) {

$(document).ready(function () {

    // Short cuts
    $(document).bind('keypress', function (e) {

        // Press ctrl + shift + x for new
        if (e.which === 24 && e.shiftKey && e.ctrlKey) {

            $('.more-button').click();
        }

        // Press ctrl + shift + a to add a new story-point to story-point
        if (e.which === 1 && e.shiftKey && e.ctrlKey) {

            $('div.story-point-shadow-container.active').find('a.add-story-point-to-this').click();
        }

        // Press ctrl + shift + u to add a new story-point to story-point
        if (e.which === 21 && e.shiftKey && e.ctrlKey) {

            $('div.story-point-shadow-container.active').find('a.update-story-point').click();
        }

        // Press shift + f to search
        if (e.which === 6 && e.shiftKey && e.ctrlKey) {

            var selectStoryPointWindow = $('#select-story-point');
            if (selectStoryPointWindow.length) {
                selectStoryPointWindow.modal();
                setTimeout(function () {
                    selectStoryPointWindow.find('input.form-control').attr('autofocus', true).focus();
                }, 500);
            }
        }

        // What do we do with the enter key?
        if (e.which == 13) {

            // Check if we have focus in the choose story-point-no
            if ($('input[name="select-story-point"]').is(':focus')) {

                $('#select-story-point button.btn-primary').click();
            }
        }
    });

    // Select story point number
    function selectStoryPointNumber(storyPointNo) {

        if (parseInt(storyPointNo) > 0) {

            var clickInput = $("div.panel-body").find("[data-story-point-no='" + storyPointNo + "']").find('div.story-point-container-top');

            if (clickInput.length > 0) {

                clickInput.click();
            }
        }
    }
    //story-point-leads-to-reference
    $('div.panel-body').on('click', 'a.story-point-leads-to-reference', function () {
        selectStoryPointNumber(parseInt($(this).data('story-point-no')));
    });
    $('#select-story-point button.btn-primary').on('click', function () {

        var storyPointNo = $('input[name="select-story-point"]'),
            storyPointNoVal = storyPointNo.val();

        selectStoryPointNumber(storyPointNoVal);

        storyPointNo.val('');

        // Close window
        $('#select-story-point').modal('hide');
    });

    $('.more-button').on('click', function () {

        setNewStoryPointParentID("");
        openNewStoryPointModal(0);
    });

    function openNewStoryPointModal(parentID) {

        var storyPointWindow = $('#new-story-point-window');

        // Do we show the parent chooser field?
        fieldToFocus = storyPointWindow.find('input[name="story_point_type"]');
        if (parentID == 0) {

            // So, we have no parent ID - that means that we should actually show the parent story point chooser
            // But we might also be in here because this story arch has no start story point and badly wants you to create one
            if (parseInt($('#story_arch_start_story_point_id').val()) == 0) {

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

        if (storyPointWindow.length) {

            storyPointWindow.modal();

            setTimeout(function () {
                fieldToFocus.attr('autofocus', true).focus();
            }, 500);
        }
    }

    // Catch datalist change event
    $('#new-story-point-window').find('input[name="story_point_type"]').bind('change', function () {

        var storyPointWindow = $('#new-story-point-window');

        // Find selected value
        var that = $(this);
        storyPointWindow.find('option').each(function () {

            if ($(this).val() == that.val()) {

                // Set the hidden input field to the value in the data field
                $('input[name="chosen_story_point_type"]').val($(this).data('value')).trigger('change');

                return false; // Stop loop
            }
        });

        return;
    });

    // Register when a new story point type is chosen
    $('input[name="chosen_story_point_type"]').on('change', function () {

        insertStoryPointAndRenderContainer($(this).val());
    });

    /**
     * Insert a story point. Furthermore, create the container for the story point.
     * 
     * @param {string} type 
     */
    function insertStoryPointAndRenderContainer(type) {
        // Get storyID and StoryArchID
        var storyID = $('#story_id').val(),
            storyArchID = $('#story_arch_id').val(),
            parentStoryPointNumber = parseInt($('#new-story-point-window').find('input[name="story_point_parent"]').val()),
            parentStoryPointID = parseInt($('#new-story-point-window').find('input[name="parent_id"]').val());

        $.post('/insert-story-point', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_id: storyID,
                story_arch_id: storyArchID,
                type: type,
                parent_number: parentStoryPointNumber,
                parent_id: parentStoryPointID
            }
        }, function (storyPointIDs) {
            var storyPoints = JSON.parse(storyPointIDs);

            if (storyPoints.error !== undefined) {

                // An error occured - what was it?
                adviceError(storyPoints.error);

                resetSearchFields();

                return;
            }

            var storyPointID = storyPoints.story_point_id,
                parentStoryPointID = storyPoints.parent_story_point_id;

            // Let's render the newly created story point
            renderStoryPoint(storyPointID);

            // Let's update the parent story point's leads-to graphics so it's visible that something has changed
            if (parentStoryPointID > 0) {

                // Decide whether or not we need to update the storypoint form.

                // Find the story point container that we've added a "leads-to" to.
                var storyPointContainer = $('div.panel-body').find('div[data-story-point-id="' + parentStoryPointID + '"]'),
                    storyPointType = storyPointContainer.data('story-point-type');

                // Now some story-point-types may need to have their specialized input refreshed on new "leads-to". Find out if this is one of them
                switch (storyPointType) {
                    case "condition":

                        // Find the appropriate container
                        var storyPointSpecializedInputContainer = storyPointContainer.find('div.story-point-form-specialized-input'),
                            generatedID = storyPointContainer.find('form').data('generated-id');

                        // This one needs to - let's make sure that happens.
                        updateStoryPointSpecializedInput(parentStoryPointID, storyPointSpecializedInputContainer, generatedID);

                        break;
                }

                updateStoryPointLeadsTo(parentStoryPointID);
            }

            // Close the modal
            $('#new-story-point-window').modal('hide');

            // Reset the search fields.
            resetSearchFields();
        });
    }

    // Update the form input that makes each 
    function updateStoryPointSpecializedInput(storyPointID, storyPointSpecializedInputContainer, generatedID) {

        var container = storyPointSpecializedInputContainer;

        $.post('/update-story-point-specialized-input', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_point_id: storyPointID,
                generated_id: generatedID
            }
        }, function (data) {

            // Now we've recieved a new rendition of the specialized form input
            var parsedData = JSON.parse(data);

            // Now update the specialized container with the new input
            container.html(parsedData);
        });
    }

    // Update story point leads to container
    function updateStoryPointLeadsTo(storyPointID) {
        var storyID = $('#story_id').val();

        $.post('/update-story-point-leads-to', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_id: storyID,
                story_point_id: storyPointID
            }
        }, function (data) {
            var parsedData = JSON.parse(data);
            // Find the right story point and append
            var storyPoint = $('div[data-story-point-id="' + storyPointID + '"]');

            updateContainer = storyPoint.find('div.story-pointleads-to-container');

            updateContainer.html("");

            updateContainer.append(parsedData);
        });
    }

    function renderStoryPoint(storyPointID) {

        var storyID = $('#story_id').val();

        $.post('/render-story-point-container', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_id: storyID,
                story_point_id: storyPointID
            }
        }, function (data) {
            var panelBody = $('div.panel-body'),
                parsedData = JSON.parse(data);

            if (parsedData == 'error') {
                // TODO: Error occured - take appropriate action
            } else {
                var storyPointID = parsedData.story_point_id,
                    html = parsedData.story_point_html;

                // TODO: Remove the old story_point_container

                panelBody.append(html);
            }
        });
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
    $('div.panel-body').on('click', 'div.story-point-container-top', function () {

        var storyID = $('#story_id').val(),
            storyPointContainer = $(this).closest('div.story-point-container'),
            storyPointShadowContainer = storyPointContainer.find('div.story-point-shadow-container'),
            storyPointID = storyPointContainer.data('story-point-id'),
            storyPointContentArea = storyPointContainer.find('div.story-point-container-middle div'),
            animationTime = 500;

        // Don't proceed if story-point is already active
        if (storyPointShadowContainer.hasClass('active')) {
            return;
        }

        // Make sure container is empty
        storyPointContentArea.html('');
        closeStoryPointForms();

        // Make sure the active class is added
        storyPointShadowContainer.addClass('active');

        $.post('/render-story-point-type-form', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_id: storyID,
                story_point_id: storyPointID
            }
        }, function (data) {
            var parsedData = JSON.parse(data);

            // Add the HTML
            storyPointContentArea.append(parsedData.html);

            // Show
            storyPointContentArea.show(animationTime);

            // Focus
            storyPointContentArea.find('input[name="name"]').focus();
        });
    });

    // close all story point forms, also inactivate everything else
    function closeStoryPointForms() {
        var animationTime = 500;

        $('div.panel-body').find('div.story-point-container').each(function () {
            var that = $(this),
                formContainer = $(this).find('div.story-point-form-container');
            formContainer.hide(animationTime);

            // Make sure active classes removed
            that.find('div.story-point-shadow-container').removeClass('active');
        });
    }

    // Update a story-point
    $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.update-story-point', function () {

        var storyID = $('#story_id').val(),
            that = $(this),
            form = that.closest('form'),
            storyPointID = that.closest('div.story-point-container').data('story-point-id');

        // Say that we're updating
        that.attr('disabled', true);
        that.html('wait...');
        $.post('/save-story-point-form', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_id: storyID,
                story_point_id: storyPointID,
                data: form.serialize()
            }
        }, function (data) {

            var parsedData = JSON.parse(data);

            that.closest('div.story-point-container').find('div.story-point-container-top span.story-point-container-top-name').html(parsedData);

            // Now are there any special functions here?
            var runJS = form.find('input.run-js-on-save');
            if (runJS !== undefined) {
                switch (runJS.data('js-function')) {
                    case 'addLeadsToToStoryPoint':
                        addLeadsToToStoryPoint(storyPointID, runJS.data('id'), runJS.data('type'));
                        break;
                }
            }

            that.attr('disabled', false);
            that.html('Update');
        });
    });

    // Add story to focused story-point
    $('div.panel-body').on('click', 'div.story-point-shadow-container.active a.add-story-point-to-this', function () {

        storyID = $('#story_id').val(), that = $(this), storyPointID = that.closest('div.story-point-container').data('story-point-id');

        openNewStoryPointModal(storyPointID);
    });

    function setNewStoryPointParentID(parentID) {
        $('#new-story-point-window').find('input[name="parent_id"]').val(parentID);
    }

    function adviceError(error) {
        switch (error) {
            case 'no_parent_id':
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

        var selectecVariable = $(this);
        var storyPointForm = selectecVariable.closest('form');
        var generatedID = storyPointForm.data('generated-id');

        storyPointForm.find('option').each(function () {

            if ($(this).val() == selectecVariable.val()) {

                // Find the correct chosen-variable input
                var formGroupContainer = selectecVariable.closest('div.form-group-container');

                // Set the hidden input field to the value in the data field
                formGroupContainer.find('.story-point-variable-choosen-variable').val($(this).data('id'));

                // Now create the field selectecVariable will contain the value
                $.post('/update-story-point-variable-input', {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    _method: 'POST',
                    data: {
                        story_id: $('#story_id').val(),
                        variable_type: $(this).data('type'),
                        generated_id: generatedID,
                        input_name: formGroupContainer.data('input-name'),
                        input_id: formGroupContainer.data('input-id')
                    }
                }, function (data) {
                    var html = JSON.parse(data);

                    // Find the correct span
                    var valueInputContainer = formGroupContainer.find('.story-point-variable-value-input');

                    // In the form - remove the current value-input and add the new one
                    valueInputContainer.html(html);
                });

                return false; // Stop loop
            }
        });

        return;
    });

    // Variable Condition - on change variable - set operator-options accordingly
    $('div.panel-body').on('change', '.story-point-variable-condition-choose-variable', function () {

        var selectecVariable = $(this);
        var storyPointForm = selectecVariable.closest('form');
        var generatedID = storyPointForm.data('generated-id');

        storyPointForm.find('option').each(function () {

            if ($(this).val() == selectecVariable.val()) {

                // Find the correct chosen-variable input
                var formGroupContainer = selectecVariable.closest('div.form-group-container');

                $.post('/update-story-point-variable-condition-choose-operator', {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    _method: 'POST',
                    data: {
                        type: $(this).data('type'),
                        number: formGroupContainer.data('number'),
                        generated_id: generatedID
                    }
                }, function (data) {
                    var html = JSON.parse(data);

                    // Find the correct span
                    var valueInputContainer = formGroupContainer.find('.story-point-variable-condition-operator-section');

                    // In the form - remove the current value-input and add the new one
                    valueInputContainer.html(html);
                });

                return false; // Stop loop
            }
        });
    });

    // Leads to chooser (Used in multiple story-point types)
    $('div.panel-body').on('change', '.story-point-choose-leads-to', function () {

        var selectecVariable = $(this);
        var storyPointForm = selectecVariable.closest('form');

        storyPointForm.find('option').each(function () {

            if ($(this).val() == selectecVariable.val()) {

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

        $.post('/update-story-point-redirect', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_point_id: storyPointID,
                type: $(this).val()
            }
        }, function (data) {

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
        });
    });

    // Redirect - choose destination
    $('div.panel-body').on('change', '.story-point-redirect-choose-destination', function () {

        var selectedDestination = $(this),
            thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            selectedIDInput = thisFormContainer.find('.story-point-redirect-selected-id'),
            runJSOnSave = thisFormContainer.find('.run-js-on-save');

        destinationDatalist.find('option').each(function () {

            if ($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                // Set some important values on the run JS on save input - to make sure we do the right thing after save
                runJSOnSave.data('id', $(this).data('id'));
                runJSOnSave.data('type', thisFormContainer.find('.story-point-redirect-select-type option:selected').val());

                return false; // Stop loop
            }
        });
    });

    // Redirect - update leads-to
    var addLeadsToToStoryPoint = function addLeadsToToStoryPoint(storyPointID, ref, type) {

        $.post('/handle-story-point-reference', {
            _token: $('meta[name=csrf-token]').attr('content'),
            _method: 'POST',
            data: {
                story_point_id: storyPointID,
                ref: ref,
                type: type,
                action: 'replace'
            }
        }, function (data) {

            // Update storypoint leads to
            updateStoryPointLeadsTo(storyPointID);
        });
    };

    // Text, incomming - choose sender
    $('div.panel-body').on('change', '.story-point-text-incomming-sender-name', function () {

        var selectedDestination = $(this),
            thisFormContainer = $(this).closest('.story-point-form-container'),
            destinationDatalist = thisFormContainer.find('datalist'),
            selectedIDInput = thisFormContainer.find('.story-point-text-incomming-sender-id');

        destinationDatalist.find('option').each(function () {

            if ($(this).val() == selectedDestination.val()) {

                // Set the appropriate value in the hidden ID field
                selectedIDInput.val($(this).data('id'));

                return false; // Stop loop
            }
        });
    });
});

/***/ })

/******/ });