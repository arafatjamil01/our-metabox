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
        multiple: false
      });

      frame.open();



      return false; // To avoid the page being submitted. You can also do preventDefault at the top with the event e.
    });
  });
})(jQuery);
