<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Under construction controller
 * Requires authentication
 *
 * @package    NINJA
 * @author     op5 AB
 * @license    GPL
 */
class Underconstruction_Controller extends Authenticated_Controller {

	public $model = false;

	public function __construct()
	{
		parent::__construct();
		$this->template->js_header = $this->add_view('js_header');
		$this->template->js_header->js = $this->xtra_js;
		$this->template->disable_refresh = true;
	}

	public function index()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Under construction');
	}

	public function schedule_downtime()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Monitoring » Schedule downtime');
	}

	public function performance_info()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Monitoring » Performance info');
	}

	public function scheduling_queue()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Monitoring » Scheduling queue');
	}

	public function trends()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Reporting » Trends');
	}

	public function schedule_reports()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Reporting » Schedule report');
	}

	public function alert_history()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Reporting » Alert history');
	}

	public function alert_summary()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Reporting » Alert summary');
	}

	public function notifications()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Reporting » Notifications');
	}

	public function event_log()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Reporting » Event log');
	}

	public function view_config()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Configuration » View config');
	}

	public function change_password()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Configuration » Change password');
	}

	public function backup_restore()
	{
		$this->template->content = '<div class="widget left w32">'.$this->translate->_('This page is not implemented yet. Stay tuned').'</div>';
		$this->template->title = $this->translate->_('Configuration » Backup/Restore');
	}
}