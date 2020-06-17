{if $notConfigured}
  {* Case types not present. Component is not configured for use. *}
  {include file="CRM/Case/Page/ConfigureError.tpl"}
{else}
  <div class="crm-block crm-form-block crm-contribution-search-form-block">
    <div class="crm-accordion-wrapper crm-contribution_search_form-accordion">
      <div class="crm-accordion-header crm-master-accordion-header">
        {ts}Filter{/ts}
      </div>
      <div class="crm-accordion-body">
        {strip}
          <div class="supportcase__search-block">

            <div class="supportcase__search-by-case-id-wrap">
              <table class="form-layout">
                <tr>
                  <td class="crm-case-common-form-block-case_id">
                    {$form.case_id.label}<br />
                    {$form.case_id.html}
                    <a class="supportcase__search-by-case-id-clear-button crm-hover-button" title="Clear">
                      <i class="crm-i fa-times"></i>
                    </a>
                  </td>
                </tr>
              </table>
            </div>

            <div class="supportcase__search-filters-wrap">
              <table class="form-layout">
                <tr>
                  {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_start_date" colspan="1"}
                  {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_end_date"  colspan="1"}
                </tr>

                <tr>
                  <td class="crm-case-common-form-block-case_subject">
                    {$form.case_keyword.label}<br />
                    {$form.case_keyword.html}
                  </td>
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
              </table>
            </div>

            <table class="form-layout">
              <tr>
                <td colspan="2">{$form.buttons.html}</td>
              </tr>
            </table>
          </div>
        {/strip}
      </div>
    </div>
  </div>

  <div class="crm-content-block crm-block">
    {if !$rows}
      <div class="crm-results-block crm-results-block-empty">
        {include file="CRM/Supportcase/Form/Dashboard/EmptyResults.tpl"}
      </div>
    {/if}

    <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
      <ul>
        <li id="tab_all" class="ui-corner-all crm-tab-button">
          <a href="#all" title="{ts}All{/ts}">
            <span> </span> {ts}All{/ts}
            <em>&nbsp;</em>
            <em style="background: #0071bd; border-radius: 10px; min-width: 10px; color: #fff; padding: 0 5px; text-align: center; display: inline-block; margin-left: 5px;">
              {if $rows}
                {$rows|@count}
              {else}
                0
              {/if}
            </em>
          </a>
        </li>
        <li id="tab_service" class="ui-corner-all crm-tab-button">
          <a href="#service" title="{ts}Service{/ts}">
            <span> </span> {ts}Service{/ts}
            <em style="background: #0071bd; border-radius: 10px; min-width: 10px; color: #fff; padding: 0 5px; text-align: center; display: inline-block; margin-left: 5px;">
              2
            </em>
            <em style="background: red; border-radius: 10px; min-width: 10px; color: #fff; padding: 0 5px; text-align: center; display: inline-block; margin-left: 2px;">
              1
            </em>
          </a>
        </li>
        <li id="tab_community" class="ui-corner-all crm-tab-button">
          <a href="#community" title="{ts}Community{/ts}">
            <span> </span> {ts}Community{/ts}
            <em style="background: #9494a5; border-radius: 10px; min-width: 10px; color: #fff; padding: 0 5px; text-align: center; display: inline-block; margin-left: 5px;">
              0
            </em>
          </a>
        </li>
      </ul>
      <div id="all" class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="padding: 0;">
        {if $rows}
          <div class="crm-results-block">

            {* This section displays the rows along and includes the paging controls *}
            <div id="supportcaseSearch" class="crm-search-results">
              {include file="CRM/Supportcase/Form/Dashboard/Selector.tpl"}
            </div>

            {* Search request has returned 1 or more matching rows. *}
            {* This section handles form elements for action task select and submit *}
            <div class="crm-search-tasks crm-event-search-tasks" style="box-shadow: none;">
              {include file="CRM/Supportcase/Form/Dashboard/SearchResultTasks.tpl" context="Case"}
            </div>

            {* END Actions/Results section *}
          </div>
        {/if}
      </div>

      <div class="clear"></div>

    </div>
    {include file="CRM/common/TabSelected.tpl" defaultTab="all"}
  </div>

{literal}
  <script>
    CRM.$(function ($) {
      initCaseIdFieldHandler();

      function initCaseIdFieldHandler() {
        var caseIdElement = $('.supportcase__search-by-case-id-wrap input#case_id');
        var searchBlockElement = $('.supportcase__search-block');
        var clearButtonElement = $('.supportcase__search-by-case-id-clear-button');
        var handleFilersBlock = function() {
          if (caseIdElement.val().length > 0) {
            searchBlockElement.addClass('searching-by-case-id');
          } else {
            searchBlockElement.removeClass('searching-by-case-id');
          }
        };
        caseIdElement.on("change paste keyup", handleFilersBlock);
        clearButtonElement.click(function() {
          caseIdElement.val('');
          handleFilersBlock();
        });
        handleFilersBlock();
      }
    });
  </script>

  <style>
    .supportcase__search-filters-wrap {
      position: relative;
    }

    .supportcase__search-block a.supportcase__search-by-case-id-clear-button {
      display: none;
    }

    .supportcase__search-filters-wrap::after {
      background: rgba(255, 255, 255, 0.6);
      content: '';
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      display: none;
      position: absolute;
      z-index: 10;
    }

    .supportcase__search-block.searching-by-case-id .supportcase__search-filters-wrap::after {
      display: block;
    }

    .supportcase__search-block.searching-by-case-id .supportcase__search-by-case-id-clear-button {
      display: inline-block;
    }
  </style>
{/literal}
{/if}
