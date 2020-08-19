<div class="crm-block crm-form-block">
  <div class="supportcase__task-message-wrap">
    <div class="messages status no-popup">
      <div class="icon inform-icon"></div>
      {if $caseExistence}
        <span>{ts}Are you sure you want to mark as spam the case?{/ts}</span>
        <br/>
        <span><b>Subject:</b> {$case->subject};</span>
        <br/>
        <span><b>Case id</b> = {$case->id};</span>
      {else}
        <span>{ts}Case does not exist. Looks like someone has already deleted this case.{/ts}</span>
      {/if}
    </div>
  </div>

  <div class="crm-submit-buttons">
      {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
