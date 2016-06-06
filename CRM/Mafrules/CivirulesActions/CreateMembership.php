<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Mafrules_CivirulesActions_CreateMembership extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @throws Exception
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $membershipParams = array();
    $actionParams = $this->getActionParameters();
    $membershipParams['membership_type_id'] = $actionParams['membership_type'];
    $membershipParams['status_id'] = $actionParams['membership_status'];
    $membershipParams['contact_id'] = $triggerData->getContactId();

    $recurring = $triggerData->getEntityData('ContributionRecur');
    if ($recurring && !empty($recurring['start_date'])) {
      $start_date = new DateTime($recurring['start_date']);
      $membershipParams['start_date'] = $start_date->format('Ymd');
    }
    if ($recurring && !empty($recurring['end_date'])) {
      $end_date = new DateTime($recurring['end_date']);
      $membershipParams['end_date'] = $end_date->format('Ymd');
    }

    try {
      civicrm_api3('Membership', 'create', $membershipParams);
    } catch (Exception $e) {
      $formattedParams = '';
      foreach($membershipParams as $key => $param) {
        if (strlen($formattedParams)) {
          $formattedParams .= ', ';
        }
        $formattedParams .= $key.' = '.$param;
      }
      throw new Exception('Civirules api action exception Membership.create ('.$formattedParams.') with reason: '.$e->getMessage());
    }
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   * $access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/createmembership', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $statuses = civicrm_api3('MembershipStatus', 'get', array());
    $types = CRM_Member_BAO_MembershipType::getMembershipTypes();
    $params = $this->getActionParameters();
    $typeLabel = '';
    if (isset($params['membership_type']) && isset($types[$params['membership_type']])) {
      $typeLabel = $types[$params['membership_type']];
    }
    $statusLabel = '';
    if (isset($params['membership_status'])) {
      foreach($statuses['values'] as $status) {
        if ($status['id'] == $params['membership_status']) {
          $statusLabel = $status['label'];
          break;
        }
      }
    }
    return 'Membership type: '.$typeLabel.' and status: '.$statusLabel;
  }

}