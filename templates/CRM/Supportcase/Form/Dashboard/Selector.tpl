{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{include file="CRM/common/pager.tpl" location="top"}
{strip}
  <table class="dataTable">
    <tr>

      <th scope="col" title="Select Rows">{$form.toggleSelect.html}</th>

      {foreach from=$columnHeaders item=header}
        <th scope="col">
          {if $header.sort}
            {assign var='key' value=$header.sort}
            {$sort->_response.$key.link}
          {else}
            {$header.name}
          {/if}
        </th>
      {/foreach}
    </tr class="columnheader">

    {counter start=0 skip=1 print=false}
    {foreach from=$rows item=row}

    <tr id='rowid{$list}{$row.case_id}' class="{cycle values="odd-row,even-row"} crm-case crm-case-status_{$row.case_status_id} crm-case-type_{$row.case_type_id}">
      {assign var=cbName value=$row.checkbox}
      <td style="width: 50px; font-weight: 600; font-size: 16px;">
        {$form.$cbName.html}
        {if $row.case_status == 'Urgent'}
          <i title="This case needs urgent attention" class="crm-i fa-exclamation-circle" aria-hidden="true" style="color: red; padding-left: 5px; padding-right: 5px;"></i>
        {/if}
        <!-- TODO: replace with lock implementation -->
        {if $row.case_id == 5}
          <i title="This case is currently locked by Patrick Figel" class="crm-i fa-lock" aria-hidden="true" style="color: blue; padding-left: 5px; padding-right: 5px;"></i>
        {/if}
      </td>
      <td class="crm-case-id crm-case-id_{$row.case_id}" style="font-weight: 600; font-size: 15px; width: 70px;">
        #{$row.case_id}
      </td>
      <td class="crm-case-id crm-case-id_{$row.case_id}" style="width: 30px;">
        <a title="{ts}Activities{/ts}" class="crm-expand-row" href="{crmURL p='civicrm/case/details' q="caseId=`$row.case_id`&cid=`$row.contact_id`"}"></a>
      </td>
      <td class="crm-case-id crm-case-id_{$row.case_id}" style="width: 250px;">
        <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}" title="{ts}View Contact Details{/ts}" target="_blank">{$row.sort_name}</a>{if $row.phone}<br /><span class="description">{$row.phone}</span>{/if}
      </td>
      <td class="crm-case-subject" style="width: 300px;">
        {$row.case_subject}
      </td>
      <td class="{$row.class} crm-case-status_{$row.case_status}">
        {$row.case_status}
      </td>
      <td class="crm-case-case_recent_activity_type">
        {if $row.case_recent_activity_type}
          <a href="{crmURL p='civicrm/supportcase/tooltip-activity-view' q="id=`$row.case_recent_activity_id`&snippet=4"}" class="crm-summary-link">
            <div>
              {$row.case_recent_activity_type} on {$row.case_recent_activity_date|crmDate}
            </div>
          </a>
        {else}
          ---
        {/if}
      </td>
      <td>{$row.action|replace:'xx':$row.case_id}{$row.moreActions|replace:'xx':$row.case_id}</td>
      {/foreach}
  </table>
{/strip}

{include file="CRM/common/pager.tpl" location="bottom"}
{crmScript file='js/crm.expandRow.js'}
