<?php
/**
 * @file BulkPasswordResetSettingsForm.inc.php
 *
 * Copyright (c) 2026 Indaka Barody
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BulkPasswordResetSettingsForm
 * @brief Form for step 1 of Bulk Password Reset
 * @author Indaka Barody
 */

import('lib.pkp.classes.form.Form');

class BulkPasswordResetSettingsForm extends Form
{
    /** @var BulkPasswordResetPlugin */
    public $plugin;

    /**
     * Constructor
     * @param $plugin BulkPasswordResetPlugin
     */
    public function __construct($plugin)
    {
        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));
        $this->plugin = $plugin;

        $this->addCheck(new FormValidator($this, 'roleId', 'required', 'plugins.generic.bulkPasswordReset.error.roleRequired'));
        $this->addCheck(new FormValidator($this, 'passwordLength', 'required', 'plugins.generic.bulkPasswordReset.error.lengthRequired'));
        $this->addCheck(
            new FormValidatorCustom($this, 'passwordLength', 'required', 'plugins.generic.bulkPasswordReset.error.lengthMin', function ($length) {
                return (int) $length >= 8;
            }),
        );
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::initData()
     */
    public function initData()
    {
        $this->setData('passwordLength', 8);
        $this->setData('charUppercase', true);
        $this->setData('charLowercase', true);
        $this->setData('charNumber', true);
        $this->setData('charSymbol', false);
        $this->setData('mustChangePassword', true);
        $this->setData('sendEmail', false);
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(['roleId', 'passwordLength', 'charUppercase', 'charLowercase', 'charNumber', 'charSymbol', 'mustChangePassword', 'sendEmail']);
    }

    /**
     * @copydoc Form::fetch()
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());

        $context = $request->getContext();

        // Get available user groups for the current context
        $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
        $userGroups = $userGroupDao->getByContextId($context->getId());

        $userGroupOptions = [
            'all_journal' => __('plugins.generic.bulkPasswordReset.allJournalRoles'),
            'all_ojs' => __('plugins.generic.bulkPasswordReset.allOjsRoles'),
        ];

        while ($userGroup = $userGroups->next()) {
            $userGroupOptions[$userGroup->getId()] = $userGroup->getLocalizedName();
        }

        $templateMgr->assign('userGroupOptions', $userGroupOptions);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Get the parameters to pass to the next step
     * @return array
     */
    public function getFetchParameters()
    {
        return [
            'roleId' => $this->getData('roleId'),
            'passwordLength' => $this->getData('passwordLength'),
            'charUppercase' => $this->getData('charUppercase') ? 1 : 0,
            'charLowercase' => $this->getData('charLowercase') ? 1 : 0,
            'charNumber' => $this->getData('charNumber') ? 1 : 0,
            'charSymbol' => $this->getData('charSymbol') ? 1 : 0,
            'mustChangePassword' => $this->getData('mustChangePassword') ? 1 : 0,
            'sendEmail' => $this->getData('sendEmail') ? 1 : 0,
        ];
    }
}
