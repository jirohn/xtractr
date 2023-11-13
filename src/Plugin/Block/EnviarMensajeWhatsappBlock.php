<?php

namespace Drupal\xtractr\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Proporciona un bloque 'Enviar Mensaje WhatsApp'.
 *
 * @Block(
 *   id = "enviar_mensaje_whatsapp",
 *   admin_label = @Translation("Enviar Mensaje WhatsApp"),
 *   category = @Translation("Custom")
 * )
 */
class EnviarMensajeWhatsAppBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'telefono')
        ->condition('field_enviado', FALSE)
        ->accessCheck(TRUE)
        ->range(0, 1);

    $nids = $query->execute();

    if ($nids) {
        $nid = reset($nids);
        $node = Node::load($nid);

        if ($node) {
            $telefono = $node->get('field_telefono')->value;
            $whatsapp_url = "https://wa.me/34{$telefono}?text=test";
            $whatsapp_link = Link::fromTextAndUrl($this->t('Enviar WhatsApp'), Url::fromUri($whatsapp_url, ['attributes' => ['target' => '_blank']]))->toString();

            $ajax_button = [
                '#type' => 'button',
                '#value' => $this->t('Marcar como Enviado'),
                '#ajax' => [
                    'callback' => '::markAsSentAjax',
                    'event' => 'click',
                    'wrapper' => 'whatsapp-block',
                ],
                '#attributes' => [
                    'class' => ['use-ajax'],
                ],
            ];

            return [
                '#markup' => $telefono . ' ' . $whatsapp_link,
                'ajax_button' => $ajax_button,
                '#attached' => [
                    'library' => [
                        'core/drupal.ajax',
                        'xtractr/whatsapp-ajax', // Asegúrate de que el nombre de la biblioteca coincida con lo que definiste en xtractr.libraries.yml.
                    ],
                ],
                '#prefix' => '<div id="whatsapp-block">',
                '#suffix' => '</div>',
            ];
        }
    }

    return ['#markup' => $this->t('No hay más números por enviar.')];
}


  /**
   * AJAX callback para marcar el nodo como enviado.
   */
  public function markAsSentAjax(array &$form, \Drupal\Core\Form\FormStateInterface $form_state) {
    // Lógica para actualizar el nodo y marcarlo como enviado.
    // ...

    // Crear una respuesta AJAX.
    $response = new AjaxResponse();

    // Agregar comandos a la respuesta para actualizar la página o mostrar un mensaje.
    // Por ejemplo, reemplazar el contenido del bloque.
    $response->addCommand(new ReplaceCommand('#whatsapp-block', '<div id="whatsapp-block">Número marcado como enviado.</div>'));

    return $response;
  }

}
