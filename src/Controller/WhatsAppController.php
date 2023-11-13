<?php

namespace Drupal\xtractr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controlador para manejar la lógica de actualización de nodos.
 */
class WhatsAppController extends ControllerBase {

  /**
   * Actualiza el campo 'field_enviado' de un nodo.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   La solicitud HTTP.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   La respuesta JSON.
   */
  public function updateNode(Request $request) {
    // Verificar que la solicitud es una solicitud Ajax.
    if ($request->isXmlHttpRequest()) {
      $nid = $request->request->get('nid');

      // Cargar y actualizar el nodo.
      if ($nid && $node = Node::load($nid)) {
        // Verificar que el nodo es del tipo correcto y que el usuario tiene permiso para editarlo.
        if ($node->bundle() == 'telefono' && $node->access('update')) {
          $node->set('field_enviado', TRUE);
          $node->save();

          return new JsonResponse(['status' => 'success', 'message' => 'Nodo actualizado.']);
        }
      }

      return new JsonResponse(['status' => 'error', 'message' => 'Nodo no encontrado o acceso denegado.']);
    }

    throw new AccessDeniedHttpException();
  }

}
