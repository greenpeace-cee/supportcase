<div class="crm-block crm-form-block crm-contribution-search-form-block">
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
                                    <a class="supportcase__search-by-case-id-clear-button crm-hover-button" title="Clear">
                                        <i class="crm-i fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="supportcase__search-filters-wrap">
                        <table class="supportcase__search-table form-layout">
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

                    <table class="supportcase__search-table form-layout-compressed">
                        <tr>
                            <td>
                                {$form.buttons.html}
                            </td>
                            <td>
                                <div class="crm-submit-buttons reset-advanced-search">
                                    <a href="{crmURL p='civicrm/supportcase' q='reset=1'}" class="crm-hover-button" title="{ts}Clear all search criteria{/ts}">
                                        <i class="crm-i fa-undo"></i>&nbsp;{ts}Reset Form{/ts}
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            {/strip}
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
{/literal}
