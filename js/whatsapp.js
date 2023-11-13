(function ($, Drupal) {
    Drupal.behaviors.whatsappButtonBehavior = {
      attach: function (context, settings) {
        $('#whatsapp-block .use-ajax', context).click(function () {
          // Aquí puedes agregar lógica adicional que se ejecutará cuando se haga clic en el botón.
          // Por ejemplo, puedes mostrar un mensaje de carga o deshabilitar temporalmente el botón.
  
          console.log('Botón AJAX presionado.');
        });
      }
    };
  })(jQuery, Drupal);
  