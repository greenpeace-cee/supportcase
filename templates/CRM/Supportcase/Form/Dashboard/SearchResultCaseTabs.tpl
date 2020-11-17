<div class="supportcase__result-block">
    <div class="crm-content-block crm-form-block">
        <div id="supportcaseTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
            <ul class="supportcase__tabs">
                {foreach from=$cases.tabs item=tab}
                    <li class="ui-state-default ui-corner-all crm-tab-button ui-tabs-tab ui-corner-top ui-tab crm-tab-button supportcase__tab-link-wrap"
                        data-tab-name="{$tab.name}" data-case-class-selector="{$tab.case_class_selector}">
                        <span title="{ts}{$tab.title}{/ts}" class="ui-tabs-anchor supportcase__tab-link">
                            <span>{ts}{$tab.title}{/ts} [{$tab.count_cases}]</span>
                            {foreach from=$tab.extra_counters item=counter}
                                <em class="supportcase__counter" style="background: {$counter.color};" title="{$counter.title}">
                                    {$counter.count}
                                </em>
                            {/foreach}
                        </span>
                    </li>
                {/foreach}
            </ul>

            <div class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="padding: 0;">

                <div class="supportcase__result-wrap">
                  {if $cases.rows}
                    <div class="crm-results-block">
                      <div class="crm-search-results">
                          {include file="CRM/Supportcase/Form/Dashboard/Selector.tpl" rows=$cases.rows}
                      </div>
                      <div class="supportcase__result-action-block">
                        <div class="crm-search-tasks crm-event-search-tasks" style="box-shadow: none;">
                            {include file="CRM/Supportcase/Form/Dashboard/SearchResultTasks.tpl" context="Case" rows=$cases.rows}
                        </div>
                      </div>
                    </div>
                  {/if}
                </div>

                <div class="supportcase__empty-result-wrap">
                  <div class="crm-results-block crm-results-block-empty">
                      {include file="CRM/Supportcase/Form/Dashboard/EmptyResults.tpl"}
                  </div>
                </div>

                <div class="clear"></div>

                {if $cases.rows}
                  {crmScript file='js/crm.expandRow.js'}
                {/if}
            </div>
        </div>
    </div>
</div>

