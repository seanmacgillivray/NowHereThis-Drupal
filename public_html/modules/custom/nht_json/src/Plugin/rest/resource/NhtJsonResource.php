<?php

namespace Drupal\nht_json\Plugin\rest\resource;

use Drupal\Component\Plugin\DependentPluginInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\BcRoute;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\Entity\File;

/**
 * Represents Example records as resources.
 *
 * @RestResource (
 *   id = "nht_json_example",
 *   label = @Translation("NHT JSON"),
 *   uri_paths = {
 *     "canonical" = "/api/nht-json.json"
 *   }
 * )
 *
 * @DCG
 * This plugin exposes database records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. You may
 * find an example of such configuration in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively you can make use of REST UI module.
 * @see https://www.drupal.org/project/restui
 * For accessing Drupal entities through REST interface use
 * \Drupal\rest\Plugin\rest\resource\EntityResource plugin.
 */
class NhtJsonResource extends ResourceBase implements DependentPluginInterface {

  /**
     * The config factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
  protected $configFactory;

  /**
     * The entity type manager.
     *
     * @var \Drupal\Core\Entity\EntityTypeManagerInterface
     */
  protected $entityTypeManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnection;

  /**
   * Constructs a Drupal\rest\Plugin\rest\resource\EntityResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Database\Connection $db_connection
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, Connection $db_connection, EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->dbConnection = $db_connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * Responds to GET requests.
   *
   * @param int $id
   *   The ID of the record.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response containing the record.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function get() {
    $result = $this->buildJSON();
    return new ResourceResponse(json_encode($result));
  }

  private function buildJSON() {
    $subdomain = $this->configFactory->get('nht_json.settings')->get('customer_subdomain');
    $result = [];
    $getID3 = new \getID3();
    $composers = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('composer');
    $clip_types = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('clip_type');
    foreach ($composers as $composer) {
      $result[$composer->tid]['name'] = $composer->name;
      foreach ($clip_types as $clip_type) {
        $clips = $this->getClipsByComposerAndClipType($composer->tid, $clip_type->tid);
        if ($clips) {
            foreach ($clips as $clip) {
              $clip_object = $this->entityTypeManager->getStorage('media')->load($clip);
              $video_media = $clip_object->get('field_media_hosted_video')->first();
              $video_id = $video_media->getValue()['cloudflareStreamVideoID'];
              $fid = $video_media->getValue()['target_id'];
              $file = File::load($fid)->getFileUri();
              $file_info = $getID3->analyze($file);
              $result[$composer->tid][$clip_type->name][$clip]['title'] = $clip_object->getName();
              $result[$composer->tid][$clip_type->name][$clip]['url'] = 'https://' . $subdomain . '/' . $video_id . '/manifest/video.mpd';
              $result[$composer->tid][$clip_type->name][$clip]['runtime_string'] = $file_info['playtime_string'] ?: 0;
              $result[$composer->tid][$clip_type->name][$clip]['runtime_float'] = $file_info['playtime_seconds'] ?: 0;
            }
          }
      }
    }
    return $result;
  }

  private function getClipsByComposerAndClipType($composer, $clip_type) {
    $result = [];
    $query = $this->entityTypeManager->getStorage('media')->getQuery();
    $query->condition('bundle', 'hosted_video');
    $query->condition('field_clip_type', $clip_type);
    $query->condition('field_composer', $composer);
    $result = $query->execute();
    return $result;
  }

  public function calculateDependencies() {
    return [];
  }


}