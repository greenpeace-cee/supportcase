<div id="bootstrap-theme">
  <div class="crm-block crm-form-block">
    <div class="spc__form-wrap">
      <div>
        <div class="spc__form-block">
          <div class="spc__form-input-label">{$form.first_name.label}</div>
          <div class="spc__form-input-html">{$form.first_name.html}</div>
        </div>
        <div class="spc__form-block">
          <div class="spc__form-input-label">{$form.last_name.label}</div>
          <div class="spc__form-input-html">{$form.last_name.html}</div>
        </div>
        <div class="spc__form-block">
          <div class="spc__form-input-label">{$form.email.label}</div>
          <div class="spc__form-input-html">{$form.email.html}</div>
        </div>
      </div>

      <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
      </div>
    </div>
  </div>
</div>
