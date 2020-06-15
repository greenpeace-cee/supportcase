{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{* CiviCase -  view case screen*}

<div class="crm-block crm-form-block crm-case-caseview-form-block">

  {* here we are showing related cases w/ jquery dialog *}
  {if $showRelatedCases}
    {include file="CRM/Case/Form/ViewRelatedCases.tpl"}
  {* Main case view *}
  {else}
  <table class="report crm-entity case-summary" data-entity="case" data-id="{$caseID}" data-cid="{$contactID}">
    {if $multiClient}
      <tr class="crm-case-caseview-client">
        <td colspan="5" class="label">
          {ts}Clients:{/ts}
          {foreach from=$caseRoles.client item=client name=clients}
            <a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$client.contact_id`"}" title="{ts}View contact record{/ts}">{$client.display_name}</a>{if not $smarty.foreach.clients.last}, &nbsp; {/if}
          {/foreach}
          <a href="#addClientDialog" class="crm-hover-button case-miniform" title="{ts}Add Client{/ts}" data-key="{crmKey name='civicrm/case/ajax/addclient'}">
            <i class="crm-i fa-user-plus"></i>
          </a>
          <div id="addClientDialog" class="hiddenElement">
            <input name="add_client_id" placeholder="{ts}- select contact -{/ts}" class="huge" data-api-params='{ldelim}"params": {ldelim}"contact_type": "{$contactType}"{rdelim}{rdelim}' />
          </div>
          {if $hasRelatedCases}
            <div class="crm-block relatedCases-link"><a class="crm-hover-button crm-popup medium-popup" href="{$relatedCaseUrl}">{$relatedCaseLabel}</a></div>
          {/if}
        </td>
      </tr>
    {/if}
    <tr>
      <td class="crm-case-caseview-{$caseID} label" style="font-weight: 600; font-size: 16px;">
        #{$caseID}
      </td>
      {if not $multiClient}
        <td>
          <table class="form-layout-compressed">
            {foreach from=$caseRoles.client item=client}
              <tr class="crm-case-caseview-display_name">
                <td class="label-left bold" style="padding: 0px; border: none;">
                  <a href="{crmURL p='civicrm/contact/view' q="action=view&reset=1&cid=`$client.contact_id`"}" title="{ts}View contact record{/ts}">{$client.display_name}</a>
                </td>
              </tr>
              {if $client.phone}
                <tr class="crm-case-caseview-phone">
                  <td class="label-left description" style="padding: 1px">{$client.phone}</td>
                </tr>
              {/if}
              {if $client.birth_date}
                <tr class="crm-case-caseview-birth_date">
                  <td class="label-left description" style="padding: 1px">{ts}DOB{/ts}: {$client.birth_date|crmDate}</td>
                </tr>
              {/if}
            {/foreach}
          </table>
          {if $hasRelatedCases}
            <div class="crm-block relatedCases-link"><a class="crm-hover-button crm-popup medium-popup" href="{$relatedCaseUrl}">{$relatedCaseLabel}</a></div>
          {/if}
        </td>
      {/if}
      <td class="crm-case-caseview-case_subject label">
        <span class="crm-case-summary-label">{ts}Subject{/ts}:</span>&nbsp;<span class="crm-editable" data-field="subject">{$caseDetails.case_subject}</span>
      </td>
      <td class="crm-case-caseview-case_status label">
        <span class="crm-case-summary-label">{ts}Status{/ts}:</span>&nbsp;{$caseDetails.case_status}&nbsp;<a class="crm-hover-button crm-popup"  href="{crmURL p='civicrm/case/activity' q="action=add&reset=1&cid=`$contactId`&caseid=`$caseId`&selectedChild=activity&atype=`$changeCaseStatusId`"}" title="{ts}Change case status (creates activity record){/ts}"><i class="crm-i fa-pencil"></i></a>
      </td>
      <td class="crm-case-caseview-case_start_date label">
        <span class="crm-case-summary-label">{ts}Open Date{/ts}:</span>&nbsp;{$caseDetails.case_start_date|crmDate}&nbsp;<a class="crm-hover-button crm-popup"  href="{crmURL p='civicrm/case/activity' q="action=add&reset=1&cid=`$contactId`&caseid=`$caseId`&selectedChild=activity&atype=`$changeCaseStartDateId`"}" title="{ts}Change case start date (creates activity record){/ts}"><i class="crm-i fa-pencil"></i></a>
      </td>
      <td>
{include file="CRM/common/Tagset.tpl" tagsetType='case'}
      </td>
    </tr>
  </table>
  {if $hookCaseSummary}
    <div id="caseSummary" class="crm-clearfix">
      {foreach from=$hookCaseSummary item=val key=div_id}
        <div id="{$div_id}"><label>{$val.label}</label><div class="value">{$val.value}</div></div>
      {/foreach}
    </div>
  {/if}
  <div id="communication" class="crm-accordion-wrapper">
    <div class="crm-accordion-header">
      {ts}Communication{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body" style="min-height: 400px;">
      EMAIL THREAD HERE
    </div>
  </div>
  <div class="clear"></div>
{include file="CRM/Case/Form/ActivityToCase.tpl"}

{include file="CRM/Case/Form/ActivityTab.tpl"}

<div class="crm-submit-buttons">
  <!--{include file="CRM/common/formButtons.tpl" location="bottom"}-->
  {crmButton href="#" class="button-name" icon="trash"}Delete{/crmButton}
  {crmButton href="#" class="button-name" icon="flag"}Report Spam{/crmButton}
  {crmButton href="#" class="button-name" icon="check"}Resolve Case{/crmButton}
</div>
{/if} {* view related cases if end *}
</div>
{literal}
<style type="text/css">
  .crm-case-caseview-case_subject span.crm-editable {
    padding-right: 32px;
    position: relative;
  }
  .crm-case-caseview-case_subject span.crm-editable:before {
    position: absolute;
    font-family: 'FontAwesome';
    top: 0;
    right: 10px;
    content: "\f040";
    opacity: 0.7;
    color: #000;
    font-size: .92em;
  }
  .crm-case-caseview-case_subject span.crm-editable-editing {
    padding-right: 0;
  }
  .crm-case-caseview-case_subject span.crm-editable-editing form > input {
    min-width: 20em;
    padding: 3px;
  }
  .crm-case-caseview-case_subject span.crm-editable-editing:before {
    content: "";
  }
</style>
{/literal}
