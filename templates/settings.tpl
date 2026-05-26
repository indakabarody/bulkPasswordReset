{**
 * templates/settings.tpl
 *
 * Copyright (c) 2026 Indaka Barody
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the Bulk Password Reset plugin.
 * @author Indaka Barody
 *}
<script>
	$(function() {ldelim}
		$('#pluginTemplateSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form
	class="pkp_form"
	id="pluginTemplateSettings"
	method="POST"
	action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
	<!-- Always add the csrf token to secure your form -->
	{csrf}

	{fbvFormArea id="templatePluginSettingsArea"}
		{fbvFormSection label="plugins.generic.pluginTemplate.publicationStatement"}
			{fbvElement
				type="text"
				id="publicationStatement"
				value=$publicationStatement
				description="plugins.generic.pluginTemplate.publicationStatement.description"
			}
		{/fbvFormSection}
	{/fbvFormArea}
	{fbvFormButtons submitText="common.save"}
</form>
