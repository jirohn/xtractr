<?php

namespace Drupal\xtractr\Plugin\Block;

use Drupal\node\Entity\Node;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Proporciona un bloque 'Lista de Páginas de Extracción'.
 *
 * @Block(
 *   id = "xtractr_lista_paginas_extraccion",
 *   admin_label = @Translation("Lista de Páginas de Extracción"),
 *   category = @Translation("Custom")
 * )
 */
class XtractrBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_user = \Drupal::currentUser();
    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'pagina_de_extraccion')
        ->condition('uid', $current_user->id())
        ->accessCheck(TRUE);

    // Definir la paginación.
    $pager_limit = 10; // Número de elementos por página.
    $query->pager($pager_limit);

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
        $node_link = $node->toLink()->toString();
        $extract_url = Url::fromRoute('xtractr.extract_from_node', ['nid' => $node->id()]);
        $extract_link = Link::fromTextAndUrl($this->t('Extraer'), $extract_url)->toString();
        $items[] = [
            '#markup' => $node_link . ' ' . $extract_link,
        ];
    }

    // Construir la lista de elementos y añadir el pager.
    return [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('Tus Páginas de Extracción'),
        '#attached' => [
            'library' => [
                'core/drupal.pager',
            ],
        ],
        'pager' => [
            '#type' => 'pager',
        ],
        '#cache' => [
            'max-age' => 0, // Desactiva la caché para este bloque.
        ],
    ];
}



  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Control de acceso al bloque
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

}
