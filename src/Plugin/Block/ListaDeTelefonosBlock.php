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
        ->accessCheck(TRUE);
    // Definir un número de elementos por página.
    $pager_limit = 10;
    $query->pager($pager_limit);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
        if ($node->hasField('field_telefono') && !$node->get('field_telefono')->isEmpty()) {
            $telefono = $node->get('field_telefono')->value;
            $enviar_url = Url::fromRoute('xtractr.update_enviado', ['nid' => $node->id()]);
            // enviar_url lo convertimos en string
            $enviar_url = $enviar_url->toString();
            $enviar_link = '<a href="' . $enviar_url . '" target="_blank" onclick="window.location.reload(true);">' . $this->t('Enviar WhatsApp') . '</a>';
    
            $items[] = ['#markup' => $telefono . ' ' . $enviar_link];
        }
    }
    

     $build = [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('Tus telefonos extraidos'),
        'pager' => [
            '#type' => 'pager',
        ],
        '#cache' => [
            'max-age' => 0, // Desactiva la caché para este bloque.
        ],
    ];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
