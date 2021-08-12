<div class="support-case-angular-js-wrap">
    <iframe width="100%" height="{$iframeHeight}" src="{$angularUrl}" style="border-style: none;"></iframe>
</div>

{literal}
    <style>
        .support-case-angular-js-wrap {
            background: white;
        }
    </style>
{/literal}

{if !$isRunInModalWindow}
    {literal}
        <style>
            /* hide some civi elements to make more space in page */
            #page-title {
                margin: 0px !important;
            }

            #header, #breadcrumb {
                display: none !important;
            }
        </style>
    {/literal}
{/if}
