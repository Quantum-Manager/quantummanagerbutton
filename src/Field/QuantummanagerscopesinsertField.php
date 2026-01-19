<?php

namespace Joomla\Plugin\Button\QuantumManagerButton\Field;

/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\Factory;
use Joomla\Component\QuantumManager\Administrator\Helper\QuantummanagerHelper;
use Joomla\Plugin\Button\QuantumManagerButton\Helper\ButtonHelper;

class QuantummanagerscopesinsertField extends SubformField
{

	protected $type = 'Quantummanagerscopesinsert';

	public function getInput()
	{
		Factory::getApplication()->getLanguage()->load('com_quantummanager', JPATH_ROOT . '/administrator/components/com_quantummanager');

		$scopesForInput = [];
		$currentValue   = $this->value;
		$scopes         = QuantummanagerHelper::getAllScope('all');
		$defaultValues  = ButtonHelper::defaultValues();
		$i              = 0;

		foreach ($scopes as $scope)
		{

			if ($scope->id === 'sessionroot')
			{
				continue;
			}

			$findValue = null;

			if (is_array($currentValue) && count($currentValue) > 0)
			{
				foreach ($currentValue as $value)
				{
					if ($value['id'] === $scope->id)
					{
						$findValue = $value;
					}
				}
			}

			$defaultTemplateList = '';
			$defaultFieldsForm   = '';

			if (isset($defaultValues[$scope->id]))
			{
				$defaultTemplateList = $defaultValues[$scope->id]->templatelist;
				$defaultFieldsForm   = json_encode($defaultValues[$scope->id]->fieldsform);
			}

			$scopesForInput['scopes' . $i] = [
				'title'        => $scope->title,
				'titleLabel'   => $scope->title,
				'id'           => $scope->id,
				'fieldsform'   => $findValue !== null ? $findValue['fieldsform'] : $defaultFieldsForm,
				'templatelist' => $findValue !== null ? $findValue['templatelist'] : $defaultTemplateList,
			];

			$i++;
		}

		$this->value = $scopesForInput;

		return parent::getInput();
	}

}