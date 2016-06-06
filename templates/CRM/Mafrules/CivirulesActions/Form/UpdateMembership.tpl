<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-mafrule-action-createmembership">
    <div class="crm-section">
        <div class="label">{$form.membership_type.label}</div>
        <div class="content">{$form.membership_type.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.membership_status.label}</div>
        <div class="content">{$form.membership_status.html}</div>
        <div class="clear"></div>
    </div>

</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>