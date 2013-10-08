<?php
namespace Aoe\Linkhandler;

/***************************************************************
 * Copyright notice
 *
 * Copyright (c) 2008, Daniel Pötzinger <daniel.poetzinger@aoemedia.de>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Linkhandler to process custom linking to any kind of configured record.
 *
 * @author Daniel Poetzinger <daniel.poetzinger@aoemedia.de>
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 */
class LinkHandler {

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObjectRenderer;

	/**
	 * Global configuration defined in plugin.tx_linkhandler
	 * @var array
	 */
	protected $configuration;

	/**
	 * The configuration key that should be used for the current link
	 * @var string
	 */
	protected $configurationKey;

	/**
	 * The full link handler key (record:[config_index]:[ŧable]:[uid])
	 *
	 * @var string
	 */
	public $linkHandlerKey;

	/**
	 * All link parameters (including class name, page type, etc.)
	 *
	 * @var string
	 */
	public $linkParameters;

	/**
	 * The text that should be linked
	 *
	 * @var string
	 */
	public $linkText;

	/**
	 * Configuration that will be passed to the typolink function
	 * @var array
	 */
	protected $typolinkConfiguration;

	/**
	 * @var array
	 */
	protected $recordRow;

	/**
	 * @var string
	 */
	protected $recordTableName;

	/**
	 * @var int
	 */
	protected $recordUid;

	/**
	 * @var \Aoe\Linkhandler\Browser\TabHandlerFactory
	 */
	protected $tabHandlerFactory;

	/**
	 * @var \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController
	 */
	protected $tsfe;


	public function __construct() {
		$this->tsfe = $GLOBALS['TSFE'];
		$this->tabHandlerFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Aoe\\Linkhandler\\Browser\\TabHandlerFactory');
	}

	/**
	 * Process the link generation
	 *
	 * @param string $linktxt
	 * @param array $conf
	 * @param string $linkHandlerKeyword Define the identifier that an record is given
	 * @param string $linkHandlerValue Table and uid of the requested record like "tt_news:2"
	 * @param string $linkParams Full link params like "record:tt_news:2"
	 * @param \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer
	 * @return string
	 */
	public function main($linktxt, $conf, $linkHandlerKeyword, $linkHandlerValue, $linkParams, $contentObjectRenderer) {

		$this->linkText = $linktxt;
		$this->linkParameters = $linkParams;
		$this->linkHandlerKey = $linkHandlerKeyword . ':' . $linkHandlerValue;
		$this->contentObjectRenderer = $contentObjectRenderer;
		$this->configuration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_linkhandler.'];

		try {
			$generatedLink = $this->generateLink();
		} catch (\Exception $ex) {
			$generatedLink = $this->getErrorMessage($ex->getMessage());
		}

		return $generatedLink;
	}

	/**
	 * Generates a typolink by using the matching tab configuration
	 *
	 * @throws \Exception
	 * @return string
	 */
	protected function generateLink() {

		$tabsConfiguration = $this->tabHandlerFactory->buildTabConfigurationsFromTypoScript($this->configuration);
		$linkInfo = $this->tabHandlerFactory->getLinkInfoArrayFromMatchingHandler($this->linkHandlerKey, $tabsConfiguration);

		if (!count($linkInfo)) {
			throw new \Exception(sprintf('No matching tab handler could be found for link handler key %s.', $this->linkHandlerKey));
		}

		$this->configurationKey = $linkInfo['act'];
		$this->recordTableName = $linkInfo['recordTable'];
		$this->recordUid = $linkInfo['recordUid'];
		$this->initRecord();

		if (!is_array($this->typolinkConfiguration)) {
			throw new \Exception(sprintf('No linkhandler configuration was found for %s within plugin.tx_linkhandler.', $this->configurationKey));
		}

		if (!is_array($this->recordRow) && !$this->typolinkConfiguration['forceLink']) {
			return $this->linkText;
		}

		// Extract link params like "target", "css-class" or "title"
		$furtherLinkParams = str_replace($this->linkHandlerKey, '', $this->linkParameters);

		/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $localcObj */
		$localcObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer');
		$localcObj->start($this->recordRow, '');

		$this->typolinkConfiguration['parameter'] .= $furtherLinkParams;

		// Build the full link to the record
		return $localcObj->typoLink($this->linkText, $this->typolinkConfiguration);
	}

	/**
	 * @param string $message
	 * @return string
	 */
	protected function getErrorMessage($message) {
		return '<span style="color: red; font-weight: bold;">' . $message . '</span>';
	}

	/**
	 * Initializes the linked record and the record specific configuration.
	 */
	protected function initRecord() {

		if (is_array($this->configuration) && array_key_exists($this->configurationKey . '.', $this->configuration)) {

			$currentConfiguration = $this->configuration[$this->configurationKey . '.'];

			if (is_array($currentConfiguration) && array_key_exists('typolink.', $currentConfiguration)) {
				$this->typolinkConfiguration = $currentConfiguration['typolink.'];
			}
		}

		$this->recordRow = $this->tsfe->sys_page->checkRecord($this->recordTableName, $this->recordUid);
	}
}