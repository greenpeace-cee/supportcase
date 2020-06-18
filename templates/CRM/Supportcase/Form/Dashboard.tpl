{if $notConfigured}
  {* Case types not present. Component is not configured for use. *}
  {include file="CRM/Case/Page/ConfigureError.tpl"}
{else}
  {include file="CRM/Supportcase/Form/Dashboard/SearchFileterFields.tpl"}
  {include file="CRM/Supportcase/Form/Dashboard/SearchResultCaseTabs.tpl"}
{/if}
