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
        ->condition('uid', \Drupal::currentUser()->id())
        ->accessCheck(TRUE)
        ->sort('created', 'DESC'); // Ordenar por fecha de creación, si es necesario.

    $nids = $query->execute();
    $nodes = Node::loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
        $nombre = $node->toLink()->toString();
        $tipo_tarifa = $node->get('field_tipo_tarifa')->value;
        $fecha_contratacion = $node->get('field_fecha_contratacion')->value;
        $observaciones = $node->get('field_observaciones')->value;

        $edit_url = Url::fromRoute('entity.node.edit_form', ['node' => $node->id()]);
        $delete_url = Url::fromRoute('entity.node.delete_form', ['node' => $node->id()]);

        $edit_button = '<a href="' . $edit_url->toString() . '" class="xtractr-edit-button">' . t('Edit') . '</a>';
        $delete_button = '<a href="' . $delete_url->toString() . '" class="xtractr-delete-button">' . 'X' . '</a>';
        // sacamos solo la fecha sin la hora del campo fecha_contratacion
        $fecha_contratacion = substr($fecha_contratacion, 0, 10);
        // resumimos el resultado de observaciones en 20 caracteres y lo finalizamos con ...
        $observaciones = substr($observaciones, 0, 20) . '...';
        $items[] = [
            '#markup' => '<div class="xtractr-cliente-item"><div class="nombre-cliente"><h3>' . $nombre . 
            '</h3></div><div class="tarifa"><h3>Tipo de tarifa:</h3> ' . $tipo_tarifa . 
            '</div><div class="fecha-contratacion"><h3>Fecha de contratacion:</h3> ' . $fecha_contratacion . 
            '</div><div class="observaciones"><h3>Observaciones:</h3>' . $observaciones . 
            '</div><div class="buttons">' . $edit_button . ' ' . $delete_button . '</div></div>',
        ];
    }

    return [
        '#theme' => 'item_list',
        '#items' => $items,
        '#title' => $this->t('Lista de Clientes'),
        '#cache' => [
            'max-age' => 0, // Sin cache
        ],

    ];
  }

}
