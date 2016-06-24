<?php

if (_mafrules_civirules_installed()) {
  return array (
    0 =>
      array (
        'name' => 'Civirules:Action.PdfDelayedByContribution',
        'entity' => 'CiviRuleAction',
        'params' =>
          array (
            'version' => 3,
            'name' => 'PdfDelayedByContribution',
            'label' => 'Send PDF (delayed by contribution)',
            'class_name' => 'CRM_Mafrules_CivirulesActions_PdfDelayedByContribution',
            'is_active' => 1
          ),
      ),
  );
}