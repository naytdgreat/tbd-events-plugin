jQuery(document).ready(function($) {
    $('#add_ticket').on('click', function() {
        var ticketHtml = '<div class="ticket" style="margin-bottom: 5px;">' +
            '<input type="text" name="ticket_name[]" placeholder="Ticket Name" required>' +
            '<input type="number" name="ticket_price[]" placeholder="Price" required>' +
            '<button type="button" class="remove_ticket bbutton">Remove</button>' +
            '</div>';
        $('#tickets_list').append(ticketHtml);
        console.log("loaded tickets");
    });

    $(document).on('click', '.remove_ticket', function() {
        $(this).parent('.ticket').remove();
    });

    var mediaUploader;
    $('#event_photo_button').on('click', function(e) {
        e.preventDefault();
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        mediaUploader = wp.media({
            title: 'Select Event Photo',
            button: { text: 'Use this image' },
            multiple: false
        });
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#event_photo').val(attachment.id);
            $('#event_photo_preview').html('<img src="' + attachment.url + '" style="max-width:100%; height:auto;">');
        });
        mediaUploader.open();
        console.log("loaded photo");
    });
    
});

// jQuery(document).ready(function($) {
//     var mediaUploader;
//     $('#event_photo_button').on('click', function(e) {
//         e.preventDefault();
//         if (mediaUploader) {
//             mediaUploader.open();
//             return;
//         }
//         mediaUploader = wp.media({
//             title: 'Select Event Photo',
//             button: { text: 'Use this image' },
//             multiple: false
//         });
//         mediaUploader.on('select', function() {
//             var attachment = mediaUploader.state().get('selection').first().toJSON();
//             $('#event_photo').val(attachment.id);
//             $('#event_photo_preview').html('<img src="' + attachment.url + '" style="max-width:100%; height:auto;">');
//         });
//         mediaUploader.open();
//         console.log("loaded photo");
//     });
// });

// jQuery(document).ready(function($) {
//     var file_frame;

//     $('#event_photo_button').on('click', function(event) {
//         event.preventDefault();
//         console.log("Just clicked");

//         // If the media frame already exists, reopen it.
//         if (file_frame) {
//             file_frame.open();
//             return;
//         }

//         // Create the media frame.
//         file_frame = wp.media({
//             title: 'Select or Upload Event Photo',
//             button: {
//                 text: 'Use this photo'
//             },
//             multiple: false // Set to true to allow multiple files to be selected
//         });

//         // When an image is selected, run a callback.
//         file_frame.on('select', function() {
//             var attachment = file_frame.state().get('selection').first().toJSON();
//             $('#event_photo').val(attachment.url); // Set the selected image URL to the input
//         });

//         // Finally, open the modal on click.
//         file_frame.open();
//     });
// });
