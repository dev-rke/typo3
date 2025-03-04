<?php

declare(strict_types=1);

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

namespace TYPO3\CMS\Beuser\ViewHelpers;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Render permission icon group (user / group / others) of the "Access" module.
 *
 * Most of that could be done in fluid directly, but this ViewHelper
 * is much better performance wise.
 *
 * @internal
 */
final class PermissionsViewHelper extends AbstractViewHelper
{
    use CompileWithRenderStatic;

    /**
     * As this ViewHelper renders HTML, the output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;

    protected static array $cachePermissionLabels = [];

    public function initializeArguments(): void
    {
        $this->registerArgument('permission', 'int', 'Current permission', true);
        $this->registerArgument('scope', 'string', '"user" / "group" / "everybody"', true);
        $this->registerArgument('pageId', 'int', '', true);
    }

    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext): string
    {
        $masks = [1, 16, 2, 4, 8];

        if (empty(self::$cachePermissionLabels)) {
            foreach ($masks as $mask) {
                self::$cachePermissionLabels[$mask] = htmlspecialchars(self::getLanguageService()->sL(
                    'LLL:EXT:beuser/Resources/Private/Language/locallang_mod_permission.xlf:' . $mask,
                ));
            }
        }

        $icon = '';
        foreach ($masks as $mask) {
            if ($arguments['permission'] & $mask) {
                $permissionClass = 'fa-check text-success';
                $mode = 'delete';
            } else {
                $permissionClass = 'fa-times text-danger';
                $mode = 'add';
            }

            $label = self::$cachePermissionLabels[$mask];
            $icon .= '<button'
                . ' aria-label="' . htmlspecialchars($label) . ', ' . htmlspecialchars($mode) . ', ' . htmlspecialchars($arguments['scope']) . '"'
                . ' title="' . htmlspecialchars($label) . '"'
                . ' data-bs-toggle="tooltip"'
                . ' data-page="' . htmlspecialchars((string)$arguments['pageId']) . '"'
                . ' data-permissions="' . htmlspecialchars((string)$arguments['permission']) . '"'
                . ' data-who="' . htmlspecialchars($arguments['scope']) . '"'
                . ' data-bits="' . htmlspecialchars((string)$mask) . '"'
                . ' data-mode="' . htmlspecialchars($mode) . '"'
                . ' class="t3-icon btn-clear change-permission fa ' . htmlspecialchars($permissionClass) . '"></button>';
        }

        return '<span id="' . htmlspecialchars($arguments['pageId'] . '_' . $arguments['scope']) . '">' . $icon . '</span>';
    }

    protected static function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
