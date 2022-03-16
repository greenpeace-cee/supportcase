<div class="supportcase__dashboard-search-filters-wrap">
    <div class="crm-block crm-form-block">
        <div class="spc__accordion crm-accordion-wrapper crm-contribution_search_form-accordion {if $isCollapseFilter}collapsed{/if}">
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
                            <button class="spc__button spc--height-big spc--blue" value="1" type="submit" name="_qf_Dashboard_refresh" id="_qf_Dashboard_refresh">
                                <span class="ui-button-icon ui-icon fa-check"></span>
                                <span class="ui-button-icon-space"> </span>
                                <span>Search</span>
                            </button>

                            <div class="spc__button spc--height-big spc--blue supportcase__button-go-to-advanced-search supportcase__toggle-mode-button">
                                <span class="ui-button-icon ui-icon fa-search"></span>
                                <span class="ui-button-icon-space"> </span>
                                <span>Advanced Search</span>
                            </div>

                            <div class="spc__button spc--height-big spc--blue supportcase__button-go-to-case-id-search supportcase__toggle-mode-button">
                                <span class="ui-button-icon ui-icon fa-search"></span>
                                <span class="ui-button-icon-space"> </span>
                                <span>Search by Case ID</span>
                            </div>

                            <a class="spc__button spc--height-big spc--cancel" href="{crmURL p='civicrm/supportcase' q='reset=1'}" title="{ts}Clear all search criteria{/ts}" >
                                <span class="ui-button-icon ui-icon fa-undo"></span>
                                <span class="ui-button-icon-space"> </span>
                                <span>{ts}Reset Form{/ts}</span>
                            </a>
                        </div>
                    </div>
                {/strip}
            </div>
        </div>
    </div>
</div>

{include file="CRM/Supportcase/Form/Dashboard/ActionPanel.tpl"}

{literal}
    <script>
        CRM.$(function ($) {
            initCaseIdFieldHandler();

            function initCaseIdFieldHandler() {
                var caseIdElement = $('.supportcase__search-by-case-id-wrap input#case_id');
                var searchBlockElement = $('.supportcase__search-block');

                $('.supportcase__toggle-mode-button').click(function(e) {
                    e.preventDefault();
                    searchBlockElement.toggleClass('supportcase__search-by-case-id-mode');
                    caseIdElement.val('');
                });

                if (caseIdElement.val().length > 0) {
                    searchBlockElement.addClass('supportcase__search-by-case-id-mode');
                } else {
                    searchBlockElement.removeClass('supportcase__search-by-case-id-mode');
                }
            }
        });
    </script>
{/literal}
