// The "Upload" button
$('.upload_image_button').click(function() {
    var send_attachment_bkp = wp.media.editor.send.attachment;
    var button = $(this);
    wp.media.editor.send.attachment = function(props, attachment) {
        $('#vos-simple-popup-background-image__image').attr('src', attachment.url);
        $('#vos-simple-popup-background-image').val(attachment.url);
        wp.media.editor.send.attachment = send_attachment_bkp;
    }
    wp.media.editor.open(button);
    return false;
});

// The "Remove" button (remove the value from input type='hidden')
$('.remove_image_button').click(function() {
    var answer = confirm('Are you sure?');
    if (answer == true) {
        $('#vos-simple-popup-background-image__image').attr('src', '');
        $('#vos-simple-popup-background-image').val('');
    }
    return false;
});