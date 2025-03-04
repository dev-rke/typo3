<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Install\Report\EnvironmentStatusReport;
use TYPO3\CMS\Install\Report\InstallStatusReport;
use TYPO3\CMS\Install\Report\SecurityStatusReport;
use TYPO3\CMS\Install\Updates\BackendUserLanguageMigration;
use TYPO3\CMS\Install\Updates\CollectionsExtractionUpdate;
use TYPO3\CMS\Install\Updates\DatabaseRowsUpdateWizard;
use TYPO3\CMS\Install\Updates\ShortcutRecordsMigration;
use TYPO3\CMS\Install\Updates\SvgFilesSanitization;
use TYPO3\CMS\Install\Updates\SysLogChannel;

defined('TYPO3') or die();

// Row updater wizard scans all table rows for update jobs.
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['databaseRowsUpdateWizard'] = DatabaseRowsUpdateWizard::class;

// v10->v11 wizards below this line
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['svgFilesSanitization'] = SvgFilesSanitization::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['shortcutRecordsMigration'] = ShortcutRecordsMigration::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['legacyCollectionsExtension'] = CollectionsExtractionUpdate::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['backendUserLanguage'] = BackendUserLanguageMigration::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['sysLogChannel'] = SysLogChannel::class;

// v11->v12 wizards below this line

// Register report module additions
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['typo3'][] = InstallStatusReport::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['security'][] = SecurityStatusReport::class;

// Only add the environment status report if not in CLI mode
if (!Environment::isCli()) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['reports']['tx_reports']['status']['providers']['system'][] = EnvironmentStatusReport::class;
}
