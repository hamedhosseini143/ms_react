<?php

namespace Drupal\ms_react\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class messagingController.
 */
class messagingController extends ControllerBase {

  public function access(AccountInterface $account) {
    $mid = \Drupal::routeMatch()->getParameter('mid');
    $current_user = \Drupal::currentUser();
    $query = \Drupal::database()->select('ms_react_sender', 'ms');
    $query->fields('ms', ['mid','us_id','ur_id']);
    $query->condition('ms.mid', $mid);
    $results = $query->execute()->fetchAll();
    $rows=[];
    foreach($results as $data){
      array_push($rows, $data->us_id,$data->ur_id);
    }
    $find = array_search($current_user->id(), $rows);
    if (isset($find)){
      return AccessResult::allowedIfHasPermission($account, 'view own message');
    }else{
      return AccessResult::allowedIfHasPermission($account, 'view any message');
    }
  }
  /**
   * Messaging.
   *
   * @return array
   *   Return Hello string.
   */
  public function messaging() {
    return [
      '#theme' => 'product_template',
          '#markup' => '<div id="ms_react"></div>',
          '#attached' => [
          'library' => 'ms_react/ms_react'
      ]
    ];
  }

}
