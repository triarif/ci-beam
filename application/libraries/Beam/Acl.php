<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

// Set the include path and require the needed files
set_include_path(get_include_path() . PATH_SEPARATOR . realpath(APPPATH) . "/third_party");
require_once(APPPATH . '/third_party/Zend/Acl.php');
require_once(APPPATH . '/third_party/Zend/Acl/Role.php');
require_once(APPPATH . '/third_party/Zend/Acl/Resource.php');
		
class Acl
{
	// Set the instance variable
	var $CI, $acl;

	function __construct()
	{
		// Get the instance
		$this->CI =& get_instance();
	}
	
	function build()
	{
		$this->acl = new Zend_Acl();
		
		// Resources
		$this->CI->load->model('acl/resource_model');
		$rows = $this->CI->resource_model->get_list(1000);
		while(count($rows) > 0)
		{
			$row = array_shift($rows);
			if (empty($row->parent_name))
				$this->acl->addResource(new Zend_Acl_Resource($row->name));
			elseif ((!empty($row->parent_name) && $this->acl->has($row->parent_name)))
				$this->acl->addResource(new Zend_Acl_Resource($row->name), $row->parent_name);
			else
				array_push ($rows, $row);
		}
		
		// Roles
		$this->CI->load->model('acl/role_model');
		$result = $this->CI->role_model->get_list(1000);
		$rows = array();
		foreach($result as $row)
		{
			$rows[$row->id]['name'] = $row->name;
			$rows[$row->id]['parents'] = array();
			if (!empty($row->parent_name))
				$rows[$row->id]['parents'][$row->parent_order] = $row->parent_name;
		}
		$this->acl->addRole(new Zend_Acl_Role('Administrator'));
		while(count($rows) > 0)
		{
			$row = array_shift($rows);
			// If role exists, continue;
			if ($this->acl->hasRole($row['name'])) continue;			
			
			// Check if every role parents exists.
			$isParentOk = TRUE;
			foreach($row['parents'] as $parent_name)
			{
				if(! $this->acl->hasRole($parent_name))
				{
					$isParentOk = FALSE;
					break;
				}
			}
			if(empty($row['parents']))
				$this->acl->addRole(new Zend_Acl_Role($row['name']));
			elseif($isParentOk)
				$this->acl->addRole(new Zend_Acl_Role($row['name']), $row['parents']);
			else
				array_push($rows, $row);
		}
		
		// Rules
		$this->acl->allow('Administrator');
		$this->CI->load->model('acl/rule_model');
		$rows = $this->CI->rule_model->get_list();
		foreach($rows as $row)
		{
			if ($row->access == 'allow')
				$this->acl->allow($row->role_name, $row->resource_name);
			else
				$this->acl->deny($row->role_name, $row->resource_name);
		}
	}

	// Function to check if the current or a preset role has access to a resource
	function is_allowed($resource, $role = '')
	{
		// If resource not exists, default to 'deny'.
		if (!$this->acl->has($resource))
		{
			return FALSE;
		}
		// If role empty, try search the session.
		if (empty($role)) 
		{
			if (isset($this->CI->session->userdata['role'])) 
			{
				$role = $this->CI->session->userdata['role'];
			}
		}
		// If role empty or not exists, default to 'deny'.
		if (empty($role) || !$this->acl->hasRole($role)) 
		{
			return false;
		}
		return $this->acl->isAllowed($role, $resource);
	}
}