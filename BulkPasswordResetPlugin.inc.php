<?php
/**
 * @file BulkPasswordResetPlugin.inc.php
 *
 * Copyright (c) 2026 Indaka Barody
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class BulkPasswordResetPlugin
 * @brief Plugin class for the Bulk Password Reset.
 * @author Indaka Barody
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class BulkPasswordResetPlugin extends GenericPlugin
{
    /**
     * @copydoc GenericPlugin::register()
     */
    public function register($category, $path, $mainContextId = null)
    {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && $this->getEnabled()) {
            // Hook for any frontend display if needed
        }
        return $success;
    }

    /**
     * Provide a name for this plugin
     * @return string
     */
    public function getDisplayName()
    {
        return __('plugins.generic.bulkPasswordReset.displayName');
    }

    /**
     * Provide a description for this plugin
     * @return string
     */
    public function getDescription()
    {
        return __('plugins.generic.bulkPasswordReset.description');
    }

    /**
     * @copydoc Plugin::getActions()
     */
    public function getActions($request, $actionArgs)
    {
        $actions = parent::getActions($request, $actionArgs);
        if (!$this->getEnabled()) {
            return $actions;
        }

        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url($request, null, null, 'manage', null, [
                    'verb' => 'settings',
                    'plugin' => $this->getName(),
                    'category' => 'generic',
                ]),
                $this->getDisplayName(),
            ),
            __('plugins.generic.bulkPasswordReset.toolName'),
            null,
        );

        array_unshift($actions, $linkAction);
        return $actions;
    }

    /**
     * @copydoc Plugin::manage()
     */
    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $this->import('BulkPasswordResetSettingsForm');
                $form = new BulkPasswordResetSettingsForm($this);
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }
                $form->readInputData();
                if ($form->validate()) {
                    // We redirect to the confirm step via JSONMessage
                    $router = $request->getRouter();
                    $confirmUrl = $router->url(
                        $request,
                        null,
                        null,
                        'manage',
                        null,
                        array_merge(
                            [
                                'verb' => 'confirm',
                                'plugin' => $this->getName(),
                                'category' => 'generic',
                            ],
                            $form->getFetchParameters(),
                        ),
                    );

                    // Return JSON message to replace modal content with confirm form. We can just load the confirm form and return its HTML
                    $this->import('BulkPasswordResetConfirmForm');
                    $confirmForm = new BulkPasswordResetConfirmForm($this, $form->getFetchParameters());
                    $confirmForm->initData();
                    return new JSONMessage(true, $confirmForm->fetch($request));
                }
                return new JSONMessage(false, $form->fetch($request));

            case 'confirm':
                $this->import('BulkPasswordResetConfirmForm');
                // Get parameters from request to pass to confirm form
                $params = [
                    'roleId' => $request->getUserVar('roleId'),
                    'passwordLength' => (int) $request->getUserVar('passwordLength'),
                    'charUppercase' => (bool) $request->getUserVar('charUppercase'),
                    'charLowercase' => (bool) $request->getUserVar('charLowercase'),
                    'charNumber' => (bool) $request->getUserVar('charNumber'),
                    'charSymbol' => (bool) $request->getUserVar('charSymbol'),
                ];

                $form = new BulkPasswordResetConfirmForm($this, $params);
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }
                $form->readInputData();
                if ($form->validate()) {
                    // Process the reset
                    return $form->execute($request);
                }
                return new JSONMessage(false, $form->fetch($request));

            case 'download':
                // Handle file download
                $fileId = $request->getUserVar('fileId');
                $sessionManager = SessionManager::getManager();
                $session = $sessionManager->getUserSession();
                $allowedFileId = $session->getSessionVar('bulkPasswordResetFileId');

                if ($fileId && $fileId === $allowedFileId) {
                    $filePath = sys_get_temp_dir() . '/' . $fileId . '.csv';
                    if (file_exists($filePath)) {
                        header('Content-Type: text/csv');
                        header('Content-Disposition: attachment; filename="password_reset_export.csv"');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        readfile($filePath);
                        unlink($filePath);
                        $session->unsetSessionVar('bulkPasswordResetFileId');
                        exit();
                    }
                }
                return new JSONMessage(false, __('plugins.generic.bulkPasswordReset.downloadError'));
        }
        return parent::manage($args, $request);
    }
}
