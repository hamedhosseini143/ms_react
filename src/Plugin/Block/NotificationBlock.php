<?php

namespace Drupal\ms_react\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'NotificationBlock' block.
 *
 * @Block(
 *  id = "notification_block",
 *  admin_label = @Translation("ms react nptification block"),
 * )
 */
class NotificationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];
    $build['#theme'] = 'notification_block';
    $build['#attached']['library'][] = 'ms_react/ms_react_notification';
    $build['notification_block']['#markup'] = '<div id="ms_react_notification"></div>';
    return $build;
  }



}
