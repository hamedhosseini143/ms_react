<?php

namespace Drupal\ms_react\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Database\Connection;
use  \Drupal\user\Entity\User;

/**
 * Class CreatMessageForm.
 */
class CreatMessageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'creat_message_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $current_user = \Drupal::currentUser();
    $user = \Drupal\user\Entity\User::load($current_user->id());
    $roles = array_map(['\Drupal\Component\Utility\Html', 'escape'], user_role_names(TRUE));
    $form['send_role'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Send To Role'),
      '#access' => $roles && $user->hasPermission('send role message'),
      '#required'      => FALSE,
      '#default_value' => FALSE,
    ];
    $form['send_only'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Send only'),
      '#access' => $roles && $user->hasPermission('send role message'),
      '#required'      => FALSE,
      '#default_value' => 0,
      '#states' => [
        'visible' => [
          ':input[name="send_role"]' => array('checked' => FALSE),
        ],
      ]
    ];
    $form['notofi'] = [
      '#type'          => 'checkbox',
      '#title'         => t('Send notification'),
      '#access' => $roles && $user->hasPermission('send role message'),
      '#required'      => FALSE,
      '#default_value' => 0,
      '#states' => [
        'visible' => [
          ':input[name="send_role"]' => array('checked' => FALSE),
        ],
      ],
    ];
    $form['usend'] = [
      '#title' => $this->t('Send To'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#selection_handler' => 'default:user',
      '#selection_settings' => [
        'include_anonymous' => FALSE,
      ],
      '#states' => [
        'visible' => [
          ':input[name="send_role"]' => array('checked' => FALSE),
        ],
        'required' => [
          ':input[name="send_role"]' => array('checked' => FALSE),
        ],
      ],
    ];

    $form['roles'] = [
      '#type' => 'select',
      '#title' => $this->t('Roles'),
      '#options' => $roles,
      '#access' => $roles && $user->hasPermission('send role message'),
      '#states' => [
        'visible' => [
          ':input[name="send_role"]' => array('checked' => TRUE),
        ],
        'required' => [
          ':input[name="send_role"]' => array('checked' => TRUE),
        ],
      ],
    ];
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('message'),
      '#required'      => TRUE,
    ];
    $form['File'] = [
      '#type' => 'managed_file',
      '#title' => t('Choose  File'),
      '#upload_location' => 'public://ms_react/',
      //'#default_value' => $entity->get('File')->value,
      '#description' => t('upload file png, jpg, jpeg, psd, cpt, cdr'),
      '#states' => [
        'visible' => [
          ':input[name="File_type"]' => array('value' => t('png, jpg, jpeg, psd, cpt, cdr')),
        ],
      ],
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $current_user = \Drupal::currentUser();
    $user_id = $current_user->id();
    $send_only = $form_state->getValue('send_only');
    $send_notification = $form_state->getValue('notofi');
    //kpr($send_only);die();
    $us_id = '0';
    if (isset($user_id)){
      $us_id = $current_user->id();
    }
    $ur_id = $form_state->getValue('usend');
    $role_name = $form_state->getValue('roles');
    $send_toRole = $form_state->getValue('send_role');
    $message = $form_state->getValue('message');
    $File = $form_state->getValue('File')[0];
    $dateTime = \Drupal::time()->getCurrentTime();
    $m_id = '';
    $ms_id  = '';
    // send message to role
    if ($send_toRole == '1'){
      // insert ms_react_message
      try {
        $query =  \Drupal::database()->insert('ms_react_message_role')
          ->fields([
            'author',
            'body',
            'file_id',
            'timestamp',
          ])
          ->values(array(
            $us_id,
            $message,
            $File,
            $dateTime,
          ));
        $ms_id = $query->execute();
      }catch (Exception $e) {
        \drupal::$this->messenger->addMessage('error');
      }
      //get all user have role
      $ids = \Drupal::entityQuery('user')
        ->condition('status', 1)
        ->condition('roles', $role_name)
        ->execute();
      foreach ($ids as $uid){
        // Insert the record to ms_react_index.
        try {
          \Drupal::database()->insert('ms_react_sender_role')
            ->fields([
              'rmid',
              'us_id',
              'ur_id',
              'creat'
            ])
            ->values([
              $ms_id,
              $us_id,
              $uid,
              $dateTime
            ])
            ->execute();
        }catch (Exception $e) {
          \drupal::$this->messenger->addMessage('error');
        }
      }
      \Drupal::messenger()->addMessage(t('notification has ben send'), 'status');
    }else {
      if ($send_notification == 1) {
        try {
          $query = \Drupal::database()->insert('ms_react_message_role')
            ->fields([
              'author',
              'body',
              'file_id',
              'timestamp',
            ])
            ->values(array(
              $us_id,
              $message,
              $File,
              $dateTime,
            ));
          $ms_id = $query->execute();
        } catch (Exception $e) {
          \drupal::$this->messenger->addMessage('error');
        }
        // Insert the record to ms_react_index.
        try {
          \Drupal::database()->insert('ms_react_sender_role')
            ->fields([
              'rmid',
              'us_id',
              'ur_id',
              'creat'
            ])
            ->values([
              $ms_id,
              $us_id,
              $ur_id,
              $dateTime
            ])
            ->execute();
        } catch (Exception $e) {
          \drupal::$this->messenger->addMessage('error');
        }
        \Drupal::messenger()->addMessage(t('notification has ben send'), 'status');
      } else {
      // Insert the record to ms_react_index.
      try {
        $query = \Drupal::database()->insert('ms_react_index')
          ->fields([
            'us_seId',
            'us_reId',
            'cr_time',
            'up_time',
            'send_only'
          ])
          ->values(array(
            $us_id,
            $ur_id,
            $dateTime,
            $dateTime,
            $send_only
          ));
        $m_id = $query->execute();
      } catch (Exception $e) {
        \drupal::$this->messenger->addMessage('error');
      }
      //insert ms_react_message
      try {
        $query = \Drupal::database()->insert('ms_react_message')
          ->fields([
            'mid',
            'author',
            'body',
            'file_id',
            'timestamp',
          ])
          ->values(array(
            $m_id,
            $us_id,
            $message,
            $File,
            $dateTime,
          ));
        $ms_id = $query->execute();
      } catch (Exception $e) {
        \drupal::$this->messenger->addMessage('error');
      }
      // Insert the record to ms_react_index.
      try {
        \Drupal::database()->insert('ms_react_sender')
          ->fields([
            'mid',
            'us_id',
            'ur_id',
            'ms_id',
          ])
          ->values([
            $m_id,
            $us_id,
            $ur_id,
            $ms_id,
          ])
          ->execute();
      } catch (Exception $e) {
        \drupal::$this->messenger->addMessage('error');
      }
      $form_state->setRedirect(
        'ms_react.messaging_controller_messaging',
        ['mid' => $m_id]
      );
      \Drupal::messenger()->addMessage(t('message has ben send'), 'status');
    }
    }
  }
  /**
   * Returns a page title.
   */
  public function getTitle() {
    return t('creat mesage');
  }
}
