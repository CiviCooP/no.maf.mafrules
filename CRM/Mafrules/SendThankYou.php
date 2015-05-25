<?php
/**
 * Class SendThankYou
 * Create activity for types first andond, and sends Thank You by email,
 * sms or pdf for generic
 *
 * MAF Norge specific:
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license AGPL-3.0
 */

class CRM_Mafrules_SendThankYou {
  public $thankYouType = null;
  public $actionParams = null;
  public $contributionData = null;

  /**
   * Constructor
   *
   * @params array $actionParams
   */
  public function __construct($actionParams) {
    $this->actionParams = $actionParams;
  }

  /**
   * Method to create the thank you
   *
   * @access public
   */
  public function create() {
    switch($this->thankYouType) {
      case 'first':
        $this->createFirstContributionActivity();
        break;
      case 'second':
        $this->createSecondContributionActivity();
        break;
      case 'generic':
        $communicationMethod = $this->retrieveContactCommunication($this->contributionData['contact_id']);
        switch ($communicationMethod) {
          case 'pdf':
            $this->sendPdf();
            break;
          case 'email':
            $this->sendEmail();
            break;
          case 'sms':
            $this->sendSms();
            break;
          case 'none';
            break;
          default:
            $this->sendSms();
            $this->sendEmail();
            break;
        }
    }
  }
  /**
   * Method to send SMS with API
   *
   * @access protected
   */
  protected function sendSms() {
    $params = array(
      'provider_id' => $this->actionParams['sms_provider_id'],
      'template_id' => $this->actionParams['sms_template_id'],
      'contact_id' => $this->contributionData['contact_id']
    );
    civicrm_api3('Sms', 'Send', $params);
  }

  /**
   * Method to send Email with API
   *
   * @access protected
   */
  protected function sendEmail() {
    $params = array(
      'email' => $this->actionParams['email_from_email'],
      'template_id' => $this->actionParams['email_template_id'],
      'contact_id' => $this->contributionData['contact_id']
    );
    if (isset($this->actionParams['email_from_name']) && !empty($this->actionParams['email_from_name'])) {
      $params['from_name'] = $this->actionParams['email_from_name'];
    }
    civicrm_api3('Email', 'Send', $params);
  }

  /**
   * Method to send PDF with API
   *
   * @access protected
   */
  protected function sendPdf() {
    $params = array(
      'to_email' => $this->actionParams['pdf_to_email'],
      'template_id' => $this->actionParams['pdf_template_id'],
      'contact_id' => $this->contributionData['contact_id']
    );
    civicrm_api3('Pdf', 'Create', $params);
  }

  /**
   * Method to create the thank you activity for the handwritten postcard at first contribution
   *
   *@access protected
   */
  protected function createFirstContributionActivity() {
    if (isset($this->actionParams['first_activity_params']) && !empty($this->actionParams['first_activity_type_id'])) {
      $activityTypeId = $this->actionParams['first_activity_type_id'];
    } else {
      $activityTypeId = 22; //print PDF in core
    }
    if (isset($this->actionParams['first_activity_status_id']) && !empty($this->actionParams['first_activity_status_id'])) {
      $activityStatusId = $this->actionParams['first_activity_status_id'];
    } else {
      $activityStatusId = 2; //completed in core
    }
    $subject = 'First Contribution Thank You Postcard (amount '.CRM_Utils_Money::format($this->contributionData['total_amount']).' on '.date('Y-m-d', strtotime($this->contributionData['receive_date'])).')';
    $activityParams = array(
      'activity_type_id' => $activityTypeId,
      'status_id' => $activityStatusId,
      'activity_date_time' => date('Ymd'),
      'subject' => $subject,
      'target_id' => $this->contributionData['contact_id']);
    civicrm_api3('Activity', 'Create', $activityParams);
  }

  /**
   * Method to create the thank you activity at second contribution
   *
   *@access protected
   */
  protected function createSecondContributionActivity() {
    if (isset($this->actionParams['second_activity_params']) && !empty($this->actionParams['second_activity_type_id'])) {
      $activityTypeId = $this->actionParams['second_activity_type_id'];
    } else {
      $activityTypeId = 22; //print PDF in core
    }
    if (isset($this->actionParams['second_activity_status_id']) && !empty($this->actionParams['second_activity_status_id'])) {
      $activityStatusId = $this->actionParams['second_activity_status_id'];
    } else {
      $activityStatusId = 1; //scheduled in core
    }
    $subject = 'Second Contribution Thank You (amount '.CRM_Utils_Money::format($this->contributionData['total_amount']).' on '.date('Y-m-d', strtotime($this->contributionData['receive_date'])).')';
    $activityParams = array(
      'activity_type_id' => $activityTypeId,
      'status_id' => $activityStatusId,
      'activity_date_time' => date('Ymd'),
      'subject' => $subject,
      'target_id' => $this->contributionData['contact_id']);
    civicrm_api3('Activity', 'Create', $activityParams);
  }

