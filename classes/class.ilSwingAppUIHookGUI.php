<?php
// Copyright (c) 2019 Institut fuer Lern-Innovation, Friedrich-Alexander-Universitaet Erlangen-Nuernberg, GPLv3, see LICENSE

/**
 * User interface hook class
 *
 * @author Fred Neumann <fred.neumann@fau.de>
 */
class ilSwingAppUIHookGUI extends ilUIHookPluginGUI
{
	/** @var ilCtrl $ctrl */
	protected $ctrl;

	/** @var ilTabsGUI $tabs */
	protected $tabs;

	/** @var  ilSwingAppPlugin $plugin_object */
	protected $plugin_object;

	/**
	 * Modify GUI objects, before they generate ouput
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param array $a_par array of parameters (depend on $a_comp and $a_part)
	 */
	public function modifyGUI($a_comp, $a_part, $a_par = array())
	{
		switch ($a_part)
		{
			//case 'tabs':
			case 'sub_tabs':

				// must be done here because ctrl and tabs are not initialized for all calls
				global $DIC;
				$this->ctrl = $DIC->ctrl();
				$this->tabs = $DIC->tabs();

				// Standard meta data editor is shown
				if ($this->ctrl->getCmdClass() == 'ildclexportgui')
				{
					$this->saveTabs('ildclexportgui');
					$this->modifyExport();
				}

				 // publishing page is shown
				if (in_array($this->ctrl->getCmdClass(), array('ilswingapppublishgui')))
				{
					$this->restoreTabs('ildclexportgui');
				}

				break;

			default:
				break;
		}
	}

	/**
	 * Save the tabs for reuse on the plugin pages
	 * @param string context for which the tabs should be saved
	 */
	protected function saveTabs($a_context)
	{
		$_SESSION['SwingApp'][$a_context]['TabTarget'] = $this->tabs->target;
		$_SESSION['SwingApp'][$a_context]['TabSubTarget'] = $this->tabs->sub_target;
	}

	/**
	 * Restore the tabs for reuse on the plugin pages
	 * @param string context for which the tabs should be saved
	 */
	protected function restoreTabs($a_context)
	{
		// reuse the tabs that were saved from the parent gui
		if (isset($_SESSION['SwingApp'][$a_context]['TabTarget']))
		{
			$this->tabs->target = $_SESSION['SwingApp'][$a_context]['TabTarget'];
		}
		if (isset($_SESSION['SwingApp'][$a_context]['TabSubTarget']))
		{
			$this->tabs->sub_target = $_SESSION['SwingApp'][$a_context]['TabSubTarget'];
		}

		if ($a_context == 'ildclexportgui')
		{
			foreach ($this->tabs->target as $td)
			{
				if (strpos(strtolower($td['link']),'ildclexportgui') !== false)
				{
					// this works when done in handler for the sub_tabs
					// because the tabs are rendered after the sub tabs
					$this->tabs->activateTab($td['id']);
				}
			}
		}
	}

    /**
     * Check if function is allowed
     * @return bool
     */
	protected function isAllowed()
    {
        $type = ilObject::_lookupType($_GET['ref_id'], true);
        return $this->plugin_object->isAllowedType($type);
    }

	/**
	 * Modify the toolbar of the meta data editor
	 */
	protected function modifyExport()
	{
	    if ($this->isAllowed())
        {
            $this->plugin_object->includeClass('class.ilSwingAppPublishGUI.php');
            $gui = new ilSwingAppPublishGUI();
            $gui->modifyExportToolbar();
        }
	}
}
?>