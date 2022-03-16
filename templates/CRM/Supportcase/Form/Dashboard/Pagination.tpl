<div class="spc__pagination">
  {if $isShowPagination}
    {if $itemsPerPage}
      <div class="spc__pagination-items-per-page">Items per page: {$itemsPerPage}</div>
    {/if}
    {include file="CRM/common/pager.tpl" location="bottom"}
  {/if}
</div>
