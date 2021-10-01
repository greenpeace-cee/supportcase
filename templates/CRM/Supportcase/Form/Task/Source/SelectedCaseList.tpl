<div class="supportcase__task-selected-cases-wrap">
  <h5>{ts}Selected cases:{/ts}</h5>
  <table class="spc__table">
    <thead>
      <tr>
        <th>id</th>
        <th>Subject</th>
      </tr>
    </thead>
    {foreach from=$cases item=case}
      <tbody>
        <tr class="spc--border-bottom">
          <td class="spc--width-100 spc--no-border">
            <span class="supportcase__task-selected-cases-id">
               {$case.id}
            </span>
          </td>
          <td class="spc--no-border">
            <span class="supportcase__task-selected-cases-subject">
               {$case.subject}
            </span>
          </td>
        </tr>
      </tbody>
    {/foreach}
  </table>
</div>
