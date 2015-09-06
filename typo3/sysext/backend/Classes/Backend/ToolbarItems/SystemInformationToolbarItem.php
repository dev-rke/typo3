<?php
namespace TYPO3\CMS\Backend\Backend\ToolbarItems;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\Toolbar\Enumeration\InformationStatus;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Http\AjaxRequestHandler;
use \TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Render system info toolbar item
 */
class SystemInformationToolbarItem implements ToolbarItemInterface {

	/**
	 * @var StandaloneView
	 */
	protected $standaloneView = NULL;

	/**
	 * Template file for the dropdown menu
	 */
	const TOOLBAR_MENU_TEMPLATE = 'SystemInformation.html';

	/**
	 * Number displayed as badge on the dropdown trigger
	 *
	 * @var int
	 */
	protected $totalCount = 0;

	/**
	 * Holds the highest severity
	 *
	 * @var string
	 */
	protected $highestSeverity = '';

	/**
	 * The CSS class for the badge
	 *
	 * @var string
	 */
	protected $severityBadgeClass = '';

	/**
	 * @var array
	 */
	protected $systemInformation = array();

	/**
	 * @var array
	 */
	protected $systemMessages = array();

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected $signalSlotDispatcher = NULL;

	/**
	 * Constructor
	 */
	public function __construct() {
		if (!$this->checkAccess()) {
			return;
		}

		$extPath = ExtensionManagementUtility::extPath('backend');
		/* @var $view StandaloneView */
		$this->standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
		$this->standaloneView->setTemplatePathAndFilename($extPath . 'Resources/Private/Templates/ToolbarMenu/' . static::TOOLBAR_MENU_TEMPLATE);

		$this->getPageRenderer()->loadRequireJsModule('TYPO3/CMS/Backend/Toolbar/SystemInformationMenu');
	}

	/**
	 * Collect the information for the menu
	 */
	protected function collectInformation() {
		$this->getWebServer();
		$this->getPhpVersion();
		$this->getDatabase();
		$this->getApplicationContext();
		$this->getGitRevision();
		$this->getOperatingSystem();

		$this->emitGetSystemInformation();
		$this->emitLoadMessages();

		$this->severityBadgeClass = $this->highestSeverity !== InformationStatus::STATUS_NOTICE ? 'badge-' . $this->highestSeverity : '';
	}

	/**
	 * Renders the menu for AJAX calls
	 *
	 * @param array $params
	 * @param AjaxRequestHandler $ajaxObj
	 */
	public function renderAjax($params = array(), $ajaxObj) {
		$this->collectInformation();
		$ajaxObj->addContent('systemInformationMenu', $this->getDropDown());
	}

	/**
	 * Gets the PHP version
	 *
	 * @return void
	 */
	protected function getPhpVersion() {
		$this->systemInformation[] = array(
			'title' => $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo.phpversion', TRUE),
			'value' => PHP_VERSION,
			'icon' => '<span class="fa fa-code"></span>'
		);
	}

	/**
	 * Get the database info
	 *
	 * @return void
	 */
	protected function getDatabase() {
		$this->systemInformation[] = array(
			'title' => $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo.database', TRUE),
			'value' => $this->getDatabaseConnection()->getServerVersion(),
			'icon' => '<span class="fa fa-database"></span>'
		);
	}

	/**
	 * Gets the application context
	 *
	 * @return void
	 */
	protected function getApplicationContext() {
		$applicationContext = GeneralUtility::getApplicationContext();
		$this->systemInformation[] = array(
			'title' => $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo.applicationcontext', TRUE),
			'value' => (string)$applicationContext,
			'status' => $applicationContext->isProduction() ? InformationStatus::STATUS_OK : InformationStatus::STATUS_WARNING,
			'icon' => '<span class="fa fa-tasks"></span>'
		);
	}

	/**
	 * Gets the current GIT revision and branch
	 *
	 * @return void
	 */
	protected function getGitRevision() {
		if (!StringUtility::endsWith(TYPO3_version, '-dev') || \TYPO3\CMS\Core\Core\SystemEnvironmentBuilder::isFunctionDisabled('exec')) {
			return;
		}
		// check if git exists
		CommandUtility::exec('git --version', $_, $returnCode);
		if ((int)$returnCode !== 0) {
			// git is not available
			return;
		}

		$revision = trim(CommandUtility::exec('git rev-parse --short HEAD'));
		$branch = trim(CommandUtility::exec('git rev-parse --abbrev-ref HEAD'));
		if (!empty($revision) && !empty($branch)) {
			$this->systemInformation[] = array(
				'title' => $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo.gitrevision', TRUE),
				'value' => sprintf('%s [%s]', $revision, $branch),
				'icon' => '<span class="fa fa-git"></span>'
			);
		}
	}

