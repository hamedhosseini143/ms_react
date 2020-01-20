<?php

namespace Drupal\ms_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class inboxMessageController.
 */
class inboxMessageController extends ControllerBase {
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
   * Inboxmessage.
   *
   * @return string
   *   Return Hello string.
   */
  public function InboxMessage() {
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $header_table = array(
      'uid'=>    t('message id'),
      'userSend'=>    t('between you'),
      'is_new'=>    t('message'),
      'lastTime' => t('Send the first message'),
      'creatTime' => t('updated Message'),
    );
    $query = $this->injected_database->select('ms_react_index', 'ms');
    $query->join('ms_react_sender', 'mrs', 'ms.m_id = mrs.mid');
    $query->fields('ms',['m_id', 'us_seId', 'us_reId', 'is_new', 'cr_time', 'up_time']);
    $query->fields('mrs',['s_id', 'us_id']);
    $query->condition('mrs.ur_id', $uid, "=");
    $query->orderBy('up_time', 'DESC');
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    foreach($result as $data){
      $query = $this->injected_database->select('ms_react_message', 'sm');
      $query->fields('sm', ['ms_id','mid' ,'author']);
      $query->condition('sm.mid', $data['m_id'], "=");
      $query->orderBy('ms_id', 'DESC');
      $query->range(0, 1);
      $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      $userReceive = '';
      if (empty($data['role_name'])){
        $users = \Drupal\user\Entity\User::load($data['us_id']);
        $userReceive = $users->realname;
      }else{
        $userReceive = $data['role_name']. ' '. t('role');
      }
      $isNew = '';
      if ($current_user->id() != $results[0][author]){
        if ($data['is_new'] == 1 ){
          $isNew =  t('new');
        }
      }
      $creatTime = t('@time ago', array('@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($data['cr_time'])));
      $updateTime = t('@time ago', array('@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($data['up_time'])));
      $messageId = Url::fromUserInput('/ms/chat/'.$data['m_id']);
      $rows[] = array(
        \Drupal::l($data['m_id'], $messageId),
        'userReceive' => $userReceive,
        \Drupal::l(count($results).' '.$isNew , $messageId),
        'cr_time' => $creatTime,
        'up_time' => $updateTime ,
      );
    }
    /** @var row $rows */
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header_table,
      '#rows' => $rows,
      '#empty' => t('No send message'),
    ];
    return $form;
  }
  /**
   * Returns a page title.
   */
  public function getTitle() {
    return t('message inbox');
  }

}
