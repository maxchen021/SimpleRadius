function DisplayWaitMessage()
{
  var docHeight = $(document).height();
  var docWidth = $(document).width();
            $(".overlay").css({
            "height":docHeight,
            "width":docWidth
             });

  var popup = $(".popup");

  var center = function () {
            var T = $(window).height() / 2 - popup.height() / 2 + $(window).scrollTop();
               
            popup.css({
                top: T     
            });
      };



 $(window).scroll(center);
  $(window).resize(center);

  center();

  $(".overlay, .popup").fadeToggle();
}