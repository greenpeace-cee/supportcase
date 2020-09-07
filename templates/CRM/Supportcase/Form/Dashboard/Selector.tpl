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
      <th class="supportcase__result-table-head-column supportcase__result-table-first-column" scope="col" title="Select Rows">
        <input type="checkbox" id="supportcaseToggleSelectCases">
      </th>

      {foreach from=$columnHeaders item=header}
        <th class="supportcase__result-table-head-column" scope="col">
          {if $header.sort}
            {assign var='key' value=$header.sort}
            {$sort->_response.$key.link}
          {else}
            <span>{$header.name}</span>
          {/if}
        </th>
      {/foreach}
    </tr class="columnheader">

    {counter start=0 skip=1 print=false}
    {foreach from=$rows item=row}

      <tr id='rowid{$list}{$row.case_id}' data-case-id="{$row.case_id}"
        class="supportcase__case-row {' '|implode:$row.classes} {if $row.case_status == 'Urgent'}supportcase__case-row-urgent{/if} {cycle values="odd-row,even-row"} crm-case crm-case-status_{$row.case_status}">
        {assign var=cbName value=$row.checkbox}
        <td class="supportcase__case-select-column-wrap supportcase__result-table-first-column">
          <div class="supportcase__case-select-row-column">
            <div class="supportcase__case-select-row-checkbox">
              {$form.$cbName.html}
            </div>
            <div class="supportcase__case-row-icons">
              {if $row.case_status == 'Urgent'}
                <i title="This case needs urgent attention" class="supportcase__case-ico supportcase__case-ico-urgent crm-i fa-exclamation-circle" aria-hidden="true"></i>
              {/if}

              {if $row.is_case_locked}
                <i title="{$row.lock_message}" class="supportcase__case-ico crm-i supportcase__case-ico-lock {if $row.is_locked_by_self}fa-unlock{else}fa-lock{/if}" aria-hidden="true"></i>
              {/if}
            </div>
          </div>
        </td>
        <td class="supportcase__case-id-column-wrap">
          <span>#{$row.case_id}</span>
        </td>
        <td class="supportcase__view-activities-column-wrap">
          <a title="{ts}Show Activities{/ts}" class="supportcase__show-case-activity-button crm-expand-row" href="{crmURL p='civicrm/case/details' q="caseId=`$row.case_id`&cid=`$row.contact_id`"}"></a>
        </td>
        <td class="supportcase__case-client-column-wrap">
          <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}" title="{ts}View Contact Details{/ts}" target="_blank">{$row.sort_name}</a>{if $row.phone}<br /><span class="description">{$row.phone}</span>{/if}
        </td>
        <td class="supportcase__case-subject-column-wrap">
          <span>{$row.case_subject}</span>
        </td>
        <td class="{$row.class} crm-summary-row">
          <div class="supportcase__case-tags-wrap">
            {if $row.case_tags}
              <div class="crm-block crm-content-block">
                {foreach from=$row.case_tags item='tag'}
                  <div class="crm-tag-item supportcase__case-tag-item" {if !empty($tag.color)}style="background-color: {$tag.color}; color: {$tag.color|colorContrast};" {/if} title="{$tag.description|escape}">
                      {$tag.name}
                  </div>
                {/foreach}
              </div>
            {/if}
          </div>
        </td>
        <td class="{$row.class} crm-case-status_{$row.case_status}">
          <span>{$row.case_status}</span>
        </td>
        <td>
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
