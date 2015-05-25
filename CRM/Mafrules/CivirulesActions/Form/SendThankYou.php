<?php

require_once 'CRM/Core/Form.php';

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Mafrules_CivirulesActions_Form_SendThankYou extends CRM_Core_Form {

  protected $ruleActionId = false;
  protected $ruleAction;
  protected $action;

  /**
   * Overridden parent method to do pre-form building processing
   *
   * @throws Exception when action or rule action not found
   * @access public
   */
  public function preProcess() {
    $this->ruleActionId = CRM_Utils_Request::retrieve('rule_action_id', 'Integer');

    $this->ruleAction = new CRM_Civirules_BAO_RuleAction();
    $this->action = new CRM_Civirules_BAO_Action();
    $this->ruleAction->id = $this->ruleActionId;
    if ($this->ruleAction->find(true)) {
      $this->action->id = $this->ruleAction->action_id;
      if (!$this->action->find(true)) {
        throw new Exception('CiviRules Could not find action with id '.$this->ruleAction->action_id);
      }
    } else {
      throw new Exception('CiviRules Could not find rule action with id '.$this->ruleActionId);
    }

    parent::preProcess();
  }

  /**
   * Method to get templates
   *
   * @return array
   * @access protected
   */
  protected function getMessageTemplates() {
    $return = array('' => ts('-- please select --'));
    $dao = CRM_Core_DAO::executeQuery("SELECT * FROM `civicrm_msg_template` WHERE `is_active` = 1 AND `workflow_id` IS NULL");
    while($dao->fetch()) {
      $return[$dao->id] = $dao->msg_title;
    }
    return $return;
  }

  /**
   * Method to get the SMS providers
   *
   * @return array $smsProviders
   * @access protected
   */
  protected function getSmsProviders() {
    $smsProviders = array();
    $query = 'SELECT id, title FROM civicrm_sms_provider WHERE is_active = %1';
    $params = array(1 => array(1, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    while ($dao->fetch()) {
      $smsProviders[$dao->id] = $dao->title;
    }
    $smsProviders[0] = '- select -';
    asort($smsProviders);
    return $smsProviders;
  }

  /**
   * Method to get the custom field option values for earmarking
   *
   * @return array $earMarkingValues
   * @access protected
   */
  protected function getEarmarking() {
    $earmarkingValues = array();
    $optionGroupParams = array(
      'name' => 'earmarking',
      'return' => 'id');
    try {
      $optionGroupId = civicrm_api3('OptionGroup', 'Getvalue', $optionGroupParams);
      $optionValueParams = array(
        'option_group_id' => $optionGroupId,
        'is_active' => 1);
      $optionValues = civicrm_api3('OptionValue', 'Get', $optionValueParams);
      foreach ($optionValues['values'] as $optionValue) {
        $earmarkingValues[$optionValue['value']] = $optionValue['label'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}
    $earmarkingValues[0] = '- select -';
    asort($earmarkingValues);
    return $earmarkingValues;
  }

  /**
   * Method to get the active activity types
   *
   * @return array $activityTypes
   * @access protectd
   */
  protected function getActivityTypes() {
    $activityTypes = CRM_Core_PseudoConstant::activityType();
    $activityTypes[0] = '- select -';
    asort($activityTypes);
    return $activityTypes;
  }

  /**
   * Method to get the active activity status
   *
   * @return array $activitStatus
   * @access protectd
   */
  protected function getActivityStatus() {
    $activityStatus = CRM_Core_PseudoConstant::activityStatus();
    $activityStatus[0] = '- select -';
    asort($activityStatus);
    return $activityStatus;
  }

  function buildQuickForm() {

    $this->setFormTitle();

    $this->add('hidden', 'rule_action_id');

    $this->add('text', 'pdf_to_email', ts('To e-mail address'), true);
    $this->addRule('pdf_to_email', ts('Email is not valid.'), 'email');

    $this->add('text', 'email_from_email', ts('From e-mail address'), true);
    $this->addRule('email_from_email', ts('Email is not valid.'), 'email');

    $this->add('text', 'email_from_name', ts('From name'), true);

    $this->add('select', 'email_template_id', ts('Message template'), $this->getMessageTemplates(), true);
    $this->add('select', 'sms_template_id', ts('Message template'), $this->getMessageTemplates(), true);
    $this->add('select', 'sms_provider_id', ts('SMS provider'), $this->getSmsProviders(), true);
    $this->add('select', 'pdf_template_id', ts('Message template'), $this->getMessageTemplates(), true);
    $this->add('select', 'first_activity_type_id', ts('Activity Type'), $this->getActivityTypes(), true);
    $this->add('select', 'second_activity_type_id', ts('Activity Type'), $this->getActivityTypes(), true);
    $this->add('select', 'first_activity_status_id', ts('Activity Status'), $this->getActivityStatus(), true);
    $this->add('select', 'second_activity_status_id', ts('Activity Status'), $this->getActivityStatus(), true);
    $this->add('select', 'earmarking_id', ts('Earmarking'), $this->getEarmarking());

    $earmarkingSelect = $this->addElement('advmultiselect', 'earmarking_id',
      ts('Available Earmarking') . ' ', $this->getEarmarking(),
      array('size' => 5,
        'style' => 'width:150px',
        'class' => 'advmultiselect')
    );

    $earmarkingSelect->setButtonAttributes('add', array('value' => ts('Exclude >>')));
    $earmarkingSelect->setButtonAttributes('remove', array('value' => ts('<< Include')));

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $data = array();
    $defaultValues = array();
    $defaultValues['rule_action_id'] = $this->ruleActionId;
    if (!empty($this->ruleAction->action_params)) {
      $data = unserialize($this->ruleCondition->action_params);
    }
    if (!empty($data['to_email'])) {
      $defaultValues['to_email'] = $data['to_email'];
    }
    if (!empty($data['template_id'])) {
      $defaultValues['template_id'] = $data['template_id'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data['earmarking_id'] = $this->_submitValues['earmarking_id'];
    $data['first_activity_type_id'] = $this->_submitValues['first_activity_type_id'];
    $data['first_activity_status_id'] = $this->_submitValues['first_activity_status_id'];
    $data['second_activity_type_id'] = $this->_submitValues['second_activity_type_id'];
    $data['second_activity_status_id'] = $this->_submitValues['second_activity_status_id'];
    $data['email_from_name'] = $this->_submitValues['email_from_name'];
    $data['email_from_email'] = $this->_submitValues['email_from_email'];
    $data['email_template_id'] = $this->_submitValues['email_template_id'];
    $data['sms_provider_id'] = $this->_submitValues['sms_provider_id'];
    $data['sms_template_id'] = $this->_submitValues['sms_template_id'];
    $data['pdf_to_email'] = $this->_submitValues['pdf_to_email'];
    $data['pdf_template_id'] = $this->_submitValues['pdf_template_id'];

    $ruleAction = new CRM_Civirules_BAO_RuleAction();
    $ruleAction->id = $this->ruleActionId;
    $ruleAction->action_params = serialize($data);
    $ruleAction->save();

    $session = CRM_Core_Session::singleton();
    $session->setStatus('Action '.$this->action->label.' parameters updated to CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleAction->rule_id),
      'Action parameters updated', 'success');

    $redirectUrl = CRM_Utils_System::url('civicrm/civirule/form/rule', 'action=update&id='.$this->ruleAction->rule_id, TRUE);
    CRM_Utils_System::redirect($redirectUrl);
  }

  /**
   * Method to set the form title
   *
   * @access protected
   */
  protected function setFormTitle() {
    $title = 'CiviRules Edit Action parameters';
    $this->assign('ruleActionHeader', 'Edit action '.$this->action->label.' of CiviRule '.CRM_Civirules_BAO_Rule::getRuleLabelWithId($this->ruleAction->rule_id));
    CRM_Utils_System::setTitle($title);
  }
}