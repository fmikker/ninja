<?php defined('SYSPATH') OR die('No direct access allowed.');

require_once('op5/log.php');

/**
 * Base Controller.
 *
 * Sets necessary objects like session and database
 *
 *  op5, and the op5 logo are trademarks, servicemarks, registered servicemarks
 *  or registered trademarks of op5 AB.
 *  All other trademarks, servicemarks, registered trademarks, and registered
 *  servicemarks mentioned herein may be the property of their respective owner(s).
 *  The information contained herein is provided AS IS with NO WARRANTY OF ANY
 *  KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A
 *  PARTICULAR PURPOSE.
 */
class Base_Controller extends Template_Controller {

	/**
	 * @var NoticeManager_Model
	 */
	public $notices;

	public function __construct() {
		parent::__construct();
		$this->notices = new NoticeManager_Model();
	}

	protected function redirect ($controller, $method = null, array $parameters = array()) {
		$url = LinkProvider::factory()->get_url($controller, $method, $parameters);

		if (Event::has_run('system.send_headers')) {
			op5log::instance('ninja')->log('notice', "Attempted redirect to '$url' but headers are already sent.");
			return false;
		}

		Event::run('system.redirect', $url);

		header('HTTP/1.1 302 Found');
		header('Location: ' . $url);
		exit(0);
	}

	/**
	 * Clean up print notifications
	 *
	 * If we want to regenerate the list of print notifiactions, we can simply clean it up
	 */
	protected function _clear_print_notification() {
		if(!$this->template instanceof View) {
			op5log::instance('ninja')->log('debug', 'You tried to clear print notifications without having a view');
			return;
		}
		$this->template->print_notifications = array();
	}

	protected function _add_print_notification($notification) {
		if(!$this->template instanceof View) {
			op5log::instance('ninja')->log('debug', 'You tried to add a print notification without having a view: '.$notification);
			return;
		}
		$this->template->print_notifications[] = $notification;
	}

	/**
         * Verify access to a given action.
         * If no access, throw a Kohana_User_Exception
         *
         * This method returns if access is allowed, setting $this->access_messages
         * and $this->access_perfdata.
         *
         * If not access is allowed, throw an exception, to break out of normal
         * execution path, and render a access denied-page.
         */
	protected function _verify_access($action, $args = array()) {
		$access = $this->mayi->run(
			$action, $args, $messages,
			$this->access_perfdata
		);

		if ($access) {
			foreach ($messages as $msg) {
				$this->notices[] = new InformationNotice_Model($msg);
				// Since the messages are published depending
				// on action instead of target, we should add
				// all messages as print_notifications as well
				$this->_add_print_notification($msg);
			}
		} elseif (!Auth::instance()->get_user()->logged_in()) {
			$this->redirect('auth', 'login', array(
				'uri' => Router::$complete_uri
			));
		} else {
			throw new Kohana_Reroute_Exception('Auth', '_no_access', array(
				$messages,
				$action
			));
		}
	}
}
