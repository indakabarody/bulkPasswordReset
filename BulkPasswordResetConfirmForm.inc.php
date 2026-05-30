<?php
/**
 * @file BulkPasswordResetConfirmForm.inc.php
 *
 * Copyright (c) 2026 Indaka Barody
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BulkPasswordResetConfirmForm
 * @brief Form for step 2 (confirmation) of Bulk Password Reset
 * @author Indaka Barody
 */

import('lib.pkp.classes.form.Form');

class BulkPasswordResetConfirmForm extends Form
{
    /** @var BulkPasswordResetPlugin */
    public $plugin;

    /** @var array */
    public $params;

    /**
     * Constructor
     * @param $plugin BulkPasswordResetPlugin
     * @param $params array Parameters from step 1
     */
    public function __construct($plugin, $params)
    {
        parent::__construct($plugin->getTemplateResource('confirmForm.tpl'));
        $this->plugin = $plugin;
        $this->params = $params;

        $this->addCheck(new FormValidator($this, 'confirmReset', 'required', 'plugins.generic.bulkPasswordReset.error.confirmRequired'));
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * @copydoc Form::initData()
     */
    public function initData()
    {
        foreach ($this->params as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * @copydoc Form::readInputData()
     */
    public function readInputData()
    {
        $this->readUserVars(['confirmReset', 'roleId', 'passwordLength', 'charUppercase', 'charLowercase', 'charNumber', 'charSymbol', 'mustChangePassword', 'sendEmail', 'resetType', 'selectedUserIds', 'selectedContexts']);
    }

    /**
     * @copydoc Form::fetch()
     */
    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());

        $context = $request->getContext();

        import('lib.pkp.classes.security.Validation');
        $isSiteAdmin = Validation::isSiteAdmin();
        $selectedContexts = $this->getData('selectedContexts') ?? $this->params['selectedContexts'] ?? [];
        if (!$isSiteAdmin || empty($selectedContexts)) {
            $selectedContexts = [$context->getId()];
        }
        $templateMgr->assign('selectedContexts', $selectedContexts);

        $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
        $roleId = $this->getData('roleId') ?? $this->params['roleId'];

        if ($roleId === 'all_journal') {
            if ($isSiteAdmin && count($selectedContexts) > 1) {
                $templateMgr->assign('roleName', __('plugins.generic.bulkPasswordReset.allSelectedJournalsRoles'));
            } else {
                $templateMgr->assign('roleName', __('plugins.generic.bulkPasswordReset.allJournalRoles'));
            }
            $userIds = \Illuminate\Database\Capsule\Manager::table('user_user_groups as uug')
                ->join('user_groups as ug', 'uug.user_group_id', '=', 'ug.user_group_id')
                ->whereIn('ug.context_id', $selectedContexts)
                ->select('uug.user_id')
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            $userCount = count($userIds);
            $templateMgr->assign('userCount', $userCount);
        } else {
            $userGroup = $userGroupDao->getById($roleId, $context->getId());

            if ($userGroup) {
                if ($isSiteAdmin) {
                    $templateMgr->assign('roleName', $userGroup->getLocalizedName() . (count($selectedContexts) > 1 ? ' (Multiple Journals)' : ''));
                    $targetRoleId = $userGroup->getRoleId();
                    $userIds = \Illuminate\Database\Capsule\Manager::table('user_user_groups as uug')
                        ->join('user_groups as ug', 'uug.user_group_id', '=', 'ug.user_group_id')
                        ->whereIn('ug.context_id', $selectedContexts)
                        ->where('ug.role_id', $targetRoleId)
                        ->select('uug.user_id')
                        ->distinct()
                        ->pluck('user_id')
                        ->toArray();
                    $userCount = count($userIds);
                    $templateMgr->assign('userCount', $userCount);
                } else {
                    $templateMgr->assign('roleName', $userGroup->getLocalizedName());
                    $userCount = $userGroupDao->getContextUsersCount($context->getId(), $roleId);
                    $templateMgr->assign('userCount', $userCount);
                }
            } else {
                $templateMgr->assign('roleName', 'Unknown');
                $userCount = 0;
                $templateMgr->assign('userCount', 0);
            }
        }

        $tooManyUsers = $userCount > 500;
        $templateMgr->assign('tooManyUsers', $tooManyUsers);

        if (!$tooManyUsers) {
            $userDao = DAORegistry::getDAO('UserDAO');
            $usersArray = [];
            
            if (isset($userIds)) {
                // $userIds is set if we used the custom Capsule queries (all_journal OR isSiteAdmin)
                foreach ($userIds as $uid) {
                    $u = $userDao->getById($uid);
                    if ($u) $usersArray[] = $u;
                }
            } else {
                // Fallback for regular Journal Managers
                $users = $userGroupDao->getUsersById($roleId, $context->getId());
                while ($u = $users->next()) {
                    $usersArray[] = $u;
                }
            }

            $userList = [];
            foreach ($usersArray as $user) {
                $userList[$user->getId()] = [
                    'name' => $user->getFullName(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                ];
            }
            
            $templateMgr->assign('userList', $userList);
        }

        return parent::fetch($request, $template, $display);
    }

    /**
     * Execute the password reset
     * @param $request Request
     * @return JSONMessage
     */
    public function execute(...$functionArgs)
    {
        $request = Application::get()->getRequest();
        $context = $request->getContext();
        $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $roleId = $this->getData('roleId');
        $length = (int) $this->getData('passwordLength');
        $length = $length >= 8 ? $length : 8;

        $useUpper = (bool) $this->getData('charUppercase');
        $useLower = (bool) $this->getData('charLowercase');
        $useNum = (bool) $this->getData('charNumber');
        $useSym = (bool) $this->getData('charSymbol');

        $mustChange = (bool) $this->getData('mustChangePassword');
        $sendEmail = (bool) $this->getData('sendEmail');
        $resetType = $this->getData('resetType') ?: 'all';
        $selectedUserIds = (array) $this->getData('selectedUserIds');

        import('lib.pkp.classes.security.Validation');
        $isSiteAdmin = Validation::isSiteAdmin();
        $selectedContexts = $this->getData('selectedContexts') ?: [];
        if (!$isSiteAdmin || empty($selectedContexts)) {
            $selectedContexts = [$context->getId()];
        }

        // At least one char type must be selected
        if (!$useUpper && !$useLower && !$useNum && !$useSym) {
            $useLower = true;
            $useNum = true;
        }

        if ($roleId === 'all_journal') {
            $userIds = \Illuminate\Database\Capsule\Manager::table('user_user_groups as uug')
                ->join('user_groups as ug', 'uug.user_group_id', '=', 'ug.user_group_id')
                ->whereIn('ug.context_id', $selectedContexts)
                ->select('uug.user_id')
                ->distinct()
                ->pluck('user_id')
                ->toArray();
            $usersArray = [];
            foreach ($userIds as $uid) {
                $u = $userDao->getById($uid);
                if ($u) $usersArray[] = $u;
            }
        } else {
            $userGroup = $userGroupDao->getById($roleId, $context->getId());
            if ($userGroup && $isSiteAdmin) {
                $targetRoleId = $userGroup->getRoleId();
                $userIds = \Illuminate\Database\Capsule\Manager::table('user_user_groups as uug')
                    ->join('user_groups as ug', 'uug.user_group_id', '=', 'ug.user_group_id')
                    ->whereIn('ug.context_id', $selectedContexts)
                    ->where('ug.role_id', $targetRoleId)
                    ->select('uug.user_id')
                    ->distinct()
                    ->pluck('user_id')
                    ->toArray();
                
                $usersArray = [];
                foreach ($userIds as $uid) {
                    $u = $userDao->getById($uid);
                    if ($u) $usersArray[] = $u;
                }
            } else {
                $users = $userGroupDao->getUsersById($roleId, $context->getId());
                $usersArray = [];
                while ($u = $users->next()) {
                    $usersArray[] = $u;
                }
            }
        }

        $csvData = [];
        $csvData[] = ['username', 'email', 'name', 'new_password'];

        import('lib.pkp.classes.security.Validation');

        foreach ($usersArray as $user) {
            // If specific reset is selected, skip users not in the selected array
            if ($resetType === 'specific' && !in_array($user->getId(), $selectedUserIds)) {
                continue;
            }

            $newPassword = $this->_generatePassword($length, $useUpper, $useLower, $useNum, $useSym);

            // Hash password using OJS standard Validation
            $hash = Validation::encryptCredentials($user->getUsername(), $newPassword);

            // Update user
            $user->setPassword($hash);
            $user->setMustChangePassword($mustChange ? 1 : 0);
            $userDao->updateObject($user);

            // Send email if requested
            if ($sendEmail) {
                import('lib.pkp.classes.mail.MailTemplate');
                $mail = new MailTemplate();
                $mail->addRecipient($user->getEmail(), $user->getFullName());

                $subject = __('plugins.generic.bulkPasswordReset.emailSubject');
                $acronym = $context ? $context->getLocalizedAcronym() : '';
                if (!empty($acronym)) {
                    $subject = '[' . $acronym . '] ' . $subject;
                }
                $mail->setSubject($subject);

                $loginUrl = $request->getDispatcher()->url($request, ROUTE_PAGE, $context->getPath(), 'login');

                // We get the raw translated string without replacing variables yet
                $body = __('plugins.generic.bulkPasswordReset.emailBody');

                $mail->setBody(nl2br($body));

                // Let MailTemplate replace the parameters so it can auto-format the URL into an HTML link
                $mail->assignParams([
                    'name' => $user->getFullName(),
                    'username' => $user->getUsername(),
                    'password' => $newPassword,
                    'journalName' => $context->getLocalizedName(),
                    'loginUrl' => $loginUrl,
                ]);
                $mail->setReplyTo($context->getData('contactEmail'), $context->getData('contactName'));
                $mail->send();
            }

            $csvData[] = [$user->getUsername(), $user->getEmail(), $user->getFullName(), $newPassword];
        }

        // Generate CSV File
        $fileId = uniqid('pr_');
        $filePath = sys_get_temp_dir() . '/' . $fileId . '.csv';
        $fp = fopen($filePath, 'w');
        foreach ($csvData as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);

        // Store fileId in session
        $sessionManager = SessionManager::getManager();
        $session = $sessionManager->getUserSession();
        $session->setSessionVar('bulkPasswordResetFileId', $fileId);

        $router = $request->getRouter();
        $downloadUrl = $router->url($request, null, null, 'manage', null, [
            'verb' => 'download',
            'plugin' => $this->plugin->getName(),
            'category' => 'generic',
            'fileId' => $fileId,
        ]);

        $message = __('plugins.generic.bulkPasswordReset.successMessage');

        // We return a JSON message that will display the success and provide a download link.
        // Since we are in an AjaxModal, we can replace the modal content with a success message and a download button.
        $html = '<div class="pkp_form"><p>' . htmlspecialchars($message) . '</p>';
        $html .= '<p><a href="' . htmlspecialchars($downloadUrl) . '" class="pkp_button" target="_blank">' . htmlspecialchars(__('plugins.generic.bulkPasswordReset.downloadCsv')) . '</a></p>';
        $html .= '<p class="pkp_help">' . htmlspecialchars(__('plugins.generic.bulkPasswordReset.downloadNotice')) . '</p></div>';

        return new JSONMessage(true, $html);
    }

    private function _generatePassword($length, $upper, $lower, $num, $sym)
    {
        $chars = '';
        if ($upper) {
            $chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($lower) {
            $chars .= 'abcdefghijklmnopqrstuvwxyz';
        }
        if ($num) {
            $chars .= '0123456789';
        }
        if ($sym) {
            $chars .= '!@#$%^&*()-_=+';
        }

        $password = '';
        $charLen = strlen($chars);
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $charLen - 1)];
        }
        return $password;
    }
}
