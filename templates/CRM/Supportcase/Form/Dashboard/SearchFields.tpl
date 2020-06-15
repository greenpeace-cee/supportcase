{*
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC. All rights reserved.                        |
 |                                                                    |
 | This work is published under the GNU AGPLv3 license with some      |
 | permitted exceptions and without any warranty. For full license    |
 | and copyright information, see https://civicrm.org/licensing       |
 +--------------------------------------------------------------------+
*}
{if $notConfigured} {* Case types not present. Component is not configured for use. *}
  {include file="CRM/Case/Page/ConfigureError.tpl"}
{else}
  <tr>
    <td class="crm-case-common-form-block-case_id">
      {$form.case_id.label}<br />
      {$form.case_id.html}
    </td>
    <td class="crm-case-common-form-block-case_subject">
      {$form.case_keyword.label}<br />
      {$form.case_keyword.html}
    </td>
    {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_start_date" colspan="1"}
    {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_end_date"  colspan="1"}
    <td class="crm-case-common-form-block-case_status_id">
      {$form.case_status_id.label}<br />
      {$form.case_status_id.html}
    </td>
  </tr>

  <tr id='case_search_form'>
    <td>
      {$form.case_agents.label}<br />
      {$form.case_agents.html}
    </td>
    <td>
      {$form.case_client.label}<br />
      {$form.case_client.html}
    </td>
    <!-- TODO: force <br /> after tag field label to match other fields instead of width hack -->
    <td style="width: 60px;">{include file="CRM/common/Tagset.tpl" tagsetType='case'}</td>
  </tr>

{/if}
