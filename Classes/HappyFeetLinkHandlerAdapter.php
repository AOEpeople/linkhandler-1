<?php
namespace Cobweb\Linkhandler;

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

use TYPO3\CMS\Core\Utility\StringUtility;

/**
 * Adapter which enables the usage of old "happyfeet" links (which were created in TYPO3 6.2 with old aoe-linkhandler-extension)
 *
 * "happyfeet"-links, which were created/edited in TYPO3 7.6 (which use this linkhandler-extension) have this syntax:
 *  - Syntax:  record:[key]:[tableName]:[recordUid]
 *  - Example: record:happyfeet:tx_happyfeet_domain_model_footnote:2
 *    ==> This syntax is fine and used by this extension
 *
 * "happyfeet"-links, which were created in TYPO3 6.2 (which used the old aoe-linkhandler-extension) have this syntax:
 *  - Syntax:  happyfeet:[tableName]:[recordUid]
 *  - Example: happyfeet:tx_happyfeet_domain_model_footnote:2
 *    ==> This syntax is (normally) not supported by this extension. By using this class, we now support that syntax
 */
class HappyFeetLinkHandlerAdapter
{
    const HAPPY_FEET_KEY = 'happyfeet';
    const HAPPY_FEET_TABLE = 'tx_happyfeet_domain_model_footnote';
    const LINK_HANDLER_KEYWORD = 'record';

    /**
     * Add typolink handler for old "happyfeet"-links (which were created in TYPO3 6.2 with old aoe-linkhandler-extension)
     *
     * @return void
     */
    public static function addTypolinkHandlerForHappyFeetLinks()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['typolinkLinkHandler'][self::HAPPY_FEET_KEY] =
            \Cobweb\Linkhandler\TypolinkHandler::class;
    }

    /**
     * fix linkHandlerKeyword for old "happyfeet"-links (which were created in TYPO3 6.2 with old aoe-linkhandler-extension)
     *
     * @param string $linkHandlerKeyword
     * @return string
     */
    public static function fixLinkHandlerKeyword($linkHandlerKeyword)
    {
        if ($linkHandlerKeyword === self::HAPPY_FEET_KEY) {
            $linkHandlerKeyword = self::LINK_HANDLER_KEYWORD;
        }
        return $linkHandlerKeyword;
    }

    /**
     * fix linkhandlerType for old "happyfeet"-links (which were created in TYPO3 6.2 with old aoe-linkhandler-extension):
     *
     * @param string $tokenValue
     * @param string $type
     * @return string
     */
    public static function fixLinkHandlerType($tokenValue, $type)
    {
        if (StringUtility::beginsWith(strtolower($tokenValue), self::HAPPY_FEET_KEY . ':')) {
            $type = 'tx_linkhandler';
        }
        return $type;
    }

    /**
     * fix URL for old "happyfeet"-links (which were created in TYPO3 6.2 with old aoe-linkhandler-extension):
     *  - URL-syntax of old "happyfeet"-links:        [key]:[tableName]:[recordUid]
     *  - URL-syntax, which is required:       record:[key]:[tableName]:[recordUid]
     *
     * @param string $url
     * @return string
     */
    public static function fixLinkHandlerUrl($url)
    {
        if (StringUtility::beginsWith(strtolower($url), self::HAPPY_FEET_KEY . ':')) {
            $url = self::LINK_HANDLER_KEYWORD . ':' . $url;
        }
        return $url;
    }

    /**
     * fix linkHandlerValue for old "happyfeet"-links (which were created in TYPO3 6.2 with old aoe-linkhandler-extension):
     *  - linkHandlerValue-syntax of old "happyfeet"-links:       [tableName]:[recordUid]
     *  - linkHandlerValue-syntax, which is required:       [key]:[tableName]:[recordUid]
     *
     * @param string $linkHandlerValue
     * @return string
     */
    public static function fixLinkHandlerValue($linkHandlerValue)
    {
        if (StringUtility::beginsWith(strtolower($linkHandlerValue), self::HAPPY_FEET_TABLE . ':')) {
            $linkHandlerValue = self::HAPPY_FEET_KEY . ':' . $linkHandlerValue;
        }
        return $linkHandlerValue;
    }
}
