<?php
/*
 * @package    plg_system_adminpath
 * @version    1.0.0
 * @author     Ilya A.Zhulin <ilya.zhulin@hotmail.com>
 * @copyright  ©Ilya A.Zhulin, 2026
 * @license    GNU General Public License version 2 or later;
 *
 * The last change: 12.02.2026, 10:33
 */

defined('_JEXEC') or die;

/**
 * Plg_system_adminpath script file.
 *
 * @package   plg_system_adminpath
 * @since     1.0.0
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;

class PlgSystemAdminPathInstallerScript
{
	/**
	 * Constructor
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function __construct(InstallerAdapter $adapter) {}

	/**
	 * Called before any type of action
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($route, InstallerAdapter $adapter)
	{
		// Проверка версии Joomla
		if (version_compare(JVERSION, '5.0', '<'))
		{
			throw new RuntimeException('This plugin requires Joomla 5.0 or later.');
		}

		// Проверка PHP версии
		if (version_compare(PHP_VERSION, '8.1', '<'))
		{
			throw new RuntimeException('This plugin requires PHP 8.1 or later.');
		}
	}

	/**
	 * Called after any type of action
	 *
	 * @param   string            $route    Which action is happening (install|uninstall|discover_install|update)
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($route, InstallerAdapter $adapter)
	{
		if ($route !== 'install')
		{
			return;
		}

		$app      = Factory::getApplication();
		$clientIp = (string) $app->getInput()->server->get('REMOTE_ADDR', '');

		if ($clientIp === '')
		{
			return;
		}

		$app->getLanguage()->load('plg_system_adminpath', JPATH_ADMINISTRATOR);

		$pluginId = 0;

		try
		{
			$record   = \Joomla\CMS\Extension\ExtensionHelper::getExtensionRecord('adminpath', 'plugin', null, 'system');
			$pluginId = $record ? (int) $record->extension_id : 0;
		}
		catch (\Throwable $e)
		{
			$pluginId = 0;
		}

		$settingsUrl = 'index.php?option=com_plugins&task=plugin.edit';

		if ($pluginId > 0)
		{
			$settingsUrl .= '&extension_id=' . $pluginId . '&add_ip=' . urlencode($clientIp);
		}

		$app->enqueueMessage(
			\Joomla\CMS\Language\Text::sprintf('PLG_SYSTEM_ADMINPATH_IP_PROMPT_MESSAGE', $clientIp, $settingsUrl),
			'notice'
		);
	}

	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install(InstallerAdapter $adapter)
	{
		$this->enablePlugin('system', 'adminpath');
	}

	private function enablePlugin($folder, $element)
	{
		try
		{
			$db = Factory::getContainer()->get('DatabaseDriver');

			$query = $db->getQuery(true)
				->update($db->quoteName('#__extensions'))
				->set($db->quoteName('enabled') . ' = 1')
				->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
				->where($db->quoteName('folder') . ' = ' . $db->quote($folder))
				->where($db->quoteName('element') . ' = ' . $db->quote($element));

			$db->setQuery($query);
			$db->execute();
			Factory::getApplication()->enqueueMessage(
				'Plugin enabled.',
				'info'
			);

		}
		catch (Exception $e)
		{
			// Логируем ошибку, но не прерываем установку
			Factory::getApplication()->enqueueMessage(
				'Notice: Could not auto-enable plugin. Please enable it manually in Plugin Manager.',
				'warning'
			);
		}
	}

	/**
	 * Called on update
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update(InstallerAdapter $adapter)
	{
		$this->enablePlugin('system', 'adminpath');
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 */
	public function uninstall(InstallerAdapter $adapter) {}
}
