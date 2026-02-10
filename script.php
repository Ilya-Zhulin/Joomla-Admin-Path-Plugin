<?php
/*
 * @package    plg_system_adminpath
 * @version    __DEPLOY_VERSION__
 * @author     Ilya A.Zhulin <ilya.zhulin@hotmail.com>
 * @copyright  ©Ilya A.Zhulin, 2026
 * @license    GNU General Public License version 2 or later;
 *
 * The last change: 10.02.2026, 23:46
 */

defined('_JEXEC') or die;

/**
 * Plg_system_adminpath script file.
 *
 * @package   plg_system_adminpath
 * @since     1.0.0
 */
class plgSystemPlg_system_adminpathInstallerScript
{
    /**
     * Constructor
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     */
    public function __construct(JAdapterInstance $adapter) {}

    /**
     * Called before any type of action
     *
     * @param string $route Which action is happening (install|uninstall|discover_install|update)
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($route, JAdapterInstance $adapter) {}

    /**
     * Called after any type of action
     *
     * @param string $route Which action is happening (install|uninstall|discover_install|update)
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($route, JAdapterInstance $adapter) {}

    /**
     * Called on installation
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install(JAdapterInstance $adapter) {}

    /**
     * Called on update
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update(JAdapterInstance $adapter) {}

    /**
     * Called on uninstallation
     *
     * @param JAdapterInstance $adapter The object responsible for running this script
     */
    public function uninstall(JAdapterInstance $adapter) {}
}
