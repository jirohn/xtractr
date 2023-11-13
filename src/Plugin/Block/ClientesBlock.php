<?php

namespace Drupal\xtractr\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\node\Entity\Node;

/**
 * Proporciona un bloque 'Lista de Clientes'.
 *
 * @Block(
 *   id = "lista_de_clientes",
 *   admin_label = @Translation("Lista de Clientes"),
 *   category = @Translation("Custom")
 * )
 */
class ClientesBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'clientes')
        ->accessCheck(TRUE)
        ->sort('created', 'DESC'); // Ordenar por fecha de creación, si es necesario.

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
        $nombre = $node->get('field_nombre')->value;
        // el nombre debe tener un enlace que dirija al nodo
        $nombre = $node->toLink()->toString();
        // Añadir otros campos del tipo de contenido clientes
        $tipo_tarifa = $node->get('field_tipo_tarifa')->value;
        $fecha_contratacion = $node->get('field_fecha_contratacion')->value;
        $observaciones = $node->get('field_observaciones')->value;
        $items[] = [
            
            '#markup' => $nombre . ' ' . $tipo_tarifa . ' ' . $fecha_contratacion . ' ' . $observaciones,
        ];
    }

    return [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('Lista de Clientes'),
        // sin cache
        '#cache' => [
            'max-age' => 0,
        ],
    ];
  }

}
