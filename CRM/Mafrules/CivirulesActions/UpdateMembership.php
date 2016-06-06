<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Mafrules_CivirulesActions_UpdateMembership extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @throws Exception
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    $sql = "SELECT id FROM `civicrm_membership` WHERE `contact_id` = %1";
    $params[1] = array($triggerData->getContactId(), 'Integer');
    if (isset($actionParams['membership_type'])) {
      $sql .= " AND `membership_type_id` IN (".implode(",", $actionParams['membership_type']).")";
    }
    if (isset($actionParams['membership_status'])) {
      $sql .= " AND `status_id` IN (".implode(",", $actionParams['membership_status']).")";
    }

    $membershipParams = array();
    $recurring = $triggerData->getEntityData('ContributionRecur');
    if ($recurring && !empty($recurring['start_date'])) {
      $start_date = new DateTime($recurring['start_date']);
      $membershipParams['start_date'] = $start_date->format('Ymd');
    } else {
      $membershipParams['start_date'] = '';
    }
    if ($recurring && !empty($recurring['end_date'])) {
      $end_date = new DateTime($recurring['end_date']);
      $membershipParams['end_date'] = $end_date->format('Ymd');
    } else {
      $membershipParams['end_date'] = '';
    }

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while($dao->fetch()) {
      $membershipParams['id'] = $dao->id;
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/updatemembership', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $types = CRM_Member_BAO_MembershipType::getMembershipTypes();
    $params = $this->getActionParameters();
    $typeLabel = '';
    if (isset($params['membership_type'])) {
      foreach($params['membership_type'] as $membership_type) {
        if (isset($types[$membership_type])) {
          if (strlen($typeLabel)) {
            $typeLabel .= ', ';
          }
          $typeLabel .= $types[$membership_type];
        }

      }

    }
    $statusLabel = '';
    if (isset($params['membership_status'])) {
      foreach ($params['membership_status'] as $status_id) {
        $membership_status = civicrm_api3('MembershipStatus', 'getvalue', array('id' => $status_id, 'return' => 'label'));
        if (strlen($statusLabel)) {
          $statusLabel .= ', ';
        }
        $statusLabel .= $membership_status;
      }
    }
    return 'Membership type is one of '.$typeLabel.' and status is one of '.$statusLabel;
  }

}