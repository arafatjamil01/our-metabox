var frame;

(function ($) {
  // our code here
  $(document).ready(function () {
    $("#upload_image").on('click', function (e) { 
     
      if( frame ){
        frame.open();
        return;
      }

      frame = wp.media({
        title: 'Select or Upload Media',
        button: {
          text: 'Insert Image'
        },
        multiple: true
      });

      frame.open();

      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        console.log(attachment.sizes.thumbnail.url);

        $("#omb_image_id").val(attachment.id);
        const thumb_url = attachment.sizes.thumbnail.url;
        $("#omb_image_url").val(thumb_url);
        $('#image_container').html(`<img src="${thumb_url}" style="max-width:100%">`);
      })


      return false; // To avoid the page being submitted. You can also do preventDefault at the top with the event e.
    });
  });
})(jQuery);
