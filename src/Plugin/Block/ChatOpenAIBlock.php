<?php

namespace Drupal\xtractr\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to chat with OpenAI.
 *
 * @Block(
 *   id = "chat_openai_block",
 *   admin_label = @Translation("Chat with OpenAI"),
 * )
 */
class ChatOpenAIBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new ChatOpenAIBlock instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the module path.
    $module_path = $this->moduleHandler->getModule('xtractr')->getPath();

    // Attach the necessary library for interacting with the OpenAI API.
    $build['#attached']['library'][] = 'xtractr/estilos-bloque';

    // Return the HTML markup where the chat will be displayed.
    $build['content'] = [
      '#markup' => '<div id="openai-chat"></div>',
    ];

    return $build;
  }

}
