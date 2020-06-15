<div class="crm-block crm-form-block crm-contribution-search-form-block">
  <div class="crm-accordion-wrapper crm-contribution_search_form-accordion">
    <div class="crm-accordion-header crm-master-accordion-header">
      {ts}Filter{/ts}
    </div><!-- /.crm-accordion-header -->
    <div class="crm-accordion-body">
      {strip}
        <table class="form-layout">
          {include file="CRM/Supportcase/Form/Dashboard/SearchFields.tpl"}
          <tr>
            <td colspan="2">{$form.buttons.html}</td>
          </tr>
        </table>
      {/strip}
    </div><!-- /.crm-accordion-body -->
  </div><!-- /.crm-accordion-wrapper -->
</div><!-- /.crm-form-block -->
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

