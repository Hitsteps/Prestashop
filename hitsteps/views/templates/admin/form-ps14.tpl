{*
*  @author Hitsteps.com sales@hitsteps.com
*  @copyright  2010-2016 Hitsteps.com
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<form enctype="multipart/form-data" method="post" class="defaultForm hitsteps" id="configuration_form">
	<fieldset id="fieldset_0">

		<label>Hitsteps API code</label>							

		<div class="margin-form">
			<input type="text" size="20" class="" value="{$account_id|escape:'htmlall':'UTF-8'}" id="HS_API_CODE" name="HS_API_CODE">&nbsp;<sup>*</sup>
			<span name="help_box" class="hint" style="display: none;">Find your API code once you add your website in your hitsteps account in setting page<span class="hint-pointer"></span></span>  
		</div>
		<div class="clear"></div>

		<div class="margin-form">
			<input class="button" type="submit" name="submithitsteps" value="{l s='Save' mod='hitsteps'}" id="configuration_form_submit_btn">
		</div>

		<div class="small"><sup>*</sup>Required</div>
	</fieldset>
</form>
