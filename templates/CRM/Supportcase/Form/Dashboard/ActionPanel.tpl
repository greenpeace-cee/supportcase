<div class="action-panel">
  <div class="ap__actions-wrap">
    <a class="btn btn-secondary sc__m-0" href="{crmURL p='civicrm/supportcase' q='reset=1'}" title="{ts}Clear all search criteria{/ts}">
      <i class="crm-i fa-undo"></i>
    </a>

    <a class="btn btn-secondary sc__m-0" href="{$addNewCaseUrl}" title="{ts}Add new one{/ts}" >
      <i class="crm-i fa-plus"></i>
    </a>

    <div class="spc__menu-block">
      <button type="button" class="btn btn-secondary disabled fastActionsMenuButton sc__m-0" title="{ts}Fast tasks{/ts}" >
        <i class="crm-i fa-ellipsis-v"></i>
      </button>

      <div class="spc__menu-wrap">
        <ul class="spc__menu">
          <li>
            <div class="spc__menu-item">Change category to:</div>
            <ul>
              {foreach from=$categories item=category}
                <li class="spc__menu-change-category" data-category-value="{$category.value}">
                  <div class="spc__menu-item">{$category.label}</div>
                </li>
              {/foreach}
            </ul>
          </li>
          <li>
            <div class="spc__menu-item spc__menu-change-case-status" data-status="spam">Report Spam</div>
          </li>
          <li>
            <div class="spc__menu-item spc__menu-change-case-status" data-status="Closed">Resolve Cases</div>
          </li>
          <li>
            <div class="spc__menu-item spc__menu-delete-case">Delete Cases</div>
          </li>
        </ul>
      </div>
    </div>

    <div class="spc__selected-cases spc--hide">Selected cases: <span class="selectedCaseCounter"></span></div>
  </div>

  {include file="CRM/Supportcase/Form/Dashboard/Pagination.tpl"}
</div>

{literal}
  <script>
    CRM.$(function ($) {
      initFastActionsMenu();
      initFastActionsMenuButton();
      initHandleSelectedCases();
      initChangingCategory();
      initChangeCaseStatus();
      initDeleteCase();

      function initChangingCategory() {
        $('.spc__menu-change-category').click(function () {
          CRM.api3('SupportcaseFastTask', 'change_category', {
            "case_ids": getSelectedCaseIds(),
            "category_value": $(this).data('category-value')
          }).then(function(result) {
            if (result.is_error === 1) {
              console.error('SupportcaseFastTask->change_category get server error:');
              console.error(error);
              CRM.status('Server error via changing category', 'error');
            } else {
              CRM.status(result['values']['message']);
            }
            reloadDashboard();
          }, function(error) {
            console.error('SupportcaseFastTask->change_category get server error:');
            console.error(error);
            CRM.status('Server error via changing category', 'error');
            reloadDashboard();
          });
        });
      }

      function initChangeCaseStatus() {
        $('.spc__menu-change-case-status').click(function () {
          CRM.api3('SupportcaseFastTask', 'change_status', {
            "case_ids": getSelectedCaseIds(),
            "status": $(this).data('status')
          }).then(function(result) {
            if (result.is_error === 1) {
              console.error('SupportcaseFastTask->change_status get server error:');
              console.error(error);
              CRM.status('Server error via changing status', 'error');
            } else {
              CRM.status(result['values']['message']);
            }
            reloadDashboard();
          }, function(error) {
            console.error('SupportcaseFastTask->change_status get server error:');
            console.error(error);
            CRM.status('Server error via changing status', 'error');
            reloadDashboard();
          });
        });
      }

      function initDeleteCase() {
        $('.spc__menu-delete-case').click(function () {
          CRM.confirm({
            title: 'Delete cases?',
            message: 'Are you sure you want to delete the selected cases?'
          })
            .on('crmConfirm:yes', function() {
              CRM.api3('SupportcaseFastTask', 'delete_case', {
                "case_ids": getSelectedCaseIds(),
              }).then(function(result) {
                if (result.is_error === 1) {
                  console.error('SupportcaseFastTask->delete_case get server error:');
                  console.error(error);
                  CRM.status('Server error via delete', 'error');
                } else {
                  CRM.status(result['values']['message']);
                }
                reloadDashboard();
              }, function(error) {
                console.error('SupportcaseFastTask->delete_case get server error:');
                console.error(error);
                CRM.status('Server error via delete', 'error');
                reloadDashboard();
              });
            });
        });
      }

      function initFastActionsMenu() {
        $(".spc__menu").menu({});
      }

      function reloadDashboard() {
        $('#crm-main-content-wrapper').crmSnippet().crmSnippet('refresh');
      }

      function initHandleSelectedCases() {
        $('.scd__case-select-row-checkbox input[type="checkbox"]').change(handleSelectedCases);
        $('#supportcaseToggleSelectCases').change(handleSelectedCases);
        $('.scd__tabs-item').click(handleSelectedCases);
      }

      function handleSelectedCases() {
        setTimeout(function() {
          var selectedCases = $('.scd__case-select-row-checkbox input[type="checkbox"]:checked');
          var menuButton = $(".fastActionsMenuButton");
          var menuWrap = $(".spc__menu-block");

          if (selectedCases.length > 0) {
            menuButton.removeClass('disabled');
          } else {
            menuButton.addClass('disabled');
          }

          menuButton.removeClass('spc--active');
          menuWrap.removeClass('spc--open');
          updateSelectedCasesCounter();
        }, 200);
      }

      function updateSelectedCasesCounter() {
        var selectedCases = $('.scd__case-select-row-checkbox input[type="checkbox"]:checked');
        $('.selectedCaseCounter').text(selectedCases.length);
        if (selectedCases.length > 0) {
          $('.spc__selected-cases').removeClass('spc--hide');
        } else {
          $('.spc__selected-cases').addClass('spc--hide');
        }
      }

      function initFastActionsMenuButton() {
        $(".fastActionsMenuButton").click(function() {
          var element = $(this);
          if (element.hasClass('disabled')) {
            return;
          }
          element.closest('.spc__menu-block').toggleClass('spc--open');
          element.toggleClass('spc--active');
        });
      }

      function getSelectedCaseIds() {
        var fastTasksSelect = $('.scd__case-select-row-checkbox input[type="checkbox"]:checked');

        var ids = [];
        fastTasksSelect.each(function( index ) {
          var caseId = $(this).closest('.scd__case-row').data('case-id');
          ids.push(caseId);
        });

        return ids.join(',');
      }

    });
  </script>
{/literal}
