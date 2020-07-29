<div class="crm-block crm-form-block">
  <div class="messages status no-popup">
    <div class="icon inform-icon"></div>
    &nbsp;{ts}Are you sure you want to change category to the selected cases?{/ts}<br/>
    <p>{include file="CRM/Case/Form/Task.tpl"}</p>
  </div>

  <div class="crm-section">
    <div class="label">{$form.case_category.label}</div>
    <div class="content">{$form.case_category.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
