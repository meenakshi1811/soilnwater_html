(function ($) {
  'use strict';

  $(function () {
    $(".profile-action a").on("click", function (e) {
      $(this).parent().toggleClass("active");

      $("#mSearch").removeClass("active");
      $("#h-search").removeClass("show");
      $(".h-search").removeClass("show");

      $("body").removeClass("sidebar-active");
      $(".site-menu").removeClass("active");
      e.stopPropagation()
    });
    $("#mSearch").on("click", function (e) {
      $(this).toggleClass("active");
      $(".h-search").toggleClass("show");

      $(".profile-action").removeClass("active");

      $("body").removeClass("sidebar-active");
      $(".site-menu").removeClass("active");
      e.stopPropagation()
    });
    $(document).on("click", function (e) {
      if ($(e.target).is(".h-search, .h-search i, .h-search input") === false) {
        $(".profile-action").removeClass("active");
        $("#mSearch").removeClass("active");
        $(".h-search").removeClass("show");
      }
    });
  });

  $(document).ready(function () {
    $('.site-menu').click(function () {
      $("body").toggleClass("sidebar-active");
      $(this).toggleClass("active");

      $(".profile-action").removeClass("active");
      $("#mSearch").removeClass("active");
      $(".h-search").removeClass("show");
    });
    $('.layer').click(function () {
      $("body").removeClass("sidebar-active");
      $(".site-menu").removeClass("active");
    });
  });

  // $(".u-input input[type=file]").change(function () {
  //   var names = [];
  //   for (var i = 0; i < $(this).get(0).files.length; ++i) {
  //     names.push($(this).get(0).files[i].name);
  //   }
  //   if ($(".u-input input[type=file]").val()) {
  //     $(".u-input label").html(names);
  //   } else {
  //     $(".u-input label").html("Browse file...");
  //   }
  // });







  $("#show_hide_password a").on('click', function (event) {
    event.preventDefault();
    if ($('#show_hide_password input').attr("type") == "text") {
      $('#show_hide_password input').attr('type', 'password');
      $('#show_hide_password i').addClass("fa-eye-slash");
      $('#show_hide_password i').removeClass("fa-eye");
    } else if ($('#show_hide_password input').attr("type") == "password") {
      $('#show_hide_password input').attr('type', 'text');
      $('#show_hide_password i').removeClass("fa-eye-slash");
      $('#show_hide_password i').addClass("fa-eye");
    }
  });








  //Avoid pinch zoom on iOS
  document.addEventListener('touchmove', function (event) {
    if (event.scale !== 1) {
      event.preventDefault();
    }
  }, false);
})(jQuery)