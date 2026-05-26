<script>
	$(function() {
		$('#bulkPasswordResetConfirmForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	});
</script>

<form class="pkp_form" id="bulkPasswordResetConfirmForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="confirm" save=true}">
	{csrf}

	<input type="hidden" name="roleId" value="{$roleId|escape}" />
	<input type="hidden" name="passwordLength" value="{$passwordLength|escape}" />
	<input type="hidden" name="charUppercase" value="{$charUppercase|escape}" />
	<input type="hidden" name="charLowercase" value="{$charLowercase|escape}" />
	<input type="hidden" name="charNumber" value="{$charNumber|escape}" />
	<input type="hidden" name="charSymbol" value="{$charSymbol|escape}" />
	<input type="hidden" name="mustChangePassword" value="{$mustChangePassword|escape}" />
	<input type="hidden" name="sendEmail" value="{$sendEmail|escape}" />

	{fbvFormArea id="bulkPasswordResetConfirmFormArea"}
		
		<div class="pkp_notification pkp_notification_warning">
			{translate key="plugins.generic.bulkPasswordReset.confirmWarning" count=$userCount roleName=$roleName}
			<br><br>
			<strong>{translate key="plugins.generic.bulkPasswordReset.globalUserWarning"}</strong>
		</div>

		{fbvFormSection title="plugins.generic.bulkPasswordReset.resetScope" list=true}
			{fbvElement type="radio" id="resetType_all" name="resetType" value="all" checked=true label="plugins.generic.bulkPasswordReset.resetAll"}
			{if $tooManyUsers}
				{fbvElement type="radio" id="resetType_specific" name="resetType" value="specific" disabled=true label="plugins.generic.bulkPasswordReset.resetSpecificDisabled"}
			{else}
				{fbvElement type="radio" id="resetType_specific" name="resetType" value="specific" label="plugins.generic.bulkPasswordReset.resetSpecific"}
			{/if}
		{/fbvFormSection}

		{if !$tooManyUsers}
		<div id="specificUsersList" style="display: none; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 20px;">
			<p><strong>{translate key="plugins.generic.bulkPasswordReset.selectUsers"}</strong></p>
			<table class="pkp_table" width="100%" style="text-align: left;">
				<thead>
					<tr>
						<th width="10%"></th>
						<th width="30%">{translate key="plugins.generic.bulkPasswordReset.name"}</th>
						<th width="20%">{translate key="plugins.generic.bulkPasswordReset.username"}</th>
						<th width="40%">{translate key="plugins.generic.bulkPasswordReset.email"}</th>
					</tr>
				</thead>
				<tbody>
					{foreach from=$userList key=userId item=userData}
						<tr>
							<td><input type="checkbox" name="selectedUserIds[]" value="{$userId|escape}" class="user_checkbox" /></td>
							<td>{$userData.name|escape}</td>
							<td>{$userData.username|escape}</td>
							<td>{$userData.email|escape}</td>
						</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		<script>
			$(function() {
				$('input[name="resetType"]').change(function() {
					if ($(this).val() === 'specific') {
						$('#specificUsersList').slideDown();
					} else {
						$('#specificUsersList').slideUp();
					}
				});
			});
		</script>
		{/if}

		{fbvFormSection list=true}
			{fbvElement type="checkbox" id="confirmReset" required="true" label="plugins.generic.bulkPasswordReset.confirmCheckbox"}
		{/fbvFormSection}

	{/fbvFormArea}

	{fbvFormButtons submitText="plugins.generic.bulkPasswordReset.execute"}
</form>
