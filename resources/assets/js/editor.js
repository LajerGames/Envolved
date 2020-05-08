$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press ctrl + shift + s to save to SQLite
        if(e.which === 19 && e.shiftKey && e.ctrlKey) {

            openSaveModal('export');

        }

    });

    $('#export-to-sqlite-link').on('click', function() {
        openSaveModal('export');
    });

    $("#backup").on("click", function() {
        openSaveModal('backup');
    });

    function openSaveModal(type) {

        var saveAsInput = $('#save_modal input#save_as'),
            storyID = saveAsInput.data('story-id'),
            type = type === undefined ? 'backup' : type;
        $.post(
            '/prepare-story-modal',
            {
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: storyID,
                    type: type
                }
            },
            function (data) {

                var decodedData = JSON.parse(data);

                // Create save as name
                saveAsInput.val(decodedData.title);
                $('#save_modal').modal();
            }
        );

    }

    $('#save_modal button.btn-primary').on('click', function() {

        if($('#save_type').val() == 'export') {
            exportStory($(this));
        } else {
            backupStory($(this));
        }


    });

    function backupStory(button) {
        $.post(
            '/stories/'+$(this).data('story-id')+'/backup',
            {
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: button.data('story-id'),
                    name: $('#save_modal input#save_as').val()
                }
            }
        )
    }

    function exportStory() {
        $.post(
            '/stories/'+$(this).data('story-id')+'/export',
            {
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: button.data('story-id'),
                    name: $('#save_modal input#save_as').val()
                }
            }
        )
    }

    // Region: Builder

    // Are we looking at archs or are we looking at story points
    page = $('#story_arch_id').val() !== undefined ? 'points' : 'archs';
    
    // Run this once before resize
    checkContainerSize(page);
    $(window).resize(function() {
        
        checkContainerSize(page);
        
    });

});

// We need the next few functions in the global scope.. sorry!

checkContainerSize = function(page) {
    // Story arch container
    var container = page == 'points' ? $('div.panel-body') : $('#story-arch-container'),
        containerWidth = container.width();

    // change css to elements according to the VP size
    var removeMarginRightFromEveryNth = 0;
    if(containerWidth >= 640 && containerWidth < 980) {
        removeMarginRightFromEveryNth = 2;
    } else if(containerWidth >= 980 && containerWidth < 1320) {
        removeMarginRightFromEveryNth = 3;
    } else if(containerWidth >= 1320 && containerWidth < 1660) {
        removeMarginRightFromEveryNth = 4;
    } else if(containerWidth >= 1660 && containerWidth < 2000) {
        removeMarginRightFromEveryNth = 5;
    } else if(containerWidth >= 2000 && containerWidth < 2340) {
        removeMarginRightFromEveryNth = 6;
    } else if(containerWidth >= 2340 && containerWidth < 2680) {
        removeMarginRightFromEveryNth = 7;
    } else if(containerWidth >= 2680 && containerWidth < 3020) {
        removeMarginRightFromEveryNth = 8;
    } else if(containerWidth >= 3020 && containerWidth < 3360) {
        removeMarginRightFromEveryNth = 9;
    } else if(containerWidth >= 3360 && containerWidth < 3700) {
        removeMarginRightFromEveryNth = 10;
    } else {
        removeMarginRightFromEveryNth = 0;
    }

    removeRightMargin(removeMarginRightFromEveryNth, page);
}

removeRightMargin = function(everyNthChild, page) {

    var margin = 40;

    if(everyNthChild < 2) {

        // Are we at story archs or story points
        if(page == 'points') {

            $('div.panel-body > .story-point-container').css('margin-right', margin+'px');
            $('div.panel-body > .story-point-container').show(100);

        } else {

            $('#story-arch-container > .arch-container').css('margin-right', margin+'px');
            $('#story-arch-container > .arch-container').show(100);

        }

        return;
    }

    // First set the setting on all of the elements
    if(page == 'points') {

        $('div.panel-body > .story-point-container').css('margin-right', margin+'px');
        $('div.panel-body > .story-point-container:nth-child('+everyNthChild+'n)').css('margin-right', '0px');
        $('div.panel-body > .story-point-container').show(100);

    } else {

        $('#story-arch-container > .arch-container').css('margin-right', margin+'px');
        $('#story-arch-container > .arch-container:nth-child('+everyNthChild+'n)').css('margin-right', '0px');
        $('#story-arch-container > .arch-container').show(100);

    }

}