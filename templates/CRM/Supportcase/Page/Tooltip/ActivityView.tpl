<div class="supportcase__tooltip-wrap">
  <div class="crm-summary-group">
    <table class="crm-table-group-summary">
      <tr>
        <td colspan="2">{$activity.subject}</td>
      </tr>
      <tr>
        <td colspan="2">
          <div>
            <div class="content supportcase__tooltip-activity-detail">
              {$activity.details}
            </div>
            <div class="clear"></div>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="1">
          <div>
            <div class="crm-section">
              <div class="label">
                {ts}Status:{/ts}
              </div>
              <div class="content">
                {$activity.status}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}Priority:{/ts}
              </div>
              <div class="content">
                {$activity.priority}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}Location:{/ts}
              </div>
              <div class="content">
                {$activity.location}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}Medium:{/ts}
              </div>
              <div class="content">
                {$activity.medium}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}Activity type:{/ts}
              </div>
              <div class="content">
                {$activity.activity_type}
              </div>
              <div class="clear"></div>
            </div>
          </div>
        </td>
        <td colspan="1">
          <div>
            <div class="crm-section">
              <div class="label">
                {ts}Date:{/ts}
              </div>
              <div class="content">
                {$activity.date}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}Added by:{/ts}
              </div>
              <div class="content">
                {foreach from=$activity.added_by_contacts item=contactName}
                  <div>{$contactName}</div>
                {/foreach}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}Assigned to:{/ts}
              </div>
              <div class="content">
                {foreach from=$activity.assigned_to_contacts item=contactName}
                  <div>{$contactName}</div>
                {/foreach}
              </div>
              <div class="clear"></div>
            </div>
            <div class="crm-section">
              <div class="label">
                {ts}With contact:{/ts}
              </div>
              <div class="content">
                {foreach from=$activity.with_contacts item=contactName}
                  <div>{$contactName}</div>
                {/foreach}
              </div>
              <div class="clear"></div>
            </div>
          </div>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="crm-section">
            <div class="label">
              {ts}Create date:{/ts}
            </div>
            <div class="content">
              {$activity.created_date}
            </div>
            <div class="clear"></div>
          </div>
          <div class="crm-section">
            <div class="label">
              {ts}Modified date:{/ts}
            </div>
            <div class="content">
              {$activity.modified_date}
            </div>
            <div class="clear"></div>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>
