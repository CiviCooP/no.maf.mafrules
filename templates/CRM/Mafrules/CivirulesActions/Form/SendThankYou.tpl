<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-mafrule-action-sendthankyou">
  <fieldset><legend>{ts}Earmarking to exclude (will NOT send Thank You){/ts}</legend>
    <div class="crm-section">
      <div class="label">{$form.earmarking_id.label}</div>
      <div class="content">{$form.earmarking_id.html}</div>
      <div class="clear"></div>
    </div>
    <fieldset><legend>{ts}First contribution :{/ts}</legend>
      <div class="crm-section">
        <div class="label">{$form.first_activity_type_id.label}</div>
        <div class="content">{$form.first_activity_type_id.html}</div>
        <div class="clear"></div>
      </div>
        <div class="crm-section">
          <div class="label">{$form.first_activity_status_id.label}</div>
          <div class="content">{$form.first_activity_status_id.html}</div>
          <div class="clear"></div>
        </div>
      </fieldset>
      <fieldset><legend>{ts}Second contribution :{/ts}</legend>
      <div class="crm-section">
        <div class="label">{$form.second_activity_type_id.label}</div>
        <div class="content">{$form.second_activity_type_id.html}</div>
        <div class="clear"></div>
      </div>
      <div class="crm-section">
        <div class="label">{$form.second_activity_status_id.label}</div>
        <div class="content">{$form.second_activity_status_id.html}</div>
        <div class="clear"></div>
      </div>
      </fieldset>
  </fieldset>
  <fieldset><legend>{ts}Email configuration{/ts}</legend>
    <div class="crm-section">
      <div class="label">{$form.email_from_name.label}</div>
      <div class="content">{$form.email_from_name.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.email_from_email.label}</div>
      <div class="content">{$form.email_from_email.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.email_template_id.label}</div>
      <div class="content">{$form.email_template_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.email_start_time.label}</div>
      <div class="content">{$form.email_start_time.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.email_end_time.label}</div>
      <div class="content">{$form.email_end_time.html}</div>
      <div class="clear"></div>
    </div>
  </fieldset>
  <fieldset><legend>{ts}SMS configuration{/ts}</legend>
    <div class="crm-section">
      <div class="label">{$form.sms_provider_id.label}</div>
      <div class="content">{$form.sms_provider_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.sms_template_id.label}</div>
      <div class="content">{$form.sms_template_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.sms_start_time.label}</div>
      <div class="content">{$form.sms_start_time.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.sms_end_time.label}</div>
      <div class="content">{$form.sms_end_time.html}</div>
      <div class="clear"></div>
    </div>
  </fieldset>
  <fieldset><legend>{ts}PDF configuration{/ts}</legend>
    <div class="crm-section">
      <div class="label">{$form.pdf_to_email.label}</div>
      <div class="content">{$form.pdf_to_email.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.pdf_template_id.label}</div>
      <div class="content">{$form.pdf_template_id.html}</div>
      <div class="clear"></div>
    </div>
  </fieldset>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>