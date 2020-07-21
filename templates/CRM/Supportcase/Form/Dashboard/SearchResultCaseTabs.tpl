<div class="supportcase__result-block">
    <div class="crm-content-block crm-form-block">
        <div id="supportcaseTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
            <ul class="supportcase__tabs">
                {foreach from=$cases.tabs item=tab}
                    <li class="ui-state-default ui-corner-all crm-tab-button ui-tabs-tab ui-corner-top ui-tab crm-tab-button supportcase__tab-link-wrap"
                        data-tab-name="{$tab.name}" data-case-class-selector="{$tab.case_class_selector}">
                        <span title="{ts}{$tab.title}{/ts}" class="ui-tabs-anchor supportcase__tab-link">
                            <span>{ts}{$tab.title}{/ts} [#{$tab.count_cases}]</span>
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
                {else}
                    <div class="crm-results-block crm-results-block-empty">
                        {include file="CRM/Supportcase/Form/Dashboard/EmptyResults.tpl"}
                    </div>
                {/if}
            </div>
            <div class="clear"></div>

            {if $cases.rows}
              {crmScript file='js/crm.expandRow.js'}
            {/if}
        </div>
    </div>
</div>

{literal}
    <script>
        CRM.$(function ($) {
            var allTabs = $('.supportcase__tab-link-wrap');
            var allRows = $('.supportcase__case-row');
            initTabs();
            activateTab(storageGetActiveTab());

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
                $('.' + activeTabElement.data('case-class-selector')).show();
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

        });
    </script>
{/literal}
