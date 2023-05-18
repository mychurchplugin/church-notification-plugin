jQuery(document).ready(function($) {
  $('.color-picker').wpColorPicker();

  $('.notification-dismiss-link').on('click', function(e) {
      e.preventDefault();

      $(this).closest('.notification-banner').hide();

      // AJAX request to dismiss the notification banner
      $.ajax({
          url: ajax_object.ajax_url,
          type: 'POST',
          data: {
              action: 'dismiss_notification_banner'
          },
          success: function() {
              console.log('Notification banner dismissed');
          },
          error: function() {
              console.log('Error dismissing notification banner');
          }
      });
  });
});
