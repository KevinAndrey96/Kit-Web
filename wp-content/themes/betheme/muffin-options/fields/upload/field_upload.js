(function($) {

  /* globals jQuery, wp */

  "use strict";

  function mfnFieldUpload() {

    jQuery('body').on('click', '.mfn-opts-upload', function(e) {

      e.preventDefault();

      var activeFileUploadContext = jQuery(this).parent();
      var type = jQuery('input', activeFileUploadContext).attr('class');

      // Create the media frame

      var customFileFrame = wp.media.frames.customHeader = wp.media({
        title: jQuery(this).data('choose'),
        library: {
          type: type
        },
        button: {
          text: jQuery(this).data('update')
        }
      });

      customFileFrame.on('select', function() {

        var attachment = customFileFrame.state().get("selection").first();

        // Update value of the targetfield input with the attachment url

        jQuery('.mfn-opts-screenshot', activeFileUploadContext).attr('src', attachment.attributes.url);
        jQuery('input', activeFileUploadContext)
          .val(attachment.attributes.url)
          .trigger('change');

        jQuery('.mfn-opts-upload', activeFileUploadContext).hide();
        jQuery('.mfn-opts-screenshot', activeFileUploadContext).show();
        jQuery('.mfn-opts-upload-remove', activeFileUploadContext).show();
      });

      customFileFrame.open();
    });

		jQuery('body').on('click', '.mfn-opts-upload-remove', function(e) {

      e.preventDefault();

      var activeFileUploadContext = jQuery(this).parent();

      jQuery('input', activeFileUploadContext).val('');
      jQuery(this).prev().fadeIn('slow');
      jQuery('.mfn-opts-screenshot', activeFileUploadContext).fadeOut('slow');
      jQuery(this).fadeOut('slow');
    });

  }

  /**
   * $(document).ready
   * Specify a function to execute when the DOM is fully loaded.
   */

  $(function() {
    mfnFieldUpload();
  });

})(jQuery);
