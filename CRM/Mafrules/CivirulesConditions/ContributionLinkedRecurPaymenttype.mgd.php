<?php
if (_mafrules_civirules_installed()) {
  return array(
    0 =>
      array(
        'name' => 'Civirules:Condition.ContributionLinkedRecurPaymenttype',
        'entity' => 'CiviRuleCondition',
        'params' =>
          array(
            'version' => 3,
            'name' => 'ContributionLinkedRecurPaymenttype',
            'label' => 'Recurring Contribution Paymenttype',
            'class_name' => 'CRM_Mafrules_CivirulesConditions_ContributionLinkedRecurPaymenttype',
            'is_active' => 1
          ),
      ),
  );
}