SelectView
==========

This class provides a new Contao view that displays a DCA in select mode but with your custom buttons.

Usage
-----
	// config/config.php
	$GLOBALS['BE_MOD']['accounts']['user']['export'] = array('tl_user_export', 'exportUsers');

	// dca/tl_user.php
	$GLOBALS['TL_DCA']['tl_user']['list']['global_operations']['export'] = array
	(
		'label'               => 'Export users',
		'href'                => 'key=export',
		'class'               => 'header_theme_import',
		'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
	);

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
					'label' => 'Export to TXT',
					'callback' => 'exportTxt'
				),
				array
				(
					'name' => 'displaySelected',
					'label' => 'Display selected',
					'callback' => 'displaySelected'
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
		 * Export users to a TXT file
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
