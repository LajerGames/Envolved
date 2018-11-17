$(document).ready(function() {
    
    // Short cuts
    $(document).bind('keypress', function(e) {

        // Press ctrl + shift + s to save to SQLite
        if(e.which === 19 && e.shiftKey && e.ctrlKey) {
            
            openExportToSQLiteModal();

        }

    });

    $('#export-to-sqlite-link').on('click', function() {
        openExportToSQLiteModal();
    });

    function openExportToSQLiteModal() {

        var saveAsInput = $('#export-to-sqlite input#save_as'),
            storyID = saveAsInput.data('story-id');
        $.post(
            '/get-story-info',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    story_id: storyID
                }
            },
            function (data) {
                var decodedData = JSON.parse(data);

                // Create save as name
                saveAsInput.val(decodedData.title);
                $('#export-to-sqlite').modal();
            }
        );
    }

    $('#export-to-sqlite button.btn-primary').on('click', function() {
        $.post(
            '/stories/'+$(this).data('story-id')+'/export',
            { 
                _token: $('meta[name=csrf-token]').attr('content'),
                _method : 'POST',
                data : {
                    action: 'save',
                    name: $('#export-to-sqlite input#save_as').val()
                }
            }
            )

    });

    // Region: Builder

    // Are we looking at archs or are we looking at story points
    page = $('#story_arch_id').val() !== undefined ? 'points' : 'archs';
    
    // Run this once before resize
    checkContainerSize(page);
    $(window).resize(function() {
        
        checkContainerSize(page);
        
    });

    function checkContainerSize(page) {
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
        } else {
            removeMarginRightFromEveryNth = 0;
        }

        removeRightMargin(removeMarginRightFromEveryNth, page);
    }

    function removeRightMargin(everyNthChild) {

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

});