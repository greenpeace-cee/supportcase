{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{strip}
  <div class="supportcase__result-table-wrap">
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

        <tr id='rowid{$row.case_id}' data-case-id="{$row.case_id}"
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

                {if $row.case_status == 'Resolved'}
                  <i title="This case has been resolved" class="supportcase__case-ico supportcase__case-ico-resolved crm-i fa-check-square" aria-hidden="true"></i>
                {/if}

                {if $row.case_status == 'Forwarded'}
                  <i title="This case was forwarded" class="supportcase__case-ico supportcase__case-ico-forwarded crm-i fa-share" aria-hidden="true"></i>
                {/if}

                {if $row.case_status == 'Spam'}
                  <i title="This case was flagged as Spam" class="supportcase__case-ico supportcase__case-ico-spam crm-i fa-flag" aria-hidden="true"></i>
                {/if}

                {if $row.is_case_deleted}
                  <i title="This case in the trash" class="supportcase__case-ico supportcase__case-ico-grey crm-i fa-trash" aria-hidden="true"></i>
                {/if}

                {if $row.is_case_locked}
                  <i title="Unlock case. {$row.lock_message}" data-case-id="{$row.case_id}" class="supportcase__case-ico crm-i supportcase__unlock-button supportcase__case-ico-lock {if $row.is_locked_by_self}fa-unlock{else}fa-lock{/if}" aria-hidden="true"></i>
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
            <div class="supportcase__case-contact-item">
              <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}" title="{ts}View Contact Details{/ts}" target="_blank">
                <span class="supportcase__case-contact-item-line" >{$row.sort_name}</span>
              </a>
              <span class="supportcase__case-contact-item-line" >
                {if $row.phone}
                  <span class="description">{$row.phone}</span>
                {/if}
              </span>
            </div>
          </td>
          <td class="supportcase__case-subject-column-wrap">
            <span>{$row.case_subject}</span>
          </td>
          <td class="supportcase__recent-communication-column-wrap">
            {if $row.case_recent_activity_id}
              <a href="{crmURL p='civicrm/supportcase/tooltip-activity-view' q="id=`$row.case_recent_activity_id`&snippet=4"}" class="crm-summary-link">
                <blockquote class="supportcase__recent-communication-details">
                  {$row.case_recent_activity_details|strip_tags:false|truncate:255|purify}
                </blockquote>
                <cite class="supportcase__recent-communication-meta">{$row.case_recent_activity_type_label} on {$row.case_recent_activity_date|crmDate}</cite>
              </a>
            {else}
              ---
            {/if}
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
          <td class="supportcase__case-status-column-wrap {$row.class} crm-case-status_{$row.case_status}">
            <span>{$row.case_status}</span>
            {foreach from=$row.case_manager_contacts item='manager_contact'}
              <div class="supportcase__case-contact-item">
                <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$manager_contact.id`"}" title="{ts}View Contact Details{/ts}" target="_blank">
                  <span class="supportcase__case-contact-item-line" >{$manager_contact.sort_name}</span>
                </a>
              </div>
            {/foreach}
          </td>
          <td class="supportcase__actions-column-wrap">
            {if $dashboardSearchQfKey}
                {$row.action|replace:'%%qfKey%%':$dashboardSearchQfKey|replace:'xx':$row.case_id|replace:'action-item crm-hover-button manage-case not-popup':'crm-hover-button manage-case'}
            {else}
                {$row.action|replace:'/%%qfKey%%':''|replace:'xx':$row.case_id|replace:'action-item crm-hover-button manage-case not-popup':'crm-hover-button manage-case'}
            {/if}

            {if !empty($row.moreActions)}
                {$row.moreActions|replace:'xx':$row.case_id}
            {/if}
          </td>
        {/foreach}
    </table>
  </div>
{/strip}
