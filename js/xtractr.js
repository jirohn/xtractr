(function ($, Drupal) {



  Drupal.behaviors.miScriptPersonalizado = {
        attach: function (context, settings) {
          //ejecutamos una vez
          //cuando carga la pagina el div con la clase xtractr-telefono-container entrara con un fadein desde transparente
          $('.xtractr-telefono-container').fadeIn(2000);
          //debug para ver si se ejecuta el script

          console.log('El script personalizado está funcionando');
          //el div con la clase xtractr-telefono-container se recargara al darle click a cualquier boton con la clase xtractr-enviar
          $('.xtractr-enviar').click(function(){
           
            //debug para ver si se ejecuta el click
            console.log('click');
            // esperamos un segundo y recargamos la pagina


              // esperamos un segundo y recargamos la pagina
              setTimeout(function(){ 
               // recargamos la pagina completa
                location.reload();

              }, 2000);
              

            });


      // Tu código JavaScript va aquí.
      // debug para ver si el script se está ejecutando
      

    }
  };
})(jQuery, Drupal);