  /**
   * method to retrieve the communication method for the thank you contact
   * - if contact can not be communicated with, return 'none'
   * - if contact can only be contacted by letter, return 'pdf'
   * - if contact can only be contacted by letter or email, return 'email'
   * - if contact can only be contacted by letter or sms, return 'sms'
   * - if contact can be contacted by both email and sms, return 'both'
   *
   * @param int $contactId
   * @return string
   * @access public
   */
  public function retrieveContactCommunication($contactId) {
    $communicationMethod = '';
    $canSms = $this->contactSmsAllowed($contactId);
    $canEmail = $this->contactEmailAllowed($contactId);
    $canPdf = $this->contactPdfAllowed($contactId);
    if ($canEmail == TRUE && $canSms == TRUE) {
      return 'both';
    }
    if ($canEmail == TRUE && $canSms == FALSE) {
      return 'email';
    }
    if ($canSms == TRUE && $canEmail == FALSE) {
      return 'sms';
    }
    if ($canPdf == TRUE) {
      return 'pdf';
    }
    if ($canSms == FALSE && $canEmail == FALSE && $canPdf == FALSE) {
      return 'none';
    }
    return $communicationMethod;
  }

  /**
   * Method to check if contact can be contacted by sms. This can only happen if the contact has a phone of the type mobile and
   * do not SMS is not checked
   *
   * @param int $contactId
   * @return boolean
   * @access public
   */
  public function contactSmsAllowed($contactId) {
    $smsAllowed = FALSE;
    $params = array(
      'id' => $contactId,
      'return' => 'do_not_sms');
    try {
      $doNotSms = civicrm_api3('Contact', 'Getvalue', $params);
      if ($doNotSms == TRUE) {
        $smsAllowed = FALSE;
      } else {
        $countParams = array(
          'contact_id' => $contactId,
          'phone_type_id' => 2);
        try {
          $mobileCount = civicrm_api3('Phone', 'Getcount', $countParams);
          if ($mobileCount > 0) {
            $smsAllowed = TRUE;
          } else {
            $smsAllowed = FALSE;
          }
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    }   catch (CiviCRM_API3_Exception $ex) {}
    return $smsAllowed;
  }

  /**
   * Method to check if contact can be contacted by email. This can only happen if the contact has an email address and
   * do not Email is not checked
   *
   * @param int $contactId
   * @return boolean
   * @access public
   */
  public function contactEmailAllowed($contactId) {
    $emailAllowed = FALSE;
    $params = array(
      'id' => $contactId,
      'return' => 'do_not_email');
    try {
      $doNotEmail = civicrm_api3('Contact', 'Getvalue', $params);
      if ($doNotEmail == TRUE) {
        $emailAllowed = FALSE;
      } else {
        try {
          $emailCount = civicrm_api3('Email', 'Getcount', array('contact_id' => $contactId));
          if ($emailCount > 0) {
            $emailAllowed = TRUE;
          } else {
            $emailAllowed = FALSE;
          }
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    }   catch (CiviCRM_API3_Exception $ex) {}
    return $emailAllowed;
  }

  /**
   * Method to check if contact can be contacted by pdf. This can only happen if the contact has an address with a postal code and
   * do not Mail is not checked
   *
   * @param int $contactId
   * @return boolean
   * @access public
   */
  public function contactPdfAllowed($contactId) {
    $pdfAllowed = FALSE;
    $params = array(
      'id' => $contactId,
      'return' => 'do_not_mail');
    try {
      $doNotMail = civicrm_api3('Contact', 'Getvalue', $params);
      if ($doNotMail == TRUE) {
        $pdfAllowed = FALSE;
      } else {
        try {
          $contactAddresses = civicrm_api3('Address', 'Get', array('contact_id' => $contactId));
          foreach ($contactAddresses as $contactAddress) {
            if (!empty($contactAddress['postal_code'])) {
              $pdfAllowed = TRUE;
            }
          }
        } catch (CiviCRM_API3_Exception $ex) {}
      }
    }   catch (CiviCRM_API3_Exception $ex) {}
    return $pdfAllowed;
  }
}