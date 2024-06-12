<div class="crm-block crm-form-block">
  <div class="spc__form-wrap">
    <div>
      <div class="spc__form-block">
        <div class="spc__form-input-label">{$form.client_contact_id.label}</div>
        <div class="spc__form-input-html">{$form.client_contact_id.html}</div>
      </div>
      <div class="spc__form-block">
        <div class="spc__form-input-label">{$form.subject.label}</div>
        <div class="spc__form-input-html">{$form.subject.html}</div>
      </div>
      <div class="spc__form-block">
        <div class="spc__form-input-label">{$form.category_id.label}</div>
        <div class="spc__form-input-html">{$form.category_id.html}</div>
      </div>
      <div class="spc__hide">
          {$form.dashboard_search_qf_key.html}
          {$form.prefill_email_id.html}
      </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
</div>
