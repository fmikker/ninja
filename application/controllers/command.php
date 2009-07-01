<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * CMD controller
 *
 * Requires authentication. See the helper nagioscmd for more info.
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Command_Controller extends Authenticated_Controller
{
	private $command_id = false;
	private $cmd_params = array();
	private $csrf_token = false;

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Initializes a page with the correct view, java-scripts and css
	 * @param $view The name of the theme-page we want to print
	 */
	protected function init_page($view)
	{
		$this->template->content = $this->add_view($view);
		$this->template->js_header = $this->add_view('js_header');
		$this->template->css_header = $this->add_view('css_header');
	}

	/**
	 * Request a command to be submitted
	 * This method prints input fields to be selected for the
	 * named command.
	 * @param $name The requested command to run
	 * @param $parameters The parameters (host_name etc) for the command
	 */
	public function submit($name = false)
	{
		$this->init_page('command/request');

		if ($name === false) {
			$name = $this->input->get('cmd_typ');
		}

		$params = array();
		foreach ($_GET as $k => $v) {
			switch ($k) {
			 case 'host':
			 case 'hostgroup':
			 case 'servicegroup':
				$params[$k . '_name'] = $v;
				break;
			 default:
				$params[$k] = $v;
			}
		}

		$command = new Command_Model;
		$info = $command->get_command_info($name, $params);

		switch ($name) {
		 case 'SCHEDULE_HOST_CHECK':
		 case 'SCHEDULE_SVC_CHECK':
		 case 'SCHEDULE_HOST_SVC_CHECKS':
			$info['params']['force'] = array
				('type' => 'checkbox',
				 'default' => true,
				 'name' => 'Force Check',
				 );
			break;
		 case 'PROCESS_HOST_CHECK_RESULT':
		 case 'PROCESS_SERVICE_CHECK_RESULT':
			$info['params']['perfdata'] = array
				('type' => 'string',
				 'size' => 100,
				 'name' => 'Performance data');
			break;
		}

		$this->template->content->requested_command = $name;
		$this->template->content->info = $info;

		if (is_array($info)) foreach ($info as $k => $v) {
			$this->template->content->$k = $v;
		}
	}

	protected function get_array_var($ary, $k, $dflt = false)
	{
		if (is_array($k)) {
			if (count($k) === 1)
				$k = array_pop($k);
		}

		if (is_array($k))
			return false;

		if (isset($ary[$k]))
			return $ary[$k];

		return $dflt;
	}

	/**
	 * Takes the command parameters given by the "submit" function
	 * and creates a Nagios command that gets fed to Nagios through
	 * the external command pipe.
	 */
	public function commit()
	{
		$this->init_page('command/commit');
		$cmd = $_REQUEST['requested_command'];
		$this->template->content->requested_command = $cmd;

		$param = $this->get_array_var($_REQUEST, 'cmd_param', array());
		switch ($cmd) {
		 case 'SCHEDULE_HOST_CHECK':
		 case 'SCHEDULE_SVC_CHECK':
		 case 'SCHEDULE_HOST_SVC_CHECKS':
			if (!empty($param['force'])) {
				echo "Forcing check<br />\n";
				unset($param['force']);
				$cmd = 'SCHEDULE_FORCED' . substr($cmd, strlen("SCHEDULE"));
			}

			break;
		 case 'PROCESS_HOST_CHECK_RESULT':
		 case 'PROCESS_SERVICE_CHECK_RESULT':
			if (!empty($param['perfdata']) && !empty($param['plugin_output'])) {
				$param['plugin_output'] .= "|$param[perfdata]";
				unset($param['perfdata']);
			}
			break;
		}

		$info = nagioscmd::cmd_info($cmd);
		$template = explode(';', $info['template']);
		for ($i = 1; $i < count($template); $i++) {
			$k = $template[$i];
			if (isset($param[$k])) {
				$template[$i] = $param[$k];
				unset($param[$k]);
			}
		}
		$ncmd = join(';', $template);

		$pipe = "/opt/monitor/var/rw/nagios.cmd";
		$nagconfig = System_Model::parse_config_file("/opt/monitor/etc/nagios.cfg");
		if (isset($nagconfig['command_file'])) {
			$pipe = $nagconfig['command_file'];
		}

		echo Kohana::debug($_REQUEST);
		echo Kohana::debug($ncmd);
		die();
		$this->template->content->result = nagioscmd::submit_to_nagios($ncmd, $pipe);
	}

	/**
	 * Display "You're not authorized" message
	 */
	public function unauthorized()
	{
		$this->template->content = $this->add_view('command/unauthorized');
		$this->template->content->error_message = $this->translate->_('You are not authorized to submit the specified command.');
		$this->template->content->error_description = $this->translate->_('Read the section of the documentation that deals with authentication and authorization in the CGIs for more information.');
		$this->template->content->return_link_lable = $this->translate->_('Return from whence you came');
	}

	/**
	 * Show info to user when use_authentication is disabled in cgi.cfg.
	 */
	public function use_authentication_off()
	{
		$this->template->content = $this->add_view('command/use_authentication_off');
		$this->template->content->error_msg = $this->translate->_('Error: Authentication is not enabled!');
		$this->template->content->error_description = $this->translate->_("As a safety precaution, commands aren't allowed when authentication is turned off.");
	}
}
