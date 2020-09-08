<div class="supportcase__dashboard-search-filters-wrap">
    <div class="crm-block crm-form-block">
        <div class="crm-accordion-wrapper crm-contribution_search_form-accordion">
            <div class="crm-accordion-header crm-master-accordion-header">
                {ts}Filter{/ts}
            </div>
            <div class="crm-accordion-body">
                {strip}
                    <div class="supportcase__search-block">

                        <div class="supportcase__search-by-case-id-wrap">
                            <table class="supportcase__search-table form-layout">
                                <tr>
                                    <td class="crm-case-common-form-block-case_id">
                                        {$form.case_id.label}<br />
                                        {$form.case_id.html}
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="supportcase__search-filters-wrap">
                            <table class="supportcase__search-table form-layout">
                                <tr>
                                    <td class="crm-case-common-form-block-case_keyword">
                                        {$form.case_keyword.label}<br />
                                        {$form.case_keyword.html}
                                    </td>
                                    <td class="crm-case-common-form-block-case_status_id">
                                        {$form.case_status_id.label}<br />
                                        {$form.case_status_id.html}
                                    </td>
                                    <td>
                                        {$form.case_agents.label}<br />
                                        {$form.case_agents.html}
                                    </td>
                                    <td>
                                        <div class="supportcase__is-case-deleted-search-filter">
                                            {$form.is_show_deleted_cases.label}<br />
                                            {$form.is_show_deleted_cases.html}
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_start_date" colspan="1"}
                                    {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_end_date"  colspan="1"}
                                    <td>
                                        {$form.case_client.label}<br />
                                        {$form.case_client.html}
                                    </td>
                                </tr>

                                {if $tagsetInfo}
                                    <tr>
                                        <td colspan="3">
                                            <div class="supportcase__case-tags-filter-wrap">
                                                <div class="crm-accordion-wrapper {if $isTagsFilterEmpty}collapsed{/if}">
                                                    <div class="crm-accordion-header">{ts}Tags:{/ts}</div>
                                                    <div class="crm-accordion-body">
                                                        <div class="supportcase__case-tags-filter">
                                                            {include file="CRM/common/Tagset.tpl" tagsetType='case'}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                {/if}
                            </table>
                        </div>

                        <div class="supportcase__search-buttons">
                            <div class="supportcase__search-button-submit">
                                {$form.buttons.html}
                            </div>
                            <div class="crm-submit-buttons reset-advanced-search">
                                <a href="{crmURL p='civicrm/supportcase' q='reset=1'}" class="crm-hover-button" title="{ts}Clear all search criteria{/ts}">
                                    <i class="crm-i fa-undo"></i>&nbsp;{ts}Reset Form{/ts}
                                </a>
                            </div>

                            <button class="supportcase__toggle-filter-button button" id="toggleFilterButton">
                              <span class="supportcase__toggle-filter-button-case-id-text">Search by Case ID</span>
                              <span class="supportcase__toggle-filter-button-params-text">Advanced Search</span>
                            </button>
                        </div>
                    </div>
                {/strip}
            </div>
        </div>
    </div>
</div>

{literal}
    <script>
        CRM.$(function ($) {
            initCaseIdFieldHandler();

            function initCaseIdFieldHandler() {
                var caseIdElement = $('.supportcase__search-by-case-id-wrap input#case_id');
                var searchBlockElement = $('.supportcase__search-block');

                $('#toggleFilterButton').click(function(e) {
                    e.preventDefault();
                    searchBlockElement.toggleClass('searching-by-case-id');
                    caseIdElement.val('');
                });

                if (caseIdElement.val().length > 0) {
                    searchBlockElement.addClass('searching-by-case-id');
                } else {
                    searchBlockElement.removeClass('searching-by-case-id');
                }
            }
        });
    </script>
{/literal}
