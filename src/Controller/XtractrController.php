<?php

namespace Drupal\xtractr\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Controller for Xtractr module.
 */
class XtractrController extends ControllerBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * XtractrController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Controller action for the extractor page.
   */
  public function index() {
    // Get the current user.
    $current_user = $this->currentUser();

    // Get the "Pagina de extraccion" content entity created by the user.
    $pagina_de_extraccion = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties(['type' => 'pagina_de_extraccion', 'uid' => $current_user->id()])
      ? reset($this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties(['type' => 'pagina_de_extraccion', 'uid' => $current_user->id()]))
      : NULL;

    // Check if the "Pagina de extraccion" entity exists and is valid.
    if ($pagina_de_extraccion && $pagina_de_extraccion->access('view')) {
      // Get the URL from the field_urlextract field.
      $url = $pagina_de_extraccion->get('field_urlextract')->value;

      // Fetch the contents from the user's page URL.
      $contents = file_get_contents($url);

      // Extract phone numbers from the contents.
      preg_match_all("/[6-7]{1}[0-9]{8}/", $contents, $matches);
      $phone_numbers = $matches[0];

      // Get the "telefono" entity storage.
      $telefono_storage = $this->entityTypeManager->getStorage('node');

      // Copy phone numbers to "telefono" entities.
      $copied = 0;
      foreach ($phone_numbers as $phone_number) {
        // Check if the phone number already exists.
        $existing_telefono = $telefono_storage->loadByProperties(['type' => 'telefono', 'field_telefono' => $phone_number]);
        if (!$existing_telefono) {
          // Create a new "telefono" entity.
          $telefono = $telefono_storage->create([
            'type' => 'telefono',
            'title' => $phone_number, // Provide a title for the entity.
            'field_telefono' => $phone_number,
            'field_enviado' => FALSE, // Set the "enviado" field to FALSE by default.

          ]);
          $telefono->save();
          $copied++;
        }
      }

      // Display a message.
      $message = $copied > 0 ? "Se han copiado $copied teléfonos nuevos" : "No se han copiado teléfonos";

      // Render the page.
      return [
        '#markup' => $this->t('Hello, Xtractr!'),
        '#page' => $contents,
        '#dump' => $phone_numbers,
        '#message' => $message,
        '#page_url' => $url,
        '#msg' => 'No Hay texto', // Replace with actual message.
      ];
    }
    else {
      // Handle the case where the "Pagina de extraccion" entity is not accessible.
      return new JsonResponse(['error' => 'La página de extracción no es válida o no es accesible']);
    }
  }
/**
 * Controller action for extracting phones from a specific node.
 */
public function extractFromNode($nid) {
  // Cargar el nodo usando el ID proporcionado.
  $node = $this->entityTypeManager->getStorage('node')->load($nid);

  // Verificar que el nodo existe y es del tipo correcto.
  if ($node && $node->bundle() == 'pagina_de_extraccion' && $node->access('view')) {
    // Obtener la URL del campo 'field_urlextract'.
    $url = $node->get('field_urlextract')->value;

    // Realizar la operación de extracción.
    if ($url) {
      // Obtener el contenido de la URL.
      $contents = file_get_contents($url);

      // Extraer números de teléfono.
      preg_match_all("/[6-7]{1}[0-9]{8}/", $contents, $matches);
      $phone_numbers = $matches[0];

      // Get the "telefono" entity storage.
      $telefono_storage = $this->entityTypeManager->getStorage('node');

      // Copy phone numbers to "telefono" entities.
      $copied = 0;
      foreach ($phone_numbers as $phone_number) {
        // Check if the phone number already exists.
        $existing_telefono = $telefono_storage->loadByProperties(['type' => 'telefono', 'field_telefono' => $phone_number]);
        if (!$existing_telefono) {
          // Create a new "telefono" entity.
          $telefono = $telefono_storage->create([
            'type' => 'telefono',
            'title' => $phone_number, // Provide a title for the entity.
            'field_telefono' => $phone_number,
            'field_enviado' => FALSE, // Set the "enviado" field to FALSE by default.
          ]);
          $telefono->save();
          $copied++;
        }
      }

      // Return a response, e.g., a message indicating how many phone numbers were extracted.
      return new JsonResponse(['message' => "Se han copiado $copied teléfonos nuevos"]);
    }
    else {
      // Handle the case where no URL is found.
      return new JsonResponse(['error' => 'No se encontró URL en la página de extracción']);
    }
  }
  else {
    // Handle the case where the node is not valid or accessible.
    return new JsonResponse(['error' => 'El nodo no es válido o no es accesible']);
  }
}
public function updateEnviado($nid) {
  $node = \Drupal\node\Entity\Node::load($nid);

  if ($node && $node->bundle() == 'telefono' && $node->hasField('field_enviado')) {
      // Actualizar el nodo.
      $node->set('field_enviado', TRUE);
      $node->save();

      // Construir la URL de WhatsApp.
      $telefono = $node->get('field_telefono')->value;

      // Obtener mensajes del usuario actual.
      $current_user = \Drupal::currentUser();
      $query = \Drupal::entityQuery('node')
          ->condition('type', 'mensajes')
          ->condition('uid', $current_user->id())
          ->accessCheck(FALSE);

      $mensajes_nids = $query->execute();
      $mensajes_nodes = \Drupal\node\Entity\Node::loadMultiple($mensajes_nids);

      $mensajes = [];
      foreach ($mensajes_nodes as $mensaje_node) {
          if ($mensaje_node->hasField('field_mensaje') && !$mensaje_node->get('field_mensaje')->isEmpty()) {
              $texto_mensaje = $mensaje_node->get('field_mensaje')->value;
              // Limpiar el texto del mensaje para eliminar etiquetas HTML.
              $texto_limpio = strip_tags($texto_mensaje);
              $mensajes[] = $texto_limpio;
          }
      }

      // Elegir un mensaje al azar si hay alguno disponible.
      $mensaje_seleccionado = !empty($mensajes) ? $mensajes[array_rand($mensajes)] : 'Mensaje predeterminado';

      $whatsapp_url = "whatsapp://send?phone=34{$telefono}&text=" . urlencode($mensaje_seleccionado);
      // Redirigir a la URL de WhatsApp.
      return new TrustedRedirectResponse($whatsapp_url);
  } else {
      // Manejar el caso de error.
      return new JsonResponse(['error' => 'Nodo no válido o no encontrado']);
  }
}


  /**
   * Controller action for changing the data source.
   */
  public function changeSource(Request $request) {
    if ($request->isXmlHttpRequest()) {
      $page = $request->request->get('page');
      $current_user = $this->currentUser();

      // Save the user's page URL to the field_urlextract field.
      $pagina_de_extraccion = $this->entityTypeManager
        ->getStorage('node')
        ->loadByProperties(['type' => 'pagina_de_extraccion', 'uid' => $current_user->id()])
        ? reset($this->entityTypeManager
          ->getStorage('node')
          ->loadByProperties(['type' => 'pagina_de_extraccion', 'uid' => $current_user->id()]))
        : NULL;

      if ($pagina_de_extraccion) {
        $pagina_de_extraccion->set('field_urlextract', $page);
        $pagina_de_extraccion->save();
        return new JsonResponse(['enviado' => TRUE]);
      }
      else {
        return new JsonResponse(['error' => 'No se encontró la página de extracción del usuario']);
      }
    }
    else {
      throw new \Exception('me quieres hackear?');
    }
  }

}
