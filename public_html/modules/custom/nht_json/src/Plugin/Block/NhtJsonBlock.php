<?php

namespace Drupal\nht_json\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "nht_json_example",
 *   admin_label = @Translation("Example"),
 *   category = @Translation("nht-json")
 * )
 */
class NhtJsonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['content'] = [
      '#markup' => $this->t('It works!'),
    ];
    return $build;
  }

}