{literal}
    <script>
        CRM.$(function ($) {
            var lockReloadTime = '{/literal}{$lockReloadTimeInSek}{literal}' * 1000;
            var allTabs = $('.supportcase__tab-link-wrap');
            var allRows = $('.supportcase__case-row');
            var caseEmptyResultBlock = $('.supportcase__empty-result-wrap');
            var caseResultBlock = $('.supportcase__result-wrap');
            var showCaseActivityButtons = $('.supportcase__show-case-activity-button');
            var caseToggleSelectButton = $('#supportcaseToggleSelectCases');

            initUpdatingPageAfterClosePopupAngularPage();
            initTabs();
            activateTab(storageGetActiveTab());
            initCaseToggleSelectButton();
            hideSelectAllCasesButton();
            initLocker();

            function initUpdatingPageAfterClosePopupAngularPage() {
                //hack to prevent double initialization
                if (window.isDialogcloseExtended === true) {
                    return;
                }

                $(document).on('dialogclose', function(e) {
                    if (window.isCrurrentPopupAngularManageCase) {
                      $('#crm-main-content-wrapper').crmSnippet().crmSnippet('refresh');
                    }
                });

                $(document).on('dialogopen', function(e) {
                    setTimeout(function () {
                        var supportCaseAngularJsWrap = $('.support-case-angular-js-wrap');
                        window.isCrurrentPopupAngularManageCase = (supportCaseAngularJsWrap.length >= 1);
                    }, 500);

                    var dialog = $(e.target);
                    dialog.dialog('option', {
                        width: '90%',
                        height: '600',
                        resizable: false,
                    });
                    dialog.trigger('dialogresize');
                });

                window.isDialogcloseExtended = true;
            }

            function initTabs() {
                allTabs.click(function() {
                    var tabName = $(this).data('tab-name');
                    activateTab(tabName);
                    storageSetActiveTab(tabName);
                });
            }

            function activateTab(tabName) {
                var activeTabElement = $('.supportcase__tab-link-wrap[data-tab-name="' + tabName + '"]');
                if (activeTabElement.length === 0) {
                    activeTabElement = allTabs.first();
                }

                allTabs.removeClass('ui-tabs-active').removeClass('ui-state-active');
                activeTabElement.addClass('ui-tabs-active').addClass('ui-state-active');
                allRows.hide();
                allRows.parent().children('.crm-child-row').remove();
                showCaseActivityButtons.removeClass('expanded');
                allRows.find('.supportcase__case-select-column-wrap input[type="checkbox"]').prop("checked", false);
                caseToggleSelectButton.prop("checked", false);

                var visibleRows = getVisibleRows();
                if (visibleRows.length > 0) {
                    caseEmptyResultBlock.hide();
                    caseResultBlock.show();
                    visibleRows.show();
                } else {
                    caseEmptyResultBlock.show();
                    caseResultBlock.hide();
                }

                handleActionTaskMenu();
            }

            function handleActionTaskMenu() {
                var checkedVisibleRows = getVisibleRows().find('.supportcase__case-select-column-wrap input[type="checkbox"]:checked');
                var tasksActionSelect = $('.supportcase__result-action-block select#task');
                $('.supportcase__result-action-block input[name=radio_ts][value=ts_all]').prop('checked', false);

                if (checkedVisibleRows.length > 0) {
                    tasksActionSelect.val('').select2('val', '').prop('disabled', false).select2('enable');
                    $('.supportcase__result-action-block input[name=radio_ts][value=ts_sel]').prop('checked', true);
                } else {
                    tasksActionSelect.val('').select2('val', '').prop('disabled', true).select2('disable');
                    $('.supportcase__result-action-block input[name=radio_ts][value=ts_sel]').prop('checked', false);
                }

                $('.supportcase__result-action-block label[for*=ts_sel] span').text(checkedVisibleRows.length);
            }

            function getVisibleRows() {
                var activeTabElement = $('.supportcase__tab-link-wrap.ui-state-active');
                var visibleRowsSelector = activeTabElement.data('case-class-selector');

                return $(visibleRowsSelector);
            }

            function initCaseToggleSelectButton() {
                caseToggleSelectButton.change(function() {
                    allRows.find('.supportcase__case-select-column-wrap input[type="checkbox"]').prop("checked", false);

                    if (this.checked) {
                        getVisibleRows().find('.supportcase__case-select-column-wrap input[type="checkbox"]').prop("checked", true);
                    }

                    handleActionTaskMenu();
                });
            }

            function hideSelectAllCasesButton() {
                if (allRows.length  === 0) {
                    return;
                }

                if (allRows.length > 1) {
                    var selectAllInput = $('.supportcase__result-action-block input[name=radio_ts][value=ts_all]');
                    var selectAllInputId = selectAllInput.prop('id');
                    selectAllInput.prop('checked', false);
                    var selectAllInputLabel = $('.supportcase__result-action-block label[for=' + selectAllInputId + ']');
                    selectAllInput.hide();
                    selectAllInputLabel.hide();
                }
            }

            function storageGetActiveTab() {
                return (window.localStorage) ? localStorage.getItem(getStorageKey()): false;
            }

            function storageSetActiveTab(tabName) {
                if (window.localStorage) {
                    localStorage.setItem(getStorageKey(), tabName);
                }
            }

            function getStorageKey() {
                var currentContactId = '{/literal}{$currentContactId}{literal}';
                return 'supportcase_search_form_active_tab_contact_id_' + currentContactId;
            }

            function initLocker() {
                //hack to prevent double initialization
                if (window.isSupportCaseLockerLoaded === true) {
                    return;
                }

                setInterval(updateCaseLocking, lockReloadTime);
                window.isSupportCaseLockerLoaded = true;
            }

            function findCaseIds() {
                var caseIds = [];
                $('.supportcase__case-row ').each(function() {
                    caseIds.push(this.getAttribute('data-case-id'));
                });
                return caseIds;
            }

            function updateCaseLocking() {
                var caseIds = findCaseIds();

                CRM.api3('CaseLock', 'get_locked_cases', {
                    "case_ids": caseIds
                }).then(function(result) {
                    if (!result.is_error) {
                        $('.supportcase__case-row .supportcase__case-row-icons .supportcase__case-ico-lock').remove();
                        result.values.map(caseLock => {
                            var icoClass = (caseLock.is_locked_by_self) ? 'fa-unlock' : 'fa-lock';
                            var icoHtml = '<i title="' + caseLock.lock_message + '" class="supportcase__case-ico crm-i supportcase__case-ico-lock ' + icoClass + '" aria-hidden="true"></i>';
                            var caseElement = $('.supportcase__case-row[data-case-id="' + caseLock.case_id + '"]');
                            caseElement.find('.supportcase__case-row-icons').append(icoHtml);
                        });
                    }
                }, function(error) {});
            }

        });
    </script>
{/literal}
