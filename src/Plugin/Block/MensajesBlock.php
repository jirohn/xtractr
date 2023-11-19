<?php

namespace Drupal\xtractr\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * Proporciona un bloque 'Lista de Mensajes'.
 *
 * @Block(
 *   id = "lista_de_mensajes",
 *   admin_label = @Translation("Lista de Mensajes"),
 *   category = @Translation("Custom")
 * )
 */
class MensajesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser();
    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'mensajes')
        ->condition('uid', $current_user->id())
        ->accessCheck(FALSE);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
        $edit_url = Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]);
        $delete_url = Url::fromRoute('entity.node.delete_form', ['node' => $node->id()]);

        $edit_link = '<a href="' . $edit_url->toString() . '" class="xtractr-editar-mensaje">' . t('Edit') . '</a>';
        $delete_link = '<a href="' . $delete_url->toString() . '" class="xtractr-eliminar-mensaje">' . t('Delete') . '</a>';

        $items[] = ['#markup' => '<div class="xtractr-mensaje-item">' . $node->label() . ' ' . $edit_link . ' ' . $delete_link . '</div>'];
    }

    return [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('Tus Mensajes'),
    ];
  }

}
