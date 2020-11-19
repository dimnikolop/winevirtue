(function($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle").on('click', function(e) {
    e.preventDefault();
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(event) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    event.preventDefault();
  });

  // Get user id to delete
  $(document).on('click', '.delete_user', function() {
    var id = $(this).data('id');
    var url = `users/delete/${id}`;
    $("#del").attr("href", url);
  });

  // Pass post id to modal delete link for delete selected post */
  $(document).on("click", ".delete_post", function() {
    var id = $(this).data('id');
    var url = `post/delete/${id}`;
    $("#del").attr("href", url);
  });

  /* Pass wine id to modal delete link for delete selected wine */
  $(document).on("click", ".delete_wine", function() {
    var id = $(this).data('id');
    var url = `wine/delete/${id}`;
    $("#del").attr("href", url);
  });

  // Show on file input the name of the selected file
  $(".custom-file-input").on("change", function() {
    var fileName = $(this).val().split("\\").pop();
    $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
  });

})(jQuery); // End of use strict
