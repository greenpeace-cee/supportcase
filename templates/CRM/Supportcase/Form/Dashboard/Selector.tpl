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
  <div class="scd__result-table-wrap">
    <table class="dataTable sc__m-0">
      <tr>
        <th class="scd__result-table-head-column scd__result-table-first-column" scope="col" title="Select Rows">
          <input type="checkbox" id="supportcaseToggleSelectCases">
        </th>

        {foreach from=$columnHeaders item=header}
          <th class="scd__result-table-head-column" scope="col">
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
          class="scd__case-row {' '|implode:$row.classes} {if $row.case_status == 'Urgent'}scd__case-row-urgent{/if} {cycle values="odd-row,even-row"} crm-case crm-case-status_{$row.case_status}">
        {assign var=cbName value=$row.checkbox}
        <td class="scd__case-select-column-wrap scd__result-table-first-column">
          <div class="scd__case-select-row-column">
            <div class="scd__case-select-row-checkbox">
              {$form.$cbName.html}
            </div>
            <div class="scd__case-row-icons">
              {if $row.case_status == 'Urgent'}
                <i title="This case needs urgent attention" class="scd__case-ico scd__case-ico-urgent crm-i fa-exclamation-circle" aria-hidden="true"></i>
              {/if}

              {if $row.case_status == 'Resolved'}
                <i title="This case has been resolved" class="scd__case-ico scd__case-ico-resolved crm-i fa-check-square" aria-hidden="true"></i>
              {/if}

              {if $row.case_status == 'Forwarded'}
                <i title="This case was forwarded" class="scd__case-ico scd__case-ico-forwarded crm-i fa-share" aria-hidden="true"></i>
              {/if}

              {if $row.case_status == 'Spam'}
                <i title="This case was flagged as Spam" class="scd__case-ico scd__case-ico-spam crm-i fa-flag" aria-hidden="true"></i>
              {/if}

              {if $row.is_case_deleted}
                <i title="This case in the trash" class="scd__case-ico scd__case-ico-grey crm-i fa-trash" aria-hidden="true"></i>
              {/if}

              {if $row.is_case_locked}
                <i title="Unlock case. {$row.lock_message}" data-case-id="{$row.case_id}" class="sc__cursor-pointer scd__case-ico crm-i scd__unlock-button scd__case-ico-lock {if $row.is_locked_by_self}fa-unlock{else}fa-lock{/if}" aria-hidden="true"></i>
              {/if}
            </div>
          </div>
        </td>
        <td class="scd__case-id-column-wrap">
          <span>#{$row.case_id}</span>
        </td>
        <td class="scd__view-activities-column-wrap">
          <a title="{ts}Show Activities{/ts}" class="scd__show-case-activity-button crm-expand-row" href="{crmURL p='civicrm/case/details' q="caseId=`$row.case_id`&cid=`$row.contact_id`"}"></a>
        </td>
        <td class="scd__case-client-column-wrap">
          <div class="scd__case-contact-item">
            <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$row.contact_id`"}" title="{ts}View Contact Details{/ts}" target="_blank">
              <span class="scd__case-contact-item-line" >{$row.sort_name}</span>
            </a>
            <span class="scd__case-contact-item-line" >
                {if $row.phone}
                  <span class="description">{$row.phone}</span>
                {/if}
              </span>
          </div>
        </td>
        <td class="scd__case-subject-column-wrap">
          <span>{$row.case_subject}</span>
        </td>
        <td class="scd__recent-communication-column-wrap">
          {if $row.case_recent_activity_id}
            <a href="{crmURL p='civicrm/supportcase/tooltip-activity-view' q="id=`$row.case_recent_activity_id`&snippet=4"}" class="crm-summary-link">
              <blockquote class="scd__recent-communication-details">
                {$row.case_recent_activity_details|strip_tags:false|truncate:255|purify}
              </blockquote>
              <cite class="scd__recent-communication-meta">{$row.case_recent_activity_type_label} on {$row.case_recent_activity_date|crmDate}</cite>
            </a>
          {else}
            ---
          {/if}
        </td>
        <td class="{$row.class} crm-summary-row">
          <div>
            {if $row.case_tags}
              <div class="crm-block crm-content-block sc__flex sc__gap-5 sc__max-width-250">
                {foreach from=$row.case_tags item='tag'}
                  <div class="crm-tag-item sc__tag" {if !empty($tag.color)}style="background-color: {$tag.color}; color: {$tag.color|colorContrast};" {/if} title="{$tag.description|escape}">
                    {$tag.name}
                  </div>
                {/foreach}
              </div>
            {/if}
          </div>
        </td>
        <td class="scd__case-status-column-wrap {$row.class} crm-case-status_{$row.case_status}">
          <span>{$row.case_status}</span>
          {foreach from=$row.case_manager_contacts item='manager_contact'}
            <div class="scd__case-contact-item">
              <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$manager_contact.id`"}" title="{ts}View Contact Details{/ts}" target="_blank">
                <span class="scd__case-contact-item-line" >{$manager_contact.sort_name}</span>
              </a>
            </div>
          {/foreach}
        </td>
        <td>
          <div class="sc__flex sc__gap-5 sc__align-items-center sc__justify-content-right">
            {if $row.is_case_deleted}
              {if $row.action_permissions.restore}
                <a href="{$row.restore_case_link}" class="btn btn-success sc__green-btn sc__m-0 crm-popup medium-popup">
                  <i class="crm-i fa-trash-restore"></i>
                  <span>Restore</span>
                </a>
              {/if}
            {else}
              {if $row.action_permissions.manage}
                <a href="{$row.manage_case_link}" class="btn btn-primary sc__m-0 no-popup">
                  <i class="crm-i fa-pencil"></i>
                  <span>Manage</span>
                </a>
              {/if}
              {if $row.action_permissions.spam}
                <a href="{$row.report_spam_link}" class="btn btn-warning sc__m-0 crm-popup medium-popup" >
                  <i class="crm-i fa-flag"></i>
                  <span>Spam</span>
                </a>
              {/if}
              {if $row.action_permissions.delete}
                <a href="{$row.delete_case_link}" class="btn btn-danger sc__m-0 crm-popup medium-popup">
                  <i class="crm-i fa-trash"></i>
                  <span>Delete</span>
                </a>
              {/if}
            {/if}
          </div>
        </td>
        {/foreach}
    </table>
  </div>
{/strip}