	/**
	 * Gets the system kernel and version
	 *
	 * @return void
	 */
	protected function getOperatingSystem() {
		$kernelName = php_uname('s');
		switch (strtolower($kernelName)) {
			case 'linux':
				$icon = 'linux';
				break;
			case 'darwin':
				$icon = 'apple';
				break;
			default:
				$icon = 'windows';
		}
		$this->systemInformation[] = array(
			'title' => $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo.operatingsystem', TRUE),
			'value' => $kernelName . ' ' . php_uname('r'),
			'icon' => '<span class="fa fa-' . htmlspecialchars($icon) . '"></span>'
		);
	}

	/**
	 * Gets the webserver software
	 */
	protected function getWebServer() {
		$this->systemInformation[] = array(
			'title' => $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo.webserver', TRUE),
			'value' => htmlspecialchars($_SERVER['SERVER_SOFTWARE']),
			'icon' => '<span class="fa fa-server"></span>'
		);
	}

	/**
	 * Emits the "getSystemInformation" signal
	 *
	 * @return void
	 */
	protected function emitGetSystemInformation() {
		list($systemInformation) = $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'getSystemInformation', array(array()));
		if (!empty($systemInformation)) {
			$this->systemInformation[] = $systemInformation;
		}
	}

	/**
	 * Emits the "loadMessages" signal
	 *
	 * @return void
	 */
	protected function emitLoadMessages() {
		list($message) = $this->getSignalSlotDispatcher()->dispatch(__CLASS__, 'loadMessages', array(array()));
		if (empty($message)) {
			return;
		}

		// increase counter
		if (isset($message['count'])) {
			$this->totalCount += (int)$message['count'];
		}

		// define the severity for the badge
		if (InformationStatus::mapStatusToInt($message['status']) > InformationStatus::mapStatusToInt($this->highestSeverity)) {
			$this->highestSeverity = $message['status'];
		}

		$this->systemMessages[] = $message;
	}

	/**
	 * Checks whether the user has access to this toolbar item
	 *
	 * @return bool TRUE if user has access, FALSE if not
	 */
	public function checkAccess() {
		return $this->getBackendUserAuthentication()->isAdmin();
	}

	/**
	 * Render system information dropdown
	 *
	 * @return string Icon HTML
	 */
	public function getItem() {
		$iconFactory = GeneralUtility::makeInstance(IconFactory::class);
		$title = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:toolbarItems.sysinfo', TRUE);
		$icon = $iconFactory->getIcon('actions-system-list-open', Icon::SIZE_SMALL);
		return '<span title="' . $title . '">' . $icon . '<span id="t3js-systeminformation-counter" class="badge"></span></span>';
	}

	/**
	 * Render drop down
	 *
	 * @return string Drop down HTML
	 */
	public function getDropDown() {
		if (!$this->checkAccess()) {
			return '';
		}

		$request = $this->standaloneView->getRequest();
		$request->setControllerExtensionName('backend');
		$this->standaloneView->assignMultiple(array(
			'installToolUrl' => BackendUtility::getModuleUrl('system_InstallInstall'),
			'messages' => $this->systemMessages,
			'count' => $this->totalCount,
			'severityBadgeClass' => $this->severityBadgeClass,
			'systemInformation' => $this->systemInformation
		));
		return $this->standaloneView->render();
	}

	/**
	 * No additional attributes needed.
	 *
	 * @return array
	 */
	public function getAdditionalAttributes() {
		return array();
	}

	/**
	 * This item has a drop down
	 *
	 * @return bool
	 */
	public function hasDropDown() {
		return TRUE;
	}

	/**
	 * Position relative to others
	 *
	 * @return int
	 */
	public function getIndex() {
		return 75;
	}

	/**
	 * Returns the current BE user.
	 *
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUserAuthentication() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Returns DatabaseConnection
	 *
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}

	/**
	 * Returns current PageRenderer
	 *
	 * @return PageRenderer
	 */
	protected function getPageRenderer() {
		return GeneralUtility::makeInstance(PageRenderer::class);
	}

	/**
	 * Returns LanguageService
	 *
	 * @return \TYPO3\CMS\Lang\LanguageService
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}

	/**
	 * Get the SignalSlot dispatcher
	 *
	 * @return \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 */
	protected function getSignalSlotDispatcher() {
		if (!isset($this->signalSlotDispatcher)) {
			$this->signalSlotDispatcher = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class)
				->get(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
		}
		return $this->signalSlotDispatcher;
	}
}