<?php defined('SYSPATH') OR die('No direct access allowed.');

class Host_Model extends Model {
	private $auth = false;

	public function __construct()
	{
		parent::__construct();
		$this->auth = new Nagios_auth_Model();
	}

	/**
	 * Fetch all onfo on a host. The returned object
	 * will contain all database fields for the host object.
	 * @param $name The host_name of the host
	 * @param $id The id of the host
	 * @return Host object on success, false on errors
	 */
	public function get_hostinfo($name=false, $id=false)
	{

		$id = (int)$id;
		$name = trim($name);

		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_info = false;

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration')
					->where('host', array('id' => $id));
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $this->auth->hosts_r)) {
				return false;
			} else {
				$host_info = $this->db
					->select('*, (UNIX_TIMESTAMP() - last_state_change) AS duration')
					->getwhere('host', array('host_name' => $name));
			}
		} else {
			return false;
		}
		return $host_info !== false ? $host_info->current() : false;
	}

	/**
	 * Determine if user is authorized to view info on a specific host.
	 * Accepts either hostID or host_name as input
	 *
	 * @param $name The host_name of the host.
	 * @param $id The id of the host
	 * @return True if authorized, false if not.
	 */
	public function authorized_for($name=false, $id=false)
	{
		$id = (int)$id;
		$name = trim($name);
		$is_auth = false;

		$auth_hosts = $this->auth->get_authorized_hosts();

		if (!empty($id)) {
			if (!array_key_exists($id, $auth_hosts)) {
				return false;
			}
		} elseif (!empty($name)) {
			if (!array_key_exists($name, $auth->hosts_r)) {
				return false;
			}
		} else {
			return false;
		}
		return true;
	}

	/**
	*
	*	Fetch host info filtered on specific field and value
	*/
	public function get_where($field=false, $value=false, $limit=false)
	{
		if (empty($field) || empty($value)) {
			return false;
		}
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_ids = array_keys($auth_hosts);
		$host_info = $this->db
			->from('host')
			->like($field, $value)
			->in('id', $host_ids)
			->limit($limit)
			->get();
		return $host_info;
	}

	/**
	*	Search through several fields for a specific value
	*/
	public function search($value=false, $limit=false)
	{
		if (empty($value)) return false;
		$auth_hosts = $this->auth->get_authorized_hosts();
		$host_ids = array_keys($auth_hosts);
		$host_info = $this->db
			->select('DISTINCT *')
			->from('host')
			->orlike(
				array(
					'host_name' => $value,
					'alias' => $value,
					'display_name' => $value,
					'address' => $value
				)
			)
			->in('id', $host_ids)
			->limit($limit)
			->get();
		return $host_info;
	}

	/**
	*	Fetch parents for a specific host
	*/
	public function get_parents($host_name=false)
	{
		if (empty($host_name))
			return false;
		$host_query = $this->auth->authorized_host_query();
		if ($host_query === true) {
			# don't use auth_host fields etc
			$auth_host_alias = 'h';
			$auth_from = ', host AS '.$auth_host_alias;
			$auth_where = ' AND ' . $auth_host_alias . ".host_name = '" . $host_name . "'";
		} else {
			$auth_host_alias = $host_query['host_field'];
			$auth_from = ' ,'.$host_query['from'];
			$auth_where = ' AND '.sprintf($host_query['where'], "'".$host_name."'");
		}
		$sql = "SELECT parent.* " .
			"FROM " .
				"host_parents AS hp, " .
				"host AS parent " . $auth_from .
			" WHERE ".
				$auth_host_alias . ".id=hp.host " . $auth_where .
				" AND parent.id=hp.parents " .
			"ORDER BY parent.host_name";
		$result = $this->db->query($sql);
		return $result;
	}

	/**
	 * Fetch hosts for current user and return
	 * array of host IDs
	 * @return array host IDs or false
	 */
	public static function authorized_hosts()
	{
		# fetch hosts for current user
		$auth = new Nagios_auth_Model();
		$user_hosts = $auth->get_authorized_hosts();
		$hostlist = array_keys($user_hosts);
		if (!is_array($hostlist) || empty($hostlist)) {
			return false;
		}
		sort($hostlist);
		return $hostlist;
	}

}
