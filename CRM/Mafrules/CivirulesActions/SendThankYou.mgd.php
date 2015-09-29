<?php

if (_mafrules_civirules_installed()) {
  return array (
    0 =>
      array (
        'name' => 'Civirules:Action.SendThankYou',
        'entity' => 'CiviRuleAction',
        'params' =>
          array (
            'version' => 3,
            'name' => 'maf_send_thank_you',
            'label' => 'Send Thank You',
            'class_name' => 'CRM_Mafrules_CivirulesActions_SendThankYou',
            'is_active' => 1
          ),
      ),
  );
}