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

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Plg_system_adminpath plugin.
 *
 * @package   plg_system_adminpath
 * @since     1.0.0
 */
class plgSystemPlg_system_adminpath extends CMSPlugin
{
    /**
     * Application object
     *
     * @var    CMSApplication
     * @since  1.0.0
     */
    protected $app;

    /**
     * Database object
     *
     * @var    JDatabaseDriver
     * @since  1.0.0
     */
    protected $db;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  1.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * onAfterInitialise.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterInitialise() {}

    /**
     * onAfterRoute.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterRoute() {}

    /**
     * onAfterDispatch.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterDispatch() {}

    /**
     * onAfterRender.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterRender()
    {
        // Access to plugin parameters
        $sample = $this->params->get('sample', '42');
    }

    /**
     * onAfterCompileHead.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterCompileHead() {}

    /**
     * OnAfterCompress.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterCompress() {}

    /**
     * onAfterRespond.
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAfterRespond() {}
}
