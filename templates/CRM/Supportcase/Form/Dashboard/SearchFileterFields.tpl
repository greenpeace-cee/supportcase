<div class="sc__mb-10">
  <div class="crm-form-block">
    <div>
      <details class="crm-accordion-bold" {if !$isTagsFilterEmpty}open{/if}>
        <summary>{ts}Filter{/ts}</summary>
        <div class="crm-accordion-body sc__accordion-body">
          <div class="crm-accordion-body sc__accordion-body">
            {strip}
              <div class="scd__search-block">

                <div class="scd__search-by-case-id-wrap">
                  <div class="sc__flex sc__gap-20">
                    <div class="scd__search-item">
                      {$form.case_id.label}
                      {$form.case_id.html}
                    </div>
                  </div>
                </div>

                <div class="scd__search-filters-wrap">
                  <div class="sc__flex sc__gap-20">
                    <div class="scd__search-item">
                      {$form.case_keyword.label}
                      {$form.case_keyword.html}
                    </div>
                    <div class="scd__search-item">
                      {$form.case_status_id.label}
                      {$form.case_status_id.html}
                    </div>

                    <div class="scd__search-item scd__checkbox-search-item">
                      {$form.is_show_deleted_cases.label}
                      {$form.is_show_deleted_cases.html}
                    </div>
                  </div>

                  <div class="sc__flex sc__gap-20">
                    <div class="scd__search-item">
                      {$form.case_agents.label}
                      {$form.case_agents.html}
                    </div>
                    <div class="scd__search-item">
                      {$form.case_client.label}
                      {$form.case_client.html}
                    </div>
                  </div>

                  <div class="sc__flex sc__gap-20 sc__mb-20">
                    <div class="scd__search-item">
                      {* Uses table tag to prevent issues at DatePickerRangeWrapper.tpl*}
                      <table class="scd__date-picker-fix-table">
                        <tr>
                          {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_start_date" colspan="1"}
                        </tr>
                      </table>
                    </div>

                    <div class="scd__search-item">
                      {* Uses table tag to prevent issues at DatePickerRangeWrapper.tpl*}
                      <table class="scd__date-picker-fix-table">
                        <tr>
                          {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_end_date"  colspan="1"}
                        </tr>
                      </table>
                    </div>
                  </div>

                  {if $tagsetInfo}
                    <div class="sc__max-width-620">
                      <details class="crm-accordion-light {if $isTagsFilterEmpty}collapsed{/if}">
                        <summary>{ts}Tags:{/ts}</summary>
                        <div class="crm-accordion-body sc__accordion-body">
                          <div>
                            {include file="CRM/common/Tagset.tpl" tagsetType='case'}
                          </div>
                        </div>
                      </details>
                    </div>
                  {/if}
                </div>

                <div class="sc__flex sc__gap-10 sc__mt-20">
                  <button class="btn btn-primary" value="1" type="submit" name="_qf_Dashboard_refresh" id="_qf_Dashboard_refresh">
                    <i class="crm-i fa-check"></i>
                    <span>{ts}Search{/ts}</span>
                  </button>

                  <div class="scd__button-go-to-advanced-search">
                    <button type="button" class="btn btn-primary scd__toggle-mode-button">
                      <i class="crm-i fa-search"></i>
                      <span>{ts}Advanced Search{/ts}</span>
                    </button>
                  </div>

                  <div class="scd__button-go-to-case-id-search">
                    <button type="button" class="btn btn-primary scd__toggle-mode-button">
                      <i class="crm-i fa-search"></i>
                      <span>Search by Case ID</span>
                    </button>
                  </div>

                  <a class="btn btn-secondary" href="{crmURL p='civicrm/supportcase' q='reset=1'}" title="{ts}Clear all search criteria{/ts}" >
                    <i class="crm-i fa-undo"></i>
                    <span>{ts}Reset Form{/ts}</span>
                  </a>
                </div>
              </div>
            {/strip}
          </div>
        </div>
      </details>
    </div>

    {include file="CRM/Supportcase/Form/Dashboard/ActionPanel.tpl"}
  </div>
</div>

{literal}
  <script>
    CRM.$(function ($) {
      initCaseIdFieldHandler();

      function initCaseIdFieldHandler() {
        var caseIdElement = $('.scd__search-by-case-id-wrap input#case_id');
        var searchBlockElement = $('.scd__search-block');

        $('.scd__toggle-mode-button').click(function(e) {
          e.preventDefault();
          searchBlockElement.toggleClass('scd__search-by-case-id-mode');
          caseIdElement.val('');
        });

        if (caseIdElement.val().length > 0) {
          searchBlockElement.addClass('scd__search-by-case-id-mode');
        } else {
          searchBlockElement.removeClass('scd__search-by-case-id-mode');
        }
      }
    });
  </script>
{/literal}
