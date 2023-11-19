<?php

namespace Drupal\xtractr\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;

/**
 * Proporciona un bloque 'Lista de Teléfonos'.
 *
 * @Block(
 *   id = "lista_de_telefonos",
 *   admin_label = @Translation("Lista de Teléfonos"),
 *   category = @Translation("Custom")
 * )
 */
class ListaDeTelefonosBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser();

    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'telefono')
        ->condition('uid', $current_user->id())
        ->condition('field_enviado', FALSE)
        ->accessCheck(FALSE);
    // Definir un número de elementos por página.
    $pager_limit = 10;
    $query->pager($pager_limit);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
      if ($node->hasField('field_telefono') && !$node->get('field_telefono')->isEmpty()) {
        $telefono = $node->get('field_telefono')->value;
        $enviar_url = Url::fromRoute('xtractr.update_enviado', ['nid' => $node->id()])->toString();
        $delete_url = Url::fromRoute('entity.node.delete_form', ['node' => $node->id()])->toString();
        $enviar_link = '<a href="' . $enviar_url . '" class="xtractr-enviar">' . $this->t('Enviar WhatsApp') . '</a>';
        $delete_link = '<a href="' . $delete_url . '" class="xtractr-eliminar">' . 'X' . '</a>';

        $items[] = ['#markup' => '<div class="xtractr-telefono">' . $delete_link .
        ' Num: ' . $telefono . 
         ' ' . $enviar_link . '</div>'];
    }
    }
    

    $build = [
      '#prefix' => '<div class="xtractr-telefono-container">', // Envolver todos los elementos en un div
      '#theme' => 'item_list', // Asegúrate de que este tema coincida con el definido en hook_theme
      '#items' => $items,
      '#suffix' => '</div>',
      '#cache' => [
          'max-age' => 0,
      ],
  ];
  // enviamos el item 1 del build al log
  $build['#attached']['library'][] = 'xtractr/estilos-bloque';

  return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
