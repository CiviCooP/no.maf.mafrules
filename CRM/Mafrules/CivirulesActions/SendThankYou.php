<?php
/**
 * Class for CiviRules SendThankYou action
 * MAF Norge specific:
 *
 * - to be used with contribution entity
 *
 * - for security at first: always update custom field CiviRules Processed = yes
 * - if contribution is part of a recurring contribution stop further processing
 * - if custom field on contribution Thank You has value No then stop further processing
 * - if custom field earmarking of contribution has an earmarking with one of the action parameters, stop further processing
 * - if Thank You Date of the contribution is not empty, stop further processing
 *
 * - if this is first contribution of this contact:
 *   - create activity of type UT Post Ekstern with status completed and subject Handwritten Postcard, no assignee
 *   - stop further processing
 *
 * - if this is the second contribution of this contact:
 *   - create activity of type UT Post Ekstern with status scheduled and subject Second Thank You, no assignee
 *   - stop further processing
 *
 * - if contact can email (has an email address and does not have do not email): send thank you email with template specified
 *   taking times into consideration:
 *   - if earlier than start time schedule for start time
 *   - if in range, execute immediately
 *   - if later than end time schedule for start time next day
 *
 * - if contact can sms (has an mobile phone and does not have do not sms): send thank you sms with template specified
 *   taking times into consideration:
 *   - if earlier than start time schedule for start time
 *   - if in range, execute immediately
 *   - if later than end time schedule for start time next day
 *
 * - if contact can not email and not sms AND does not have do not mail
 *   AND custom field Takkebrev pr post is Yes AND contact has active address:
 *   send thank you pdf with template specified taking times into consideration:
 *   - if earlier than start time schedule for start time
 *   - if in range, execute immediately
 *   - if later than end time schedule for start time next day

 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Mafrules_CivirulesActions_SendThankYou extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_EventData_EventData $eventData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_EventData_EventData $eventData) {
    $contribution = $eventData->getEntityData('Contribution');
    $this->setSendThankYouProcessed($contribution['contribution_id']);
    $processContribution = TRUE;
    /*
     * test to check if contribution should be ignored
     */
    if ($this->contributionThankYou($contribution['id']) == FALSE) {
      $processContribution = FALSE;
    }
    if (isset($contribution['contribution_recur_id']) && !empty($contribution['contribution_recur_id'])
      && $processContribution == TRUE) {
      $processContribution = FALSE;
    }
    if (isset($contribution['thankyou_date']) && !empty($contribution['thankyou_date']) && $processContribution == TRUE) {
      $processContribution = FALSE;
    }
    if ($this->excludeEarmarking($contribution['id']) == TRUE && $processContribution == TRUE) {
      $processContribution = FALSE;
    }

    if ($processContribution) {
      $countContactContributions = $this->countContactContributions($contribution['contact_id']);
      $thankYou = new CRM_Mafrules_SendThankYou($this->getActionParameters());
      $thankYou->contributionData = $contribution;
      switch ($countContactContributions) {
        case 0:
          $thankYou->thankYouType = 'first';
          break;
        case 1:
          $thankYou->thankYouType = 'first';
          break;
        case 2:
          $thankYou->thankYouType = 'second';
          break;
        default:
          $thankYou->thankYouType = 'generic';
          break;
      }
      $thankYou->create();
    }
  }

  /**
   * Method to return count of contact contributions (completed only)
   *
   * @param $contactId
   * @return array
   * @throws CiviCRM_API3_Exception
   */
  protected function countContactContributions($contactId) {
    $contributionParams = array(
      'contact_id' => $contactId,
      'contribution_status_id' => 1,
      'options' => array('limit' => 999999));
    $contactContributions = civicrm_api3('Contribution', 'Getcount', $contributionParams);
    return $contactContributions;
  }

  /**
   * Method to get the value of custom field thank you for contribution
   *
   * @param int $contributionId
   * @return bool
   * @access protected
   */
  protected function contributionThankYou($contributionId) {
    $sendThankYou = TRUE;
    $customGroupParams = array(
      'name' => 'maf_norge_contribution_thank_you',
      'extends' => 'Contribution');
    try {
      $customGroup = civicrm_api3('CustomGroup', 'Getsingle', $customGroupParams);
      $customFieldParams = array(
        'custom_group_id' => $customGroup['id'],
        'name' => 'contribution_thank_you',
        'return' => 'column_name');
      try {
        $columnName = civicrm_api3('CustomField', 'Getvalue', $customFieldParams);
        $query = 'SELECT '.$columnName.' FROM '.$customGroup['table_name'].' WHERE entity_id = %1';
        $params = array(1 => array($contributionId, 'Integer'));
        $dao = CRM_Core_DAO::executeQuery($query, $params);
        if ($dao->fetch()) {
          $sendThankYou = $dao->$columnName;
        }
      } catch (CiviCRM_API3_Exception $ex) {}
    } catch (CiviCRM_API3_Exception $ex) {}
    return $sendThankYou;
  }

  /**
   * Method to set send thank you processed to true
   *
   * @param int $contributionId
   * @access protected
   */
  protected function setSendThankYouProcessed($contributionId) {
    if (!empty($contributionId)) {
      $query = 'REPLACE INTO civicrm_value_civirules_check SET send_thank_you_processed = %1, entity_id = %2';
      $params = array(
        1 => array(1, 'Integer'),
        2 => array($contributionId, 'Integer'));
      CRM_Core_DAO::executeQuery($query, $params);
    }
  }

  /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/sendthankyou', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $friendlyParams = array();
    $params = $this->getActionParameters();
    if (!empty($params)) {
      if (!empty($params['earmarking_id'])) {
        $formattedEarmarking = array();
        $friendlyParams[] = 'exclude earmarkings ' . implode(',', $params['earmarking_id']);
      }

      $friendlyParams[] = 'run between ' . $params['process_start_time'] . ' and ' . $params['process_end_time'];
      $friendlyParams[] = 'activity type/status for first ' . $params['first_activity_type_id'] . '/' . $params['first_activity_status_id']
        . ' and second ' . $params['second_activity_type_id'] . '/' . $params['second_activity_status_id'] . ' contribution';
      $friendlyParams[] = 'email from name ' . $params['email_from_name'] . ' and email address '
        . $params['email_from_email'] . ' with template ' . $params['email_template_id'];
      $friendlyParams[] = 'sms from provider ' . $params['sms_provider_id'] . ' with template ' . $params['sms_template_id'];
      $friendlyParams[] = 'pdf to email ' . $params['pdf_to_email'] . ' with template ' . $params['pdf_template_id'];
    }
    return implode('<br/>', $friendlyParams);
  }

  /**
   * Method to check if contribution should be left out for earmarking exclusions
   *
   * @param int $contributionId
   * @return bool
   * @access protected
   */
  protected function excludeEarmarking($contributionId) {
    $actionParams = $this->getActionParameters();
    $excludeForEarmarking = FALSE;
    $customGroupParams = array(
      'name' => 'nets_transactions',
      'extends' => 'Contribution');

    try {
      $customGroup = civicrm_api3('CustomGroup', 'Getsingle', $customGroupParams);
      $customFieldParams = array(
        'custom_group_id' => $customGroup['id'],
        'name' => 'earmarking',
        'return' => 'column_name');
      try {
        $columnName = civicrm_api3('CustomField', 'Getvalue', $customFieldParams);
        $query = 'SELECT '.$columnName.' FROM '.$customGroup['table_name'].' WHERE entity_id = %1';
        $params = array(1 => array($contributionId, 'Integer'));
        $dao = CRM_Core_DAO::executeQuery($query, $params);
        if ($dao->fetch()) {
          if (is_array($actionParams['earmarking_id'])) {
            if (in_array($dao->$columnName, $actionParams['earmarking_id'])) {
              $excludeForEarmarking = TRUE;
            }
          } else {
            if ($dao->$columnName == $actionParams['earmarking_id']) {
              $excludeForEarmarking = TRUE;
            }
          }
        }
      } catch (CiviCRM_API3_Exception $ex) {}
    } catch (CiviCRM_API3_Exception $ex) {}
    return $excludeForEarmarking;
  }

  /**
   * Overridden parent method to calculate the time when the action should be processed
   * Used here to make sure no actions are executed before the action parameters start time
   * and not after the action parameters end time
   *
   * @param DateTime $date
   * @return bool
   * @access public
   */
  public function delayTo(DateTime $date) {
    $startTime = null;
    $endTime = null;
    $actionParams = $this->getActionParameters();
    if (isset($actionParams['process_start_time']) && !empty($actionParams['process_start_time'])) {
      $paramStart = date('Ymd').date('Hi', strtotime($actionParams['process_start_time'])).'00';
      $start = new DateTime($paramStart);
      $startTime = date_format($start, 'YmdHis');
    }
    if (isset($actionParams['process_end_time']) && !empty($actionParams['process_end_time'])) {
      $paramEnd = date('Ymd').date('Hi', strtotime($actionParams['process_end_time'])).'00';
      $end = new DateTime($paramEnd);
      $endTime = date_format($end, 'YmdHis');
    }
    $nowTime = date_format($date, 'YmdHis');
    if (!empty($startTime) || !empty($endTime)) {
      if ($nowTime < $startTime) {
        return $start;
      }
      if ($nowTime > $endTime) {
        return $start->modify('+1 day');
      }
    }
    return false;
  }

}