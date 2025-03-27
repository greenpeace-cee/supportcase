<div id="bootstrap-theme" class="sc__page-wrap">
  <div class="crm-form-block">
    <div>
      <div>
        <div class="crm-section">
          <div class="label">
            {$form.client_contact_id.label}
          </div>
          <div class="content">
            <div class="sc__max-width-300">
              {$form.client_contact_id.html}
            </div>
          </div>
          <div class="clear"></div>
        </div>

        <div class="crm-section">
          <div class="label">
            {$form.subject.label}
          </div>
          <div class="content">
            <div class="sc__max-width-300">
              {$form.subject.html}
            </div>
          </div>
          <div class="clear"></div>
        </div>

        <div class="crm-section">
          <div class="label">
            {$form.category_id.label}
          </div>
          <div class="content">
            <div class="sc__max-width-300">
              {$form.category_id.html}
            </div>
          </div>
          <div class="clear"></div>
        </div>

        <div class="sc__display-none">
          {$form.dashboard_search_qf_key.html}
          {$form.prefill_email_id.html}
        </div>
      </div>

      <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
