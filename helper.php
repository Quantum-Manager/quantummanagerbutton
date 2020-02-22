<?php

/**
 * @package    quantummanagerbutton
 * @author     Dmitry Tsymbal <cymbal@delo-design.ru>
 * @copyright  Copyright © 2019 Delo Design & NorrNext. All rights reserved.
 * @license    GNU General Public License version 3 or later; see license.txt
 * @link       https://www.norrnext.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class QuantummanagerbuttonHelper
{


	public static function loadLang()
	{
		Factory::getLanguage()->load('plg_editors-xtd_quantummanagerbutton', JPATH_ADMINISTRATOR);
	}


	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function getFieldsForScopes()
	{

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('params')))
			->from('#__extensions')
			->where( 'element=' . $db->quote('quantummanagerbutton'));
		$extension = $db->setQuery( $query )->loadObject();
		$params = json_decode($extension->params, JSON_OBJECT_AS_ARRAY);

		if(!isset($params['scopes']) || empty($params['scopes']) || count((array)$params['scopes']) === 0)
		{
			$scopes = self::defaultValues();
		}
		else
		{
			$scopes = $params['scopes'];
		}

		$output = [];

		foreach ($scopes as $scope)
		{
			$scope = (array)$scope;
			$output[$scope['id']] = [
				'title' => $scope['title'],
				'fieldsform' => $scope['fieldsform']
			];
		}

		return $output;
	}

	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function getTemplateListForScopes()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('params')))
			->from('#__extensions')
			->where( 'element=' . $db->quote('quantummanagerbutton'));
		$extension = $db->setQuery( $query )->loadObject();
		$params = json_decode($extension->params, JSON_OBJECT_AS_ARRAY);

		if(!isset($params['scopes']) || empty($params['scopes']) || count((array)$params['scopes']) === 0)
		{
			$scopes = self::defaultValues();
		}
		else
		{
			$scopes = $params['scopes'];
		}

		$output = [];

		foreach ($scopes as $scope)
		{
			$templatelist = [];
			$templatelistFromScope = $scope['templatelist'];
			foreach ($templatelistFromScope as $keyTemplate => $template)
			{
				$templatelist[] = $template['templatename'];
			}
			$scope = (array)$scope;
			$output[$scope['id']] = [
				'title' => $scope['title'],
				'templatelist' => $templatelist
			];
		}

		return $output;
	}


	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	public static function defaultValues()
	{
		$lang = Factory::getLanguage();
		$lang->load('plg_editors-xtd_quantummanagerbutton', JPATH_ADMINISTRATOR);
		$lang->load('com_quantummanager', JPATH_ROOT . '/administrator/components/com_quantummanager');

		return [
			'images' => (object)[
				'id' => 'images',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_IMAGES'),
				'template' => '<img src="{file}" alt="{alt}" width="{width}" height="{height}" />',
				'fieldsform' => [
					'fieldsform0' => [
						'nametemplate' => 'width',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_WIDTH_NAME'),
						'default' => '',
						'type' => 'number',
					],
					'fieldsform1' => [
						'nametemplate' => 'height',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_HEIGHT_NAME'),
						'default' => '',
						'type' => 'number',
					],
					'fieldsform3' => [
						'nametemplate' => 'alt',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_ALT_NAME'),
						'default' => '',
						'type' => 'text',
					]
				]
			],
			'docs' => (object)[
				'id' => 'docs',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_DOCS'),
				'template' => '<a href="{file}" target="_blank">{name}</a>',
				'fieldsform' => [
					'fieldsform0' => [
						'nametemplate' => 'name',
						'name' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_DOCS_FIELDSFORM_NAME_NAME'),
						'default' => Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_IMAGES_FIELDSFORM_DEFAULT_NAME'),
						'type' => 'text',
					],
				]
			],
			'music' => (object)[
				'id' => 'music',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_MUSIC'),
				'template' => '<audio controls src="{file}"> ' . Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_MUSIC_TEMPLATE_TEXT') . '</audio>',
				'fieldsform' => '',
			],
			'videos' => (object)[
				'id' => 'videos',
				'title' => Text::_('COM_QUANTUMMANAGER_SCOPE_VIDEOS'),
				'template' => '<video src="{file}" autoplay>' . Text::_('PLG_BUTTON_QUANTUMMANAGERBUTTON_SCOPES_VIDEOS_TEMPLATE_TEXT') . '</video>',
				'fieldsform' => '',
			]
		];
	}


}