<script>
	$(function() {
		$('#bulkPasswordResetSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	});
</script>

<form class="pkp_form" id="bulkPasswordResetSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}

	{fbvFormArea id="bulkPasswordResetSettingsFormArea"}
		
		{fbvFormSection title="plugins.generic.bulkPasswordReset.targetRole" description="plugins.generic.bulkPasswordReset.targetRoleDescription"}
			{fbvElement type="select" id="roleId" from=$userGroupOptions selected=$roleId required="true" translate=false}
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.bulkPasswordReset.passwordLength"}
			{fbvElement type="text" id="passwordLength" value=$passwordLength required="true"}
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.bulkPasswordReset.charTypes" list=true}
			{fbvElement type="checkbox" id="charUppercase" checked=$charUppercase label="plugins.generic.bulkPasswordReset.charUppercase"}
			{fbvElement type="checkbox" id="charLowercase" checked=$charLowercase label="plugins.generic.bulkPasswordReset.charLowercase"}
			{fbvElement type="checkbox" id="charNumber" checked=$charNumber label="plugins.generic.bulkPasswordReset.charNumber"}
			{fbvElement type="checkbox" id="charSymbol" checked=$charSymbol label="plugins.generic.bulkPasswordReset.charSymbol"}
		{/fbvFormSection}

		{fbvFormSection title="plugins.generic.bulkPasswordReset.additionalOptions" list=true}
			{fbvElement type="checkbox" id="mustChangePassword" checked=$mustChangePassword label="plugins.generic.bulkPasswordReset.mustChangePassword"}
			{fbvElement type="checkbox" id="sendEmail" checked=$sendEmail label="plugins.generic.bulkPasswordReset.sendEmail"}
		{/fbvFormSection}

	{/fbvFormArea}

	{fbvFormButtons submitText="plugins.generic.bulkPasswordReset.next"}
</form>
