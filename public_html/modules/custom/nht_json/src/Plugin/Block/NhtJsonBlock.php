<?php

namespace Drupal\nht_json\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "nht_json_example",
 *   admin_label = @Translation("NHT JS App"),
 *   category = @Translation("nht-json")
 * )
 */
class NhtJsonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#attached' => [
        'library' => [
          'nht_json/nht_json'
        ]
      ],
      '#markup' => '<div id="appWrapper"></div>

    <script type="module" src="/dist/index.js"></script>',
    ];
    return $build;
  }

}
