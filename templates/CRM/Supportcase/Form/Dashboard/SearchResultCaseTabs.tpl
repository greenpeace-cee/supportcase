<div class="scd__result-block">
  <div>

    <div id="supportcaseTabContainer" class="ui-tabs">
      <ul class="ui-tabs-nav scd__tabs">
        {foreach from=$cases.tabs item=tab}
          <li class="scd__tabs-item scd__tab-link-wrap ui-tab ui-tabs-active"
              data-tab-name="{$tab.name}" data-case-class-selector="{$tab.case_class_selector}">
            <a class="ui-tabs-anchor scd__tab-link" href="#" title="{ts}{$tab.title}{/ts}">
                <span>{ts}{$tab.title}{/ts}</span>
                <em>{$tab.count_cases}</em>
                {foreach from=$tab.extra_counters item=counter}
                  <em style="background: {$counter.color} !important; color: #fff !important;" title="{$counter.title}">
                    {$counter.count}
                  </em>
                {/foreach}
            </a>
          </li>
        {/foreach}
      </ul>

      <div class="sc__p-10">
        <div class="scd__result-wrap">
          {if $cases.rows}
            <div>
              {include file="CRM/Supportcase/Form/Dashboard/Selector.tpl" rows=$cases.rows}
              {include file="CRM/Supportcase/Form/Dashboard/Pagination.tpl"}
            </div>
          {/if}
        </div>

        <div class="scd__empty-result-wrap">
          {include file="CRM/Supportcase/Form/Dashboard/EmptyResults.tpl"}
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
    var allTabs = $('.scd__tab-link-wrap');
    var allRows = $('.scd__case-row');
    var caseEmptyResultBlock = $('.scd__empty-result-wrap');
    var caseResultBlock = $('.scd__result-wrap');
    var showCaseActivityButtons = $('.scd__show-case-activity-button');
    var caseToggleSelectButton = $('#supportcaseToggleSelectCases');

    initTabs();
    activateTab(storageGetActiveTab());
    initCaseToggleSelectButton();
    hideSelectAllCasesButton();
    initLocker();

    function initTabs() {
      allTabs.click(function () {
        var tabName = $(this).data('tab-name');
        activateTab(tabName);
        storageSetActiveTab(tabName);
      });
    }

    function activateTab(tabName) {
      var activeTabElement = $('.scd__tab-link-wrap[data-tab-name="' + tabName + '"]');
      if (activeTabElement.length === 0) {
        activeTabElement = allTabs.first();
      }

      allTabs.removeClass('ui-tabs-active');
      activeTabElement.addClass('ui-tabs-active');
      allRows.hide();
      allRows.parent().children('.crm-child-row').remove();
      showCaseActivityButtons.removeClass('expanded');
      allRows.find('.scd__case-select-column-wrap input[type="checkbox"]').prop("checked", false);
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
      var checkedVisibleRows = getVisibleRows().find('.scd__case-select-column-wrap input[type="checkbox"]:checked');
      var tasksActionSelect = $('.scd__result-action-block select#task');
      $('.scd__result-action-block input[name=radio_ts][value=ts_all]').prop('checked', false);

      if (checkedVisibleRows.length > 0) {
        tasksActionSelect.val('').select2('val', '').prop('disabled', false).select2('enable');
        $('.scd__result-action-block input[name=radio_ts][value=ts_sel]').prop('checked', true);
      } else {
        tasksActionSelect.val('').select2('val', '').prop('disabled', true).select2('disable');
        $('.scd__result-action-block input[name=radio_ts][value=ts_sel]').prop('checked', false);
      }

      $('.scd__result-action-block label[for*=ts_sel] span').text(checkedVisibleRows.length);
    }

    function getVisibleRows() {
      var activeTabElement = $('.scd__tab-link-wrap.ui-tabs-active');
      var visibleRowsSelector = activeTabElement.data('case-class-selector');

      return $(visibleRowsSelector);
    }

    function initCaseToggleSelectButton() {
      caseToggleSelectButton.change(function () {
        allRows.find('.scd__case-select-column-wrap input[type="checkbox"]').prop("checked", false);

        if (this.checked) {
          getVisibleRows().find('.scd__case-select-column-wrap input[type="checkbox"]').prop("checked", true);
        }

        handleActionTaskMenu();
      });
    }

    function hideSelectAllCasesButton() {
      if (allRows.length === 0) {
        return;
      }

      if (allRows.length > 1) {
        var selectAllInput = $('.scd__result-action-block input[name=radio_ts][value=ts_all]');
        var selectAllInputId = selectAllInput.prop('id');
        selectAllInput.prop('checked', false);
        var selectAllInputLabel = $('.scd__result-action-block label[for=' + selectAllInputId + ']');
        selectAllInput.hide();
        selectAllInputLabel.hide();
      }
    }

    function storageGetActiveTab() {
      return (window.localStorage) ? localStorage.getItem(getStorageKey()) : false;
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

      initUnlocking();
      setInterval(updateCaseLocking, lockReloadTime);
      window.isSupportCaseLockerLoaded = true;
    }

    function findCaseIds() {
      var caseIds = [];
      $('.scd__case-row ').each(function () {
        caseIds.push(this.getAttribute('data-case-id'));
      });
      return caseIds;
    }

    function updateCaseLocking() {
      var caseIds = findCaseIds();

      CRM.api3('CaseLock', 'get_locked_cases', {
        "case_ids": caseIds
      }).then(function (result) {
        if (!result.is_error) {
          $('.scd__case-row .scd__case-row-icons .scd__case-ico-lock').remove();
          result.values.map(caseLock => {
            var icoClass = (caseLock.is_locked_by_self) ? 'fa-unlock' : 'fa-lock';
            var classes = 'scd__unlock-button scd__case-ico crm-i scd__case-ico-lock ' + icoClass;
            var icoHtml = '<i title="Unlock case. ' + caseLock.lock_message + '" data-case-id="' + caseLock.case_id + '" class="' + classes + '" aria-hidden="true"></i>';
            var caseElement = $('.scd__case-row[data-case-id="' + caseLock.case_id + '"]');
            caseElement.find('.scd__case-row-icons').append(icoHtml);
            caseElement.find('.scd__case-row-icons .scd__unlock-button[data-case-id="' + caseLock.case_id + '"]').click(function () {
              unlockCase(this.getAttribute('data-case-id'));
            });
          });
        }
      }, function (error) {
      });
    }

    function initUnlocking() {
      var unlockButtons = $('.scd__unlock-button');
      if (unlockButtons.length === 0) {
        return;
      }

      unlockButtons.click(function () {
        unlockCase(this.getAttribute('data-case-id'));
      });
    }

    function unlockCase(caseId) {
      CRM.confirm({
        title: 'Unlock case',
        width: '600',
        message: 'Case with id = ' + caseId + ' will be unlocked.',
        options: {yes: "Unlock case", no: "Cancel"},
      }).on('crmConfirm:yes', function () {
        CRM.api3('CaseLock', 'unlock_case', {
          "case_id": caseId
        }).then(function (result) {
          if (result.is_error === 1) {
            console.error('unlock_case get error:');
            console.error(result.error_message);
          } else {
            CRM.status('Case id = ' + caseId + ' is unlocked.');
            $('.scd__unlock-button[data-case-id="' + caseId + '"]').remove();
          }
        }, function (error) {
        });
      });
    }

  });
</script>
{/literal}
