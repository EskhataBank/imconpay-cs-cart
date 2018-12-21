{* $Id: imconpay.tpl  $cas *}

<div class="control-group">
	<label class="control-label" for="merchantid">{__("merchant_id")}:</label>
  <div class="controls">
    <input type="text" name="payment_data[processor_params][imconpay_merchantid]" id="merchantid" value="{$processor_params.imconpay_merchantid}" class="input-text" />
  </div>
</div>

<div class="control-group">
	<label class="control-label" for="details">{__("shared_secret")}:</label>
  <div class="controls">
    <input type="text" name="payment_data[processor_params][imconpay_merchnatSecretKey]" id="secret" value="{$processor_params.imconpay_merchnatSecretKey}" class="input-text" size="100" />
  </div>
</div>
<div class="control-group">
	<label class="control-label" for="details">{__("language")}</label>
  <div class="controls">
    <input type="text" name="payment_data[processor_params][imconpay_lang]" id="lang" value="{$processor_params.imconpay_lang}" class="input-text" size="100" />
  </div>
</div>
<div class="control-group">
	<label class="control-label" for="imconpay_currency">{__("currency")}:</label>
	<div class="controls">
		<select name="payment_data[processor_params][currency]" id="imconpay_currency">
			<option value="shop_cur"{if $processor_params.currency == "shop_cur"} selected="selected"{/if}>{__("primary_currency")}</option>
			<option value="USD"{if $processor_params.currency == "USD"} selected="selected"{/if}>{__("currency_code_usd")}</option>
			<option value="EUR"{if $processor_params.currency == "EUR"} selected="selected"{/if}>{__("currency_code_eur")}</option>
			<option value="TJS"{if $processor_params.currency == "TJS"} selected="selected"{/if}>{__("currency_code_tjs")}</option>
			<option value="GBP"{if $processor_params.currency == "GBP"} selected="selected"{/if}>{__("currency_code_gbp")}</option>
			<option value="RUB"{if $processor_params.currency == "RUB"} selected="selected"{/if}>{__("currency_code_rub")}</option>
		</select>
	</div>
</div>
