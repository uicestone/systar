<?php
class SS_Router extends CI_Router{
	
	function __construct() {
		parent::__construct();
	}
	
	function _validate_request($segments)
	{
		if (count($segments) == 0)
		{
			return $segments;
		}

		if(file_exists(APPPATH.'controllers/'.COMPANY_TYPE.'/'.COMPANY_CODE.'/'.COMPANY_CODE.'_'.$segments[0].EXT))
		{
			$this->directory=COMPANY_TYPE.'/'.COMPANY_CODE.'/';
			$segments[0]=COMPANY_CODE.'_'.$segments[0];
			//TODO 直接修改了 $segments，将使segments[0]与url第一字段可能不一致。继承Router::_set_request也许可以解决
			return $segments;
			
		}
		
		elseif(file_exists(APPPATH.'controllers/'.COMPANY_TYPE.'/'.COMPANY_CODE.'/'.$segments[0].EXT))
		{
			$this->directory=COMPANY_TYPE.'/'.COMPANY_CODE.'/';
			return $segments;
			
		}
		
		elseif(file_exists(APPPATH.'controllers/'.COMPANY_TYPE.'/'.COMPANY_CODE.'_'.$segments[0].EXT))
		{
			$this->directory=COMPANY_TYPE.'/'.COMPANY_CODE.'/';
			$this->class=COMPANY_CODE.'_'.$segments[0];
			return $segments;
			
		}
		
		elseif(file_exists(APPPATH.'controllers/'.COMPANY_TYPE.'/'.$segments[0].EXT)){
			$this->directory=COMPANY_TYPE.'/';
			return $segments;
		}
		
		// Does the requested controller exist in the root folder?
		if (file_exists(APPPATH.'controllers/'.$segments[0].EXT))
		{
			return $segments;
		}

		// Is the controller in a sub-folder?
		if (is_dir(APPPATH.'controllers/'.$segments[0]))
		{
			// Set the directory and remove it from the segment array
			$this->set_directory($segments[0]);
			$segments = array_slice($segments, 1);

			if (count($segments) > 0)
			{
				// Does the requested controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$segments[0].'.php'))
				{
					if ( ! empty($this->routes['404_override']))
					{
						$x = explode('/', $this->routes['404_override']);

						$this->set_directory('');
						$this->set_class($x[0]);
						$this->set_method(isset($x[1]) ? $x[1] : 'index');

						return $x;
					}
					else
					{
						show_404($this->fetch_directory().$segments[0]);
					}
				}
			}
			else
			{
				// Is the method being specified in the route?
				if (strpos($this->default_controller, '/') !== FALSE)
				{
					$x = explode('/', $this->default_controller);

					$this->set_class($x[0]);
					$this->set_method($x[1]);
				}
				else
				{
					$this->set_class($this->default_controller);
					$this->set_method('index');
				}

				// Does the default controller exist in the sub-folder?
				if ( ! file_exists(APPPATH.'controllers/'.$this->fetch_directory().$this->default_controller.'.php'))
				{
					$this->directory = '';
					return array();
				}

			}

			return $segments;
		}
		
		// If we've gotten this far it means that the URI does not correlate to a valid
		// controller class.  We will now see if there is an override
		if ( ! empty($this->routes['404_override']))
		{
			$x = explode('/', $this->routes['404_override']);

			$this->set_class($x[0]);
			$this->set_method(isset($x[1]) ? $x[1] : 'index');

			return $x;
		}


		// Nothing else to do at this point but show a 404
		show_404($segments[0]);
	}
}
?>
