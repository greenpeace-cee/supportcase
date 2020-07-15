<div id="search-status">
  <table class="form-layout-compressed">
    <tr>
      <td class="font-size11pt"> {ts}Select Records{/ts}:</td>
      <td class="nowrap">
        {$form.radio_ts.ts_all.html} <label for="{$ts_all_id}">{ts count=$pager->_totalItems plural='All %count records'}The found record{/ts}</label> &nbsp; {if $pager->_totalItems > 1} {$form.radio_ts.ts_sel.html} <label for="{$ts_sel_id}">{ts 1="<span></span>"}%1 Selected records only{/ts}</label>{/if}
      </td>
    </tr>
    <tr>
      <td colspan="2">
        <span id='task-section'>
        {$form.task.html}
          {if $actionButtonName}
            {$form.$actionButtonName.html}
          {else}
            {$form._qf_Search_next_action.html}
          {/if}
      </span>
      </td>
    </tr>
  </table>
</div>
