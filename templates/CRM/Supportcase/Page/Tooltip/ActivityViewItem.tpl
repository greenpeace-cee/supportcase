<div class="sc__flex sc__justify-content-space-between sc__width-300">
  <div class="sc__font-weight-bold">
    {ts}{$label}{/ts}
  </div>
  <div>
    {if $value}
      {$value}
    {/if}

    {if $valueItems}
      {foreach from=$valueItems item=valueItem}
        <div>{$valueItem}</div>
      {/foreach}
    {/if}
  </div>
</div>
