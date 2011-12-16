<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Kamil Kuzminski 2011
 * @author     Kamil Kuzminski <http://qzminski.com>
 * @package    Backend
 * @license    LGPL
 * @filesource
 */


/**
 * Add a new global operation button
 */
$GLOBALS['TL_DCA']['tl_user']['list']['global_operations']['export'] = array
(
	'label'               => 'Export users',
	'href'                => 'key=export',
	'class'               => 'header_theme_import',
	'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
);


/**
 * Class tl_user_export
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Kamil Kuzminski 2011
 * @author     Kamil Kuzminski <http://qzminski.com>
 * @package    Backend
 */
class tl_user_export extends Backend
{

	/**
	 * Export users in many ways
	 * @param object
	 * @return string
	 */
	public function exportUsers(DataContainer $dc)
	{
		if ($this->Input->get('key') != 'export')
		{
			$this->redirect($this->getReferer());
		}

		// Form buttons
		$arrButtons = array
		(
			array
			(
				'name' => 'exportTxt',
				'label' => 'Export to TXT'
			),
			array
			(
				'name' => 'displaySelected',
				'label' => 'Display selected'
			)
		);

		$objSelect = new SelectView($dc->table, $arrButtons);

		// Export users to a TXT file
		if ($objSelect->action == 'exportTxt')
		{
			$this->exportUsersToTxt($objSelect->ids);
		}

		// Display users ID on the 
		if ($objSelect->action == 'displaySelected')
		{
			$_SESSION['TL_INFO'][] = 'You have selected ' . count($objSelect->ids) . ' user(s) ID: ' . implode(', ', $objSelect->ids) . '.';
			$this->redirect($this->getReferer());
		}

		return $objSelect->renderView();
	}


	/**
	 * Export users to TXT file
	 * @param array
	 */
	public function exportUsersToTxt($arrIds)
	{
		$this->import('Database');
		$objFile = new File('tl_files/users.txt');
		$objFile->truncate();

		// Get users
		$objUsers = $this->Database->execute("SELECT * FROM tl_user WHERE id IN(" . implode(',', array_map('intval', $arrIds)) . ")");

		while ($objUsers->next())
		{
			$strRow = $objUsers->name . ' (' . $objUsers->username . ') - ' . $objUsers->id;
			$objFile->append($strRow);
		}

		$objFile->close();

		// Download the file
		$GLOBALS['TL_CONFIG']['allowedDownload'] .= ',txt';
		$this->sendFileToBrowser('tl_files/users.txt');
	}
}

?>