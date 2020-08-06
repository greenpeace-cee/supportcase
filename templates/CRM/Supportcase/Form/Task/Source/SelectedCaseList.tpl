<div class="supportcase__task-selected-cases-wrap">
  <h5>{ts}Selected cases:{/ts}</h5>
  <table class="row-highlight">
    {foreach from=$cases item=case}
      <tr>
        <td>
          <span class="supportcase__task-selected-cases-subject"><i>(id = {$case.id})</i> {$case.subject}</span>
        </td>
      </tr>
    {/foreach}
  </table>
</div>
