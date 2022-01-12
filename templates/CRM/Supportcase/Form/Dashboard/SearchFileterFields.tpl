<div class="supportcase__dashboard-search-filters-wrap">
    <div class="crm-block crm-form-block">
        <div class="spc__accordion crm-accordion-wrapper crm-contribution_search_form-accordion">
            <div class="crm-accordion-header crm-master-accordion-header">
                {ts}Filter{/ts}
            </div>
            <div class="crm-accordion-body">
                {strip}
                    <div class="supportcase__search-block">

                        <div class="supportcase__search-by-case-id-wrap">
                            <div class="supportcase__search-item-row">
                                <div class="supportcase__search-item">
                                    {$form.case_id.label}<br />
                                    {$form.case_id.html}
                                </div>
                            </div>
                        </div>

                        <div class="supportcase__search-filters-wrap">
                            <div class="supportcase__search-item-row">
                                <div class="supportcase__search-item">
                                    {$form.case_keyword.label}<br />
                                    {$form.case_keyword.html}
                                </div>
                                <div class="supportcase__search-item">
                                    {$form.case_status_id.label}<br />
                                    {$form.case_status_id.html}
                                </div>
                                <div class="supportcase__search-item">
                                    {$form.case_agents.label}<br />
                                    {$form.case_agents.html}
                                </div>
                                <div class="supportcase__search-item supportcase--column-150-width supportcase__is-case-deleted-search-filter">
                                    {$form.is_show_deleted_cases.label}<br />
                                    {$form.is_show_deleted_cases.html}
                                </div>
                            </div>

                            <div class="supportcase__search-item-row">
                                <div class="supportcase__search-item">
                                    <table class="spc__table spc--no-border supportcase__search-table form-layout">
                                        <tr class="spc--no-border">
                                            {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_start_date" colspan="1"}
                                        </tr>
                                    </table>
                                </div>

                                <div class="supportcase__search-item">
                                    <table class="spc__table spc--no-border supportcase__search-table form-layout">
                                        <tr class="spc--no-border">
                                            {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName="case_end_date"  colspan="1"}
                                        </tr>
                                    </table>
                                </div>

                                <div class="supportcase__search-item">
                                    {$form.case_client.label}<br />
                                    {$form.case_client.html}
                                </div>
                            </div>

                            <div class="supportcase__search-item-row">
                                {if $tagsetInfo}
                                    <div class="supportcase__search-item supportcase--column-full-width">
                                        <div class="supportcase__case-tags-filter-wrap">
                                            <div class="spc__accordion spc--header-grey crm-accordion-wrapper {if $isTagsFilterEmpty}collapsed{/if}">
                                                <div class="crm-accordion-header">{ts}Tags:{/ts}</div>
                                                <div class="crm-accordion-body">
                                                    <div class="supportcase__case-tags-filter">
                                                        {include file="CRM/common/Tagset.tpl" tagsetType='case'}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        <div class="supportcase__search-buttons">
                            <div class="supportcase__search-button-submit">
                                {$form.buttons.html}
                            </div>

                            <button class="spc__button spc--height-big supportcase__toggle-filter-button button" id="toggleFilterButton">
                              <span class="supportcase__toggle-filter-button-case-id-text">Search by Case ID</span>
                              <span class="supportcase__toggle-filter-button-params-text">Advanced Search</span>
                            </button>

                            <div class="crm-submit-buttons reset-advanced-search">
                                <a href="{crmURL p='civicrm/supportcase' q='reset=1'}" class="spc__button spc--height-big spc--cancel" title="{ts}Clear all search criteria{/ts}">
                                    <i class="crm-i fa-undo"></i>&nbsp;{ts}Reset Form{/ts}
                                </a>
                            </div>

                            <a href="{crmURL p='civicrm/supportcase/add-case' q='reset=1'}" title="{ts}Add new one{/ts}" class=" crm-popup">
                                <button class="spc__button spc--height-big">
                                    <span class="ui-button-icon ui-icon fa-plus"></span>
                                    <span class="ui-button-icon-space"> </span>
                                    <span>Add Case</span>
                                </button>
                            </a>
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
