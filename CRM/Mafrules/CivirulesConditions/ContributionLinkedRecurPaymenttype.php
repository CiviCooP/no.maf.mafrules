<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_Mafrules_CivirulesConditions_ContributionLinkedRecurPaymenttype extends CRM_CivirulesConditions_Generic_ValueComparison {

  /**
   * Returns the value of the field for the condition
   * For example: I want to check if age > 50, this function would return the 50
   *
   * @param object CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return
   * @access protected
   * @abstract
   */
  protected function getFieldValue(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $recur_id = false;
    $contribution = $triggerData->getEntityData('Contribution');
    $contributionRecur = $triggerData->getEntityData('ContributionRecur');
    if ($contributionRecur && !empty($contributionRecur['id'])) {
      $recur_id = $contributionRecur['id'];
    } elseif ($contribution && !empty($contribution['contribution_recur_id'])) {
      $recur_id = $contribution['contribution_recur_id'];
    }
    if (!$recur_id) {
      return null;
    }

    $queryParams[1] = array($recur_id, 'Integer');
    $value = CRM_Core_DAO::singleValueQuery("select payment_type_id from civicrm_contribution_recur_offline WHERE recur_id = %1", $queryParams);
    if (!$value) {
      return null;
    }
    return $value;
  }

  /**
   * Returns an array with all possible options for the field, in
   * case the field is a select field, e.g. gender, or financial type
   * Return false when the field is a select field
   *
   * This method could be overriden by child classes to return the option
   *
   * The return is an array with the field option value as key and the option label as value
   *
   * @return bool
   */
  public function getFieldOptions() {
    return _recurring_getPaymentTypes();
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $options = $this->getFieldOptions();
    $value = $this->getComparisonValue();
    if (isset($options[$value])) {
      $value = $options[$value];
    }
    return htmlentities(($this->getOperator())).' '.htmlentities($value);
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/conditions/mafrules/valuecomparison/', 'rule_condition_id='.$ruleConditionId);
  }

}