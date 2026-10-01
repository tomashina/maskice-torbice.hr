$(document).on( "click","#select-gls-ps > span", function(e) {
    var $url = 'index.php?route=extension/shipping/gls_ps',
    $btn = $(this).button('loading');
   	e.preventDefault();

   	$.get($url, function(data) {
      $btn.button('reset');

      $('#gls-parcelshop-widget .modal-content').html(data);
      $('#gls-parcelshop-widget').modal('show');
    });
 
});
