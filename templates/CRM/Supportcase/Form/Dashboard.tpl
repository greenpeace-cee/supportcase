<div id="bootstrap-theme" class="sc__page-wrap">
  {if !$isCaseComponentEnabled}
    <div class="messages status no-popup">
      <span class="msg-title">{ts}Can't show page content.{/ts}</span>
      <span class="msg-title">{ts}Cases component is disabled.{/ts}</span>
      <span class="msg-title">{ts}To enable go to the:{/ts}</span>
      <a href="{crmURL p='civicrm/admin/setting/component' q="&action=update&reset=1"}">
        <span>{ts}Enable CiviCRM Components page{/ts}</span>
      </a>
    </div>
  {elseif $notConfigured}
    {* Case types not present. Component is not configured for use. *}
    {include file="CRM/Case/Page/ConfigureError.tpl"}
  {else}
    {include file="CRM/Supportcase/Form/Dashboard/SearchFileterFields.tpl"}
    {include file="CRM/Supportcase/Form/Dashboard/SearchResultCaseTabs.tpl"}
  {/if}
</div>
