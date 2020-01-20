<?php

namespace Drupal\ms_react\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
/**
 * Class deleteMessageForm.
 */
class deleteMessageForm extends FormBase {

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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'delet_message_form';
  }
  public function access(AccountInterface $account) {
    $mid = \Drupal::routeMatch()->getParameter('mid');
    $current_user = \Drupal::currentUser();
    $uid = $current_user->id();
    $query = \Drupal::database()->select('ms_react_index', 'ms');
    $query->fields('ms', ['m_id','us_seId','us_reId']);
    $query->condition('ms.m_id', $mid);
    $results = $query->execute()->fetchAll();
    $rows=[];
    foreach($results as $data){
      $rows[] = array(
        'sender' =>$data->us_seId,
        'receiver' => $data->us_reId,
      );

    }
    if ($current_user->id() == $rows[0]['sender'] || $current_user->id() == $rows[0]['receiver']){
      return AccessResult::allowedIfHasPermission($account, 'delete own message');
    }else{
      return AccessResult::allowedIfHasPermission($account, 'delete any message');
    }
  }
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $mid = \Drupal::routeMatch()->getParameter('mid');
    $form['tel'] = array(
      '#type' => 'item',
      '#title' => t('Are you sure you want to delete the message ?'),
    );
    $form['cancel'] = [
      '#type' => 'submit',
      '#value' => t('cancel'),
      '#title' => $this->t('cancel'),
      '#submit' => ['::cancelSubmit'],
      '#attributes' => array('class' => array('btn btn-success')),

    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('delete'),
      '#attributes' => array('class' => array('btn btn-danger')),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mid = \Drupal::routeMatch()->getParameter('mid');
    // delete table ms_react_message
    try {
      $query = $this->injected_database->delete('ms_react_message');
      $query->condition('mid',$mid , "=");
      $result = $query->execute();

    }catch (Exception $e) {
      \drupal::$this->messenger->addMessage('error');
    }
    // delete table ms_react_sender
    try {
      $query = $this->injected_database->delete('ms_react_sender');
      $query->condition('mid',$mid , "=");
      $result = $query->execute();

    }catch (Exception $e) {
      \drupal::$this->messenger->addMessage('error');
    }
    // delete table ms_react_index
    try {
      $query = $this->injected_database->delete('ms_react_index');
      $query->condition('m_id',$mid , "=");
      $result = $query->execute();

    }catch (Exception $e) {
      \drupal::$this->messenger->addMessage('error');
    }
    $form_state->setRedirect(
      'ms_react.all_message_controller_allMessage'
    );
    \Drupal::messenger()->addMessage(t('Delete Succes'), 'status');
  }
  /**
   * {@inheritdoc}
   */
  public function cancelSubmit(array &$form, FormStateInterface $form_state) {
    $mid = \Drupal::routeMatch()->getParameter('mid');
    $form_state->setRedirect(
      'ms_react.messaging_controller_messaging',
      ['mid' => $mid]
    );
  }

}

