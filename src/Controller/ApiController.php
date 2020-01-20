<?php

namespace Drupal\ms_react\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class ApiController.
 */
class ApiController extends ControllerBase {

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
//  message root
  public function msRoot(){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    $mid = \Drupal::routeMatch()->getParameter('mid');
    $select = $this->injected_database->select('ms_react_index', 'ms');
    $select->fields('ms');
    $select->addField('msm', ['m_id', 'us_seId', 'us_reId', 'send_only']);
    $select->condition('m_id', $mid, "=");
    //$select->orderBy('ms_id', 'DESC');
    $result = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    //kpr($result);die();
    $current_user = \Drupal::currentUser();
    if (count($result)>0){
      foreach ($result as $row) {
        $user = '';
        if ($current_user->id() != $row['us_seId']){
          $users = \Drupal\user\Entity\User::load($row['us_seId']);
          $user = $users->realname;
        }else{
          $users = \Drupal\user\Entity\User::load($row['us_reId']);
          $user = $users->realname;
        }
        $message_item['messageRoot'] = t('between you and').' '.$user;
        $message_item['info'] = $row['send_only'];
      }
    }else{
      $message_item = t('no message');
    }

    echo json_encode($message_item);
    exit;
  }
  public function getMessage(){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    $mid = \Drupal::routeMatch()->getParameter('mid');
    $current_user = \Drupal::currentUser();
    $select = $this->injected_database->select('ms_react_message', 'ms');
    $select->fields('ms');
    $select->addField('msm', ['ms_id', 'author', 'body', 'file_id','timestamp']);
    $select->condition('mid', $mid, "=");
    $select->orderBy('ms_id', 'DESC');
    $result = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    $result_count = count($result);
    $messageIng = [];
    $messageIng["records"] = [];
    $messageIng["total"] = $result_count;
    $deletePermission = 0;
    if ($current_user->hasPermission('delete one message')){
      $deletePermission = 1;
    }
    if($result_count > 0 ) {
      foreach ($result as $row) {
        $fileSRC = '1';
        $side ='';
        if (isset($row['file_id'])){
          $file = \Drupal\file\Entity\File::load($row['file_id']);
          $fileSRC = file_create_url($file->getFileUri());
        }
        $user = \Drupal\user\Entity\User::load($row['author']);
        $avatarUrl = '1';
        if (isset($user->get("user_picture")->getValue()[0]['target_id'])){
          $userFid = $user->get("user_picture")->getValue()[0]['target_id'];
          $fil = \Drupal\file\Entity\File::load($userFid);
          $avatarUrl = file_create_url($fil->getFileUri());
        }
        if ($current_user->id() == $row['author']){
          $side = 'right';
        }else{
          $side = 'left';
        }
        $updateTime = t('@time ago', array('@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($row['timestamp'])));
        $message_item = [
          "ms_id" => $row['ms_id'],
          "author" => $user->realname,
          "authorId" => $row['author'],
          "body" => html_entity_decode($row['body']),
          "fileUrl" => $fileSRC,
          "side" => $side,
          "user_pick" => $avatarUrl,
          "timestamp" =>html_entity_decode($updateTime),
          "time" => $row['timestamp'],
          "deletePermission" => $deletePermission,
        ];
        array_push($messageIng["records"], $message_item);
      }
    }
    else{
      $message_item = [
        "ms_id" => t('no message'),
      ];
      array_push($messageIng["records"], $message_item);
    }
    echo json_encode($messageIng);
    if ($messageIng['records'][0]['authorId'] != $current_user->id() ){
      // update ms_react_index
      $result = $this->injected_database->update('ms_react_index')->fields(
        array(
          'is_new' => 0,
        )
      )->condition('m_id', $mid, "=")->execute();
    }
    exit;
  }
//  send message
  public function creatMessage(Request $request){
    //required headers
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    $current_user = \Drupal::currentUser();
    $params = array();
    $content = $request->getContent();
    if (!empty($content)) {
      // 2nd param to get as array
      $params = json_decode($content, TRUE);
    }
    // Process $params...
      $result = $this->injected_database->insert('ms_react_message')->fields(
      array(
        'mid' => $params['data']['mid'],
        'author' => $current_user->id(),
        'body' =>  $params['data']['body'],
        'file_id' => $params['data']['fid'],
        'timestamp' => time(),
      )
      );
      $ms_id = $result->execute();
    $current_user = \Drupal::currentUser();
    $select = $this->injected_database->select('ms_react_message', 'ms');
    $select->fields('ms');
    $select->addField('msm', ['ms_id', 'author', 'body', 'file_id','timestamp']);
    $select->condition('ms_id', $ms_id, "=");
    $select->orderBy('ms_id', 'ASC');
    $result = $select->execute()->fetchAll(\PDO::FETCH_ASSOC);
    $result_count = count($result);
    $messageIng = [];
    $messageIng["records"] = [];
    $messageIng["total"] = $result_count;
    if($result_count > 0 ) {
      foreach ($result as $row) {
        $fileSRC = '1';
        $side ='';
        if (isset($row['file_id'])){
          $file = \Drupal\file\Entity\File::load($row['file_id']);
          $fileSRC = file_create_url($file->getFileUri());
        }
        $user = \Drupal\user\Entity\User::load($row['author']);
        $avatarUrl = '1';
        if (isset($user->get("user_picture")->getValue()[0]['target_id'])){
          $userFid = $user->get("user_picture")->getValue()[0]['target_id'];
          $fil = \Drupal\file\Entity\File::load($userFid);
          $avatarUrl = file_create_url($fil->getFileUri());
        }
        if ($current_user->id() == $row['author']){
          $side = 'right';
        }else{
          $side = 'left';
        }
        $updateTime = t('@time ago', array('@time' => \Drupal::service('date.formatter')->formatTimeDiffSince($row['timestamp'])));
        $message_item = [
          "ms_id" => $row['ms_id'],
          "author" => $user->realname,
          "body" => html_entity_decode($row['body']),
          "fileUrl" => $fileSRC,
          "side" => $side,
          "user_pick" => $avatarUrl,
          "timestamp" =>html_entity_decode($updateTime),
        ];
        array_push($messageIng, $message_item);
      }
    }
    else{
      $message_item = [
        "ms_id" => t('no message'),
      ];
      array_push($messageIng["records"], $message_item);
    }
    // update ms_react_index
    $result = $this->injected_database->update('ms_react_index')->fields(
      array(
        'is_new' => 1,
        'up_time' => time(),
      )
    )->condition('m_id', $params['data']['mid'], "=")->execute();
    return new JsonResponse($messageIng);
  }
  //  message notification
  public function msNotification(){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    $count = 0;
    $current_user = \Drupal::currentUser();
    $query = $this->injected_database->select('ms_react_sender', 'mrs');
    $query->join('ms_react_index', 'ms', 'mrs.mid = ms.m_id');
    $query->fields('mrs',['s_id','mid', 'us_id', 'ur_id']);
    $query->condition('ms.is_new', 1, "=");
    $orCondition = $query->orConditionGroup();
    $orCondition->condition('mrs.us_id', $current_user->id(), "=");
    $orCondition->condition('mrs.ur_id', $current_user->id(), "=");
    $query->condition($orCondition);
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($result as $row) {
      $query = $this->injected_database->select('ms_react_message', 'sm');
      $query->fields('sm', ['ms_id','mid', 'author']);
      $query->condition('sm.mid', $row['mid'], "=");
      //$query->condition('sm.author', $current_user->id(), "!=");
      $query->orderBy('ms_id', 'DESC');
      $query->range(0, 1);
      $results = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
      foreach ($results as $data){
        if ($data['author'] != $current_user->id()){
          $count +=1;
        }
      }
    }
      echo json_encode($count);
    exit;
  }

  /**
   * @return JsonResponse
   */
  public function getRoute(){
    global $base_path;
    $languagecode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $url = '';
    if (isset($languagecode)){
      $url =  $base_path . $languagecode .'/';
    }else{
      $url =  $base_path . '/';
    }
    return new JsonResponse($url);
  }

  public function deleteMsid(Request $request){
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    $msid = \Drupal::routeMatch()->getParameter('msid');
    try {
      $query = $this->injected_database->delete('ms_react_message');
      $query->condition('ms_id',$msid , "=");
      $result = $query->execute();

    }catch (Exception $e) {
      \drupal::$this->messenger->addMessage('error');
    }
    return new JsonResponse('ok');
  }
  }
