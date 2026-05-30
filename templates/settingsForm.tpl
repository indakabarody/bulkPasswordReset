<script>
	$(function() {
		$('#bulkPasswordResetSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	});
</script>

<form class="pkp_form" id="bulkPasswordResetSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}

	{fbvFormArea id="bulkPasswordResetSettingsFormArea"}
		
		{if $isSiteAdmin}
		{fbvFormSection title="plugins.generic.bulkPasswordReset.selectJournals"}
			<div id="journalsList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; border-radius: 3px;">
				<div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 10px;">
					<input type="text" id="journalSearchFilter" placeholder="Search..." style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; width: 100%; max-width: 250px;" />
				</div>
				<table class="pkp_table" width="100%" style="text-align: left; margin: 0;" id="journalsTable">
					<thead>
						<tr>
							<th width="5%"><input type="checkbox" id="selectAllContexts" /></th>
							<th width="60%">{translate key="plugins.generic.bulkPasswordReset.journal"}</th>
							<th width="15%">{translate key="plugins.generic.bulkPasswordReset.initial"}</th>
							<th width="20%">{translate key="plugins.generic.bulkPasswordReset.path"}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$contextOptions key=contextId item=contextData}
							<tr>
								<td style="vertical-align: middle;"><input type="checkbox" name="selectedContexts[]" id="context_{$contextId|escape}" value="{$contextId|escape}" class="context_checkbox" /></td>
								<td style="vertical-align: middle;"><label for="context_{$contextId|escape}" style="cursor: pointer; font-weight: normal; margin: 0; display: block;">{$contextData.name|escape}</label></td>
								<td style="vertical-align: middle;"><label for="context_{$contextId|escape}" style="cursor: pointer; font-weight: normal; margin: 0; display: block;">{$contextData.acronym|escape}</label></td>
								<td style="vertical-align: middle;"><label for="context_{$contextId|escape}" style="cursor: pointer; font-weight: normal; margin: 0; display: block;">{$contextData.path|escape}</label></td>
							</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		{/fbvFormSection}
		<script>
			$(function() {
				// Select all journals toggle
				$('#selectAllContexts').click(function() {
					$('.context_checkbox:visible').prop('checked', this.checked);
				});

				// Simple search filter
				$('#journalSearchFilter').on('keyup', function() {
					var value = $(this).val().toLowerCase();
					$('#journalsTable tbody tr').filter(function() {
						var text = $(this).text().toLowerCase();
						$(this).toggle(text.indexOf(value) > -1);
						if ($(this).is(':hidden')) {
							$(this).find('.context_checkbox').prop('checked', false);
						}
					});
				});
			});
		</script>
		{/if}

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
