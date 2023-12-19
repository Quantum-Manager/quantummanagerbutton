<?php namespace Joomla\Plugin\Button\QuantumManagerButton\Field;

/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Component\QuantumManager\Administrator\Helper\QuantummanagerHelper;
use Joomla\Plugin\Button\QuantumManagerButton\Helper\ButtonHelper;


/**
 * @package     ${NAMESPACE}
 *
 * @since       version
 */
class QuantummanagerscopesinsertField extends SubformField
{


	/**
	 * @var string
	 */
	public $type = 'QuantumManagerScopesInsert';


	/**
	 * @return string
	 */
	public function getInput()
	{
		Factory::getLanguage()->load('com_quantummanager', JPATH_ROOT . '/administrator/components/com_quantummanager');

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

			$title = '';

			if (substr_count($scope->title, 'COM_QUANTUMMANAGER'))
			{
				$title = Text::_($scope->title);
			}

			$defaultTemplateList = '';
			$defaultFieldsform   = '';

			if (isset($defaultValues[$scope->id]))
			{
				$defaultTemplateList = $defaultValues[$scope->id]->templatelist;
				$defaultFieldsform   = json_encode($defaultValues[$scope->id]->fieldsform);
			}

			$scopesForInput['scopes' . $i] = [
				'title'        => $scope->title,
				'titleLabel'   => $scope->title,
				'id'           => $scope->id,
				'fieldsform'   => $findValue !== null ? $findValue['fieldsform'] : $defaultFieldsform,
				'templatelist' => $findValue !== null ? $findValue['templatelist'] : $defaultTemplateList,
			];

			$i++;
		}

		$this->value = $scopesForInput;
		$html        = parent::getInput();

		return $html;
	}


}