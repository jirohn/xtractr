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
(function ($) {
  const apiKey = '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'; // Reemplaza con tu clave de API
  const $chatContainer = $('#openai-chat');

  $(document).ready(function () {
    // un div para guardar el globo de chat
    const $chat = $('<div id="chat-input"></div>');
    const $input = $('<input type="text" id="user-input input-ai" placeholder="¿Necesitas ayuda?" />');
    const $button = $('<button id="send-btn enviar-ai">Enviar</button>');
    //introducimos input y boton en el div $chat
    $chat.append($input).append($button);

    $chatContainer.append($chat);

    $button.click(sendMessage);
    $input.keypress(function (e) {
      if (e.which === 13) {
        sendMessage();
      }
    });

    function sendMessage() {
      const userMessage = $input.val();
      $input.val('');
      appendMessage(userMessage, 'user');

      // Enviar la pregunta a OpenAI
      sendToOpenAI(userMessage);
    }

    function sendToOpenAI(message) {
      $.ajax({
        method: 'POST',
        url: 'https://api.openai.com/v1/engines/davinci/completions',
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type': 'application/json',
        },
        data: JSON.stringify({
          prompt: message,
          max_tokens: 120,
          temperature: 0.9, // Ajusta la temperatura (entre 0 y 1)
          frequency_penalty: 0.1, // Ajusta la penalización de frecuencia
          presence_penalty: 0.1, // Ajusta la penalización de presencia
        }),
        success: function (response) {
          const answer = response.choices[0].text.trim();
          appendMessage(answer, 'bot');
        },
        error: function () {
          appendMessage('Lo siento, hubo un error al procesar tu solicitud.', 'bot');
        },
      });
    }

    function appendMessage(message, sender) {
      const $message = $('<div class="message">').text(message);
      if (sender === 'user') {
        $message.addClass('user-message');
      } else {
        $message.addClass('bot-message');
      }
      $chatContainer.append($message);
      $chatContainer.scrollTop($chatContainer[0].scrollHeight);
    }
  });
})(jQuery);

