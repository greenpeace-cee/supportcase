<div class="supportcase__result-block">
    <div class="crm-content-block crm-form-block">
        <div id="mainTabContainer" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
            <ul>
                {foreach from=$caseTabs item=tab}
                    <li id="tab_head_{$tab.html_id}" class="ui-corner-all crm-tab-button">
                        <a href="#tab_body_{$tab.html_id}" title="{ts}{$tab.title}{/ts}">
                            <span>{ts}{$tab.title}{/ts} [#{$tab.count_cases}]</span>
                            {foreach from=$tab.extra_counters item=counter}
                                <em class="supportcase__counter" style="background: {$counter.color};" title="{$counter.title}">
                                    {$counter.count}
                                </em>
                            {/foreach}
                        </a>
                    </li>
                {/foreach}
            </ul>

            {foreach from=$caseTabs item=tab}
                <div id="tab_body_{$tab.html_id}" class="ui-tabs-panel ui-widget-content ui-corner-bottom" style="padding: 0;">
                    {if $tab.count_cases > 0}
                        <div class="crm-results-block">
                            <div class="crm-search-results">
                                {include file="CRM/Supportcase/Form/Dashboard/Selector.tpl" rows=$tab.cases}
                            </div>
                            <div class="supportcase__result-action-block">
                                <div class="crm-search-tasks crm-event-search-tasks" style="box-shadow: none;">
                                      {include file="CRM/Supportcase/Form/Dashboard/SearchResultTasks.tpl" context="Case" rows=$tab.cases}
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
            {/foreach}

        </div>
        {include file="CRM/common/TabSelected.tpl"}
    </div>
</div>

{literal}
    <script>
        CRM.$(function ($) {
            handleActiveTab();

            function handleActiveTab() {
                var mainTabContainer = $('#mainTabContainer');
                mainTabContainer.tabs({active: getActiveTabIndex()});

                mainTabContainer.click(function() {
                    var currentTabIndex = mainTabContainer.tabs('option', 'active');
                    setActiveTabIndex(currentTabIndex);
                });
            }

            function getActiveTabIndex() {
                return (window.localStorage) ? localStorage.getItem(getStorageKey()): 0;
            }

            function setActiveTabIndex(tabIndex) {
                if (window.localStorage) {
                    localStorage.setItem(getStorageKey(), tabIndex);
                }
            }

            function getStorageKey() {
                var currentContactId = '{/literal}{$currentContactId}{literal}';
                return 'supportcase_search_form_active_tab_contact_id_' + currentContactId;
            }

        });
    </script>
{/literal}
