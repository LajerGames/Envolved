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

});