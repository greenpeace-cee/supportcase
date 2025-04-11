<div class="scd__tooltip-wrap sc__p-20">
  <div>
    <div class="sc__font-size-20 sc__pb-10">{$activity.subject}</div>
    <div class="scd__tooltip-activity-detail">
      {$activity.details|escape:'htmlall':'UTF-8'}
    </div>
  </div>

  <div class="sc__flex sc__justify-content-space-between sc__pt-10">
    <div>
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Status:" value={$activity.status}}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Priority:" value={$activity.priority}}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Location:" value={$activity.location}}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Medium:" value={$activity.medium}}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Activity type:" value={$activity.activity_type}}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Create date:" value=$activity.created_date}
    </div>

    <div>
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Date:" value={$activity.date}}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Added by:" valueItems=$activity.added_by_contacts}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Assigned to:" valueItems=$activity.assigned_to_contacts}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="With contact:" valueItems=$activity.with_contacts}
      {include file="CRM/Supportcase/Page/Tooltip/ActivityViewItem.tpl" label="Modified date:" value=$activity.modified_date}
    </div>
  </div>
</div>
