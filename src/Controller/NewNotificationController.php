<?php

namespace Drupal\ms_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewNotificationController.
 */
class NewNotificationController extends ControllerBase {
  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $injected_database;

  public function __construct(Connection $injected_database) {
    $this->injected_database = $injected_database;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }
  /**
   * Newnotofi.
   *
   * @return array
   *   Return Hello string.
   */
  public function newNotofi() {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $query = $this->injected_database->select('ms_react_message_role', 'ms');
    $query->join('ms_react_sender_role', 'mrs', 'ms.rmid = mrs.rmid');
    $query->fields('ms',['author', 'body', 'file_id', 'timestamp']);
    $query->condition('mrs.ur_id', $uid, "=");
    $query->condition('mrs.is_new', 1, "=");
    $query->orderBy('timestamp', 'DESC');
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    $row = [];
    foreach($result as $data) {
      $fileSRC = '';
      if (isset($data['file_id'])){
        $file = \Drupal\file\Entity\File::load($data['file_id']);
        $fileSRC = file_create_url($file->getFileUri());
      }
      $updateTime = t('@time ago', array('@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($data['timestamp'])));
      $row[] = [
        "body" => html_entity_decode($data['body']),
        "creat" => $updateTime,
        "file" => $fileSRC,
      ];
      // update ms_react_sender_role
      $result = $this->injected_database->update('ms_react_sender_role')->fields(
        array(
          'is_new' => 0,
        )
      )
        ->condition('is_new', 1, "=")
        ->condition('ur_id', $uid, "=")
        ->execute();
    }
    return [
      '#theme' => 'ms_react_notification',
      '#list_type' => 'ul',
      '#title' => t('my notification'),
      '#items' => $row,
    ];
  }
  /**
   * Returns a page title.
   */
  public function getTitle() {
    return t('new notification');
  }
  /**
   * Newnotofi.
   *
   * @return array
   *   Return Hello string.
   */
  public function count() {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $query = $this->injected_database->select('ms_react_message_role', 'ms');
    $query->join('ms_react_sender_role', 'mrs', 'ms.rmid = mrs.rmid');
    $query->fields('ms',['author', 'body', 'file_id', 'timestamp']);
    $query->condition('mrs.ur_id', $uid, "=");
    $query->condition('mrs.is_new', 1, "=");
    $query->orderBy('timestamp', 'DESC');
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    $newCount = count($result);
    return [
      '#theme' => 'ms_react_notification_cunt',
      '#items' => $newCount,
      '#attached' => [
        'library' => 'ms_react/ms_react_my_notification'
      ]
    ];
  }
}
