<?php
	/*
		counter-strike 1.6 server info cheker 
		
		create by: umprex
		date: 11.01:2015
		
		#### how use ####
		#
		# $server = new cstrike('ip', port);
		# if ip has been validate, $server->info return array with information about this server
		# else $server->info error with messege of problem
		#
		#### how use ####
	*/
	
	class cstrike
	{
		function __construct($ip, $port)
		{
			$this->err 		= false;
			$this->info 	= false;
			$this->status	= false;
			
			if(filter_var($ip, FILTER_VALIDATE_IP))
			{	
				$this->ip 	= $ip;
			}
			else
			{
				$this->err 	= true;
			}
			
			if(is_numeric($port))
			{
				$this->port = $port;
			}
			else
			{
				$this->err	= true;
			}
			
			$this->timeout 	= 2;
			$this->cmd		= "\xFF\xFF\xFF\xFF\x54";
			
			if(!$this->err)
			{
				$this->connection();
			}
			else
			{
				$this->info = 'ip validation errors';
			}
		}
		
		function connection()
		{
			if($fsock = fsockopen("udp://" . $this->ip, $this->port, $errno, $errstr)) 
			{
			    socket_set_timeout($fsock, $this->timeout);
				
			    fwrite($fsock, $this->cmd);
			    $fsockstatus = fread($fsock, 1400);
			    fclose($fsock);

			    $this->status = ($fsockstatus ? true : false);
				
			    if ($this->status == true)
				{
			        $this->info['server_type'] = $fsockstatus[4];
			        $fsockstatus = substr($fsockstatus, 5);
					
			        if ($this->info['server_type'] == 'm') 
					{
			            $this->info['address']     = empty($this->cs_get_value_string($fsockstatus)) . "{$this->ip}:{$this->port}";
			            $this->info['hostname']    = htmlentities($this->cs_get_value_string($fsockstatus));
			            $this->info['map']         = $this->cs_get_value_string($fsockstatus);
			            $this->info['gamedir']     = $this->cs_get_value_string($fsockstatus);
			            $this->info['description'] = $this->cs_get_value_string($fsockstatus);
			            $this->info['players']     = $this->cs_get_value_byte($fsockstatus);
			            $this->info['max']         = $this->cs_get_value_byte($fsockstatus);
			            $this->info['protocol']    = $this->cs_get_value_byte($fsockstatus);
			            $this->info['lan']         = $this->cs_get_value_byte($fsockstatus);
			            $this->info['os']          = $fsockstatus[0];
			            $fsockstatus               = substr($fsockstatus, 1);
			            $this->info['password']    = $this->cs_get_value_byte($fsockstatus);
			            $this->info['is_mod']      = $this->cs_get_value_byte($fsockstatus);
			            $this->info['url_info']    = $this->cs_get_value_string($fsockstatus);
			            $this->info['url_down']    = $this->cs_get_value_string($fsockstatus);
			            $this->info['unused']      = $this->cs_get_value_string($fsockstatus);
			            $this->info['mod_version'] = $this->cs_get_value_lint($fsockstatus);
			            $this->info['mod_size']    = $this->cs_get_value_lint($fsockstatus);
			            $this->info['sv_only']     = $this->cs_get_value_byte($fsockstatus);
			            $this->info['cl']          = $this->cs_get_value_byte($fsockstatus);
			            $this->info['secure']      = $this->cs_get_value_byte($fsockstatus);
			            $this->info['bots']        = $this->cs_get_value_byte($fsockstatus);
			        }
			        elseif ( $this->info['server_type'] == 'I' ) 
					{
			            $this->info['address']     = "{$this->ip}:{$this->port}";
			            $this->info['protocol']    = $this->cs_get_value_byte($fsockstatus);
			            $this->info['hostname']    = htmlentities($this->cs_get_value_string($fsockstatus));
			            $this->info['map']         = $this->cs_get_value_string($fsockstatus);
			            $this->info['gamedir']     = $this->cs_get_value_string($fsockstatus);
			            $this->info['description'] = $this->cs_get_value_string($fsockstatus);
			            $this->info['app_id']      = $this->cs_get_value_sint($fsockstatus);
			            $this->info['players']     = $this->cs_get_value_byte($fsockstatus);
			            $this->info['max']         = $this->cs_get_value_byte($fsockstatus);
			            $this->info['bots']        = $this->cs_get_value_byte($fsockstatus);
			            $this->info['lan']         = $this->cs_get_value_byte($fsockstatus);
			            $this->info['os']          = $this->cs_get_value_string($fsockstatus);
			            $this->info['password']    = $this->cs_get_value_byte($fsockstatus);
			            $this->info['secure']      = $this->cs_get_value_byte($fsockstatus);
			            $this->info['version']     = $this->cs_get_value_string($fsockstatus);
			        }
			    }
				else
				{
					$this->info = 'connection error';
				}
			}
		}
		
		
		function cs_get_value_string( &$data ) 
		{
			$temp = '';
			$i = 0;
			while ( ord( $data[ $i ] ) != 0 ){
				$temp .= $data[ $i ];
				$i++;
			}
			$data = substr( $data, $i + 1 );
			return $temp;
		}

		function cs_get_value_byte( &$data ) 
		{
			$temp = $data[ 0 ];
			$data = substr( $data, 1 );
			return ord( $temp );
		}

		function cs_get_value_lint( &$data ) 
		{
			$temp = substr( $data, 0, 4 );
			$data = substr( $data, 4 );
			$array = @unpack( 'Lint', $temp );
			return $array[ 'int' ];
		}

		function cs_get_value_sint( &$data ) 
		{
			$tmp = substr( $data, 0, 2 );
			$data = substr( $data, 2 );
			$array = @unpack( 'Sshort', $tmp );
			return $array['short'];
		}
	}
?>
