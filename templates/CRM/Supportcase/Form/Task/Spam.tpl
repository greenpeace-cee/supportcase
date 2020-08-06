<div class="crm-block crm-form-block">
  <div class="supportcase__task-message-wrap">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      &nbsp;{ts}Are you sure you want to mark as spam the selected cases?{/ts}<br/>
      <p>{include file="CRM/Case/Form/Task.tpl"}</p>
    </div>
  </div>

  {include file="CRM/Supportcase/Form/Task/Source/SelectedCaseList.tpl"}

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
