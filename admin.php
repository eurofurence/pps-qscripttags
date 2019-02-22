<?php
/**
 * QScript Tags plugin for DokuWiki
 *
 * @license    MIT
 * @author     Eli Fenton
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 *
 */
class admin_plugin_qscripttags extends DokuWiki_Admin_Plugin {
	/** @type helper_plugin_qscripttags */
	protected $hlp;

	public function __construct() {
		/** @type helper_plugin_qscripttags $this ->hlp */
		$this->hlp = plugin_load('helper', 'qscripttags');
	}

	/**
	 * return sort order for position in admin menu
	 */
	public function getMenuSort() {
		return 140;
	}

	/**
	 * return prompt for admin menu
	 */
	public function getMenuText($language) {
		return $this->getLang('name');
	}

	/**
	 * handle user request
	 */
	public function handle() {
		global $INPUT;
		if ($INPUT->post->has('aldata')) {
			if (!$this->hlp->saveConfigFile($INPUT->post->str('aldata'))) {
				msg('Failed to save data', 1);
			}
			else {
				// Break the cache, so that all pages are regenerated.
				touch(DOKU_CONF."local.php");
			}
		}
	}

	/**
	 * output appropriate html
	 */
	public function html() {
		global $lang;
		$config = $this->hlp->loadConfigFile();

		$lines = preg_split('/\r?\n/', $config);
		$allTt = true;

		echo $this->locale_xhtml('admin_help');
		echo '<form action="" method="post" >';
		echo '<input type="hidden" name="do" value="admin" />';
		echo '<input type="hidden" name="page" value="qscripttags" />';
		echo '<textarea class="edit plugin-qscripttags__admintext" rows="15" cols="80" style="height: 500px" name="aldata">';
		echo formtext($config);
		echo '</textarea><br/><br/>';
		echo '<input type="submit" value="' . $lang['btn_save'] . '" class="button" />';
		echo '</form>';
	}
}
