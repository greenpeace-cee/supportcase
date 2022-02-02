<div class="crm-block crm-form-block">
  <div class="add-case">
    <div>
      <div class="add-case__input">
        <div class="add-case__input-label">{$form.first_name.label}</div>
        <div class="add-case__input-html">{$form.first_name.html}</div>
      </div>
      <div class="add-case__input">
        <div class="add-case__input-label">{$form.last_name.label}</div>
        <div class="add-case__input-html">{$form.last_name.html}</div>
      </div>
      <div class="add-case__input">
        <div class="add-case__input-label">{$form.email.label}</div>
        <div class="add-case__input-html">{$form.email.html}</div>
      </div>
    </div>

    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
  </div>
</div>

{literal}
  <style>

      .add-case {
          padding: 20px 10px 0 10px;
      }

      .add-case__input {
          display: flex;
          margin-bottom: 5px;
          align-items: center;
      }

      .add-case__input-label {
          width: 200px;
          text-align: right ;
      }

      .add-case__input-html {
          width: 300px;
      }
      .add-case__input-html .crm-error {
          display: block;
          margin-top: 5px;
      }
  </style>
{/literal}
