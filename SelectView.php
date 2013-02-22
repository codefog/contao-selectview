<?php

/**
 * selectview extension for Contao Open Source CMS
 * 
 * Copyright (C) 2013 Codefog
 * 
 * @package selectview
 * @link    http://codefog.pl
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Class SelectView
 * 
 * Provide a new back end view similar to "Edit multiple".
 */
class SelectView extends Backend
{

	/**
	 * Name of the table
	 * @var string
	 */
	protected $strTable;

	/**
	 * Available buttons
	 * @var array
	 */
	protected $arrButtons = array();

	/**
	 * Name of the current action
	 * @var string
	 */
	protected $strAction;

	/**
	 * Current version
	 * @var string
	 */
	public static $strVersion = '1.0.0';


	/**
	 * Initialize the object
	 * @param string
	 * @param array
	 * @throws Exception
	 */
	public function __construct($strTable, $arrButtons)
	{
		$this->strTable = $strTable;
		$this->arrButtons = $arrButtons;

		// Front end mode
		if (TL_MODE == 'FE')
		{
			throw new Exception('You must not use this class in front end mode!');
		}

		// No table
		if (!strlen($this->strTable))
		{
			throw new Exception('Please sepcify the table name!');
		}

		// No buttons
		if (!is_array($this->arrButtons) || empty($this->arrButtons))
		{
			throw new Exception('You have not specified any buttons!');
		}

		$this->import('Input');

		// Set current action
		if ($this->Input->post('FORM_SUBMIT') == 'tl_select')
		{
			foreach ($this->arrButtons as $arrButton)
			{
				if ($this->Input->post($arrButton['name']))
				{
					$this->strAction = $arrButton['name'];
				}
			}
		}
	}


	/**
	 * Return current action or selected IDs
	 * @param string
	 * @return mixed
	 */
	public function __get($strKey)
	{
		switch ($strKey)
		{
			case 'action':
				return $this->strAction;
				break;

			case 'ids':
				if ($this->strAction)
				{
					return $this->Input->post('IDS');
				}

				return array();
				break;
		}
	}


	/**
	 * Replace the default buttons
	 * @return string
	 */
	public function renderView()
	{
		$objDc = new DC_SelectView($this->strTable);
		$strBuffer = $objDc->runSelectMode();
		$strButtons = '<div class="tl_submit_container">';

		// Generate buttons
		foreach ($this->arrButtons as $arrButton)
		{
			$strButtons .= sprintf('<input id="%s" class="tl_submit" type="submit" value="%s" accesskey="%s" name="%s">  ',
									$arrButton['name'],
									specialchars($arrButton['label']),
									($arrButton['accesskey'] ? $arrButton['accesskey'] : substr($arrButton['name'], 0, 1)),
									$arrButton['name']);
		}

		return preg_replace('/<div class="tl_submit_container">\s+(<input type="submit"(.*)?>\s+)+\s*<\/div>/', $strButtons . '</div>', $strBuffer);
	}
}


/**
 * Class DC_SelectView
 * 
 * Provide methods to display DCA in select mode.
 */
class DC_SelectView extends DC_Table
{

	/**
	 * Return records in the select mode
	 * @return string
	 */
	public function runSelectMode()
	{
		$this->import('Input');
		$this->Input->setGet('act', 'select');
		return parent::showAll();
	}
}
