<?php
if (_mafrules_civirules_installed()) {
  return array(
    0 =>
      array(
        'name' => 'Civirules:Condition.ContributionLinkedRecurEarmarking',
        'entity' => 'CiviRuleCondition',
        'params' =>
          array(
            'version' => 3,
            'name' => 'ContributionLinkedRecurEarmarking',
            'label' => 'Recurring Contribution Earmarking',
            'class_name' => 'CRM_Mafrules_CivirulesConditions_ContributionLinkedRecurEarmarking',
            'is_active' => 1
          ),
      ),
  );
}