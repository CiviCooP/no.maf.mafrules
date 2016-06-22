<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
if (_mafrules_civirules_installed()) {
  return array (
    0 =>
      array (
        'name' => 'Civirules:Action.CreateMembershipFromContributionMaf',
        'entity' => 'CiviRuleAction',
        'params' =>
          array (
            'version' => 3,
            'name' => 'maf_create_membership_from_contribution',
            'label' => 'Create membership from Contribution',
            'class_name' => 'CRM_Mafrules_CivirulesActions_CreateMembershipFromContribution',
            'is_active' => 1
          ),
      ),
  );
}