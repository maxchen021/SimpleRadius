<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class SSLCerts
{
	private $ssl_cert_type="";

	//=========================================================================================
	public function SetSSLCertType($cert_type)
	{
		$this->ssl_cert_type=$cert_type;
	}
	
	public function SaveSSLCertsKeyDataToFile($result,$filename)
	{
		if ($row = $result->fetchArray())
	     {
			$filehandle = fopen($filename, 'w') or die("can't open file: " . $filename);
	        fwrite($filehandle,$row['key_data']);
			fclose($filehandle);
	     }
	}

	//=========================================================================================
	public function SaveSSLCertsToFile()
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;
		$ssl_cert_dir = $this->GetCertDir();

		//ca cert
		$query="select * from ssl_certs where key_name='" . $this->ssl_cert_type . "_ssl_cert_ca_cert'";
  		$result=$CURRENT_DB->DBSelectQuery($query);
  		$filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['ca_cert_name'];		
	    $this->SaveSSLCertsKeyDataToFile($result,$filename);
		 
		 //public key
		 $query="select * from ssl_certs where key_name='" . $this->ssl_cert_type . "_ssl_cert_public_key'";
  		 $result=$CURRENT_DB->DBSelectQuery($query);
  		 $filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['public_key_name'];
  		 $this->SaveSSLCertsKeyDataToFile($result,$filename);

		 //private key
		 $query="select * from ssl_certs where key_name='" . $this->ssl_cert_type . "_ssl_cert_private_key'";
  		 $result=$CURRENT_DB->DBSelectQuery($query);
  		 $filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['private_key_name'];
  		 $this->SaveSSLCertsKeyDataToFile($result,$filename);
		   
		 //dh parameters
		 $query="select * from ssl_certs where key_name='" . $this->ssl_cert_type . "_dh_parameters'";
  		 $result=$CURRENT_DB->DBSelectQuery($query);
  		 $filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['dh_parameters_name'];
  		 $this->SaveSSLCertsKeyDataToFile($result,$filename);
		 
		 
		 if ( strcmp($this->ssl_cert_type,"https")==0 )
		 {
		 	SimpleRadius::Run_Command("Update_Apache_SSL_Certs");
		 }
		 elseif ( strcmp($this->ssl_cert_type,"radius")==0 ) {
		 	SimpleRadius::Run_Command("Update_Radius_SSL_Certs");
		 }
		 
	     
	}

	//=========================================================================================
	public function SaveCertToDB($key_name,$key_data)
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;

		$query="update ssl_certs set key_data='" . $key_data .  "' where key_name='" . $key_name . "'" ;
		$result=$CURRENT_DB->DBUpdateQuery($query);
	}
	
	//=========================================================================================
	public function GetCertFromDB($key_name)
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;
		
		$query="select * from ssl_certs where key_name='" . $key_name . "'";

		$result=$CURRENT_DB->DBSelectQuery($query);

		$key_data="";
		if($row=$result->fetchArray())
		{
			if( isset($row['key_data']) && strcmp($row['key_data'],"")!=0 ) {
			  $key_data=$row['key_data'];
			}
		}
		return $key_data;
	}
	
	//=========================================================================================
	
	public function Generate_New_SSL_Certs()
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;
		
		$ssl_cert_dir = $this->GetCertDir();
		$this->CreateSSLCertDirectories($ssl_cert_dir);
		$csr_filename=$ssl_cert_dir . "/server.csr";
		$public_key_filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['public_key_name'];
		$private_key_filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['private_key_name'];
		$ca_cert_filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['ca_cert_name'];

		$command='openssl req -new -newkey rsa:' . $SYSTEM_SETTING['ssl_cert']['key_size'] . ' -nodes -out ' . $csr_filename . ' -keyout ' . $private_key_filename . ' -subj "' . $SYSTEM_SETTING['ssl_cert']['subject'] . '"';
		echo `$command`;
		$command='openssl x509 -req -days 3650 -in ' . $csr_filename . ' -signkey ' . $private_key_filename . ' -out ' . $public_key_filename;
		echo `$command`;
		$command="cp " . $public_key_filename . " " . $ca_cert_filename;
		echo `$command`;
		
		$command = "cat " . $public_key_filename;
		$public_key = `$command`;
		$command = "cat " . $private_key_filename;
		$private_key = `$command`;
		$command = "cat " . $ca_cert_filename;
		$ca_cert = `$command`;
		
		$this->SaveCertToDB($this->ssl_cert_type . "_ssl_cert_public_key",$public_key);
		$this->SaveCertToDB($this->ssl_cert_type . "_ssl_cert_private_key",$private_key);
		$this->SaveCertToDB($this->ssl_cert_type . "_ssl_cert_ca_cert",$ca_cert);

	}
	
	//using the dh parameters that come with freeradius to reduce time to create it
	/*
	public function Generate_New_DH_Parameters()
	{
		global $CURRENT_DB;
		global $SYSTEM_SETTING;
		
		$ssl_cert_dir = $this->GetCertDir();
		$this->CreateSSLCertDirectories($ssl_cert_dir);
		
		$dh_parameters_filename=$ssl_cert_dir . "/" . $SYSTEM_SETTING['ssl_cert']['dh_parameters_name'];
				
		$command='openssl dhparam -out ' . $dh_parameters_filename . " " . $SYSTEM_SETTING['ssl_cert']['key_size'];
		echo `$command`;
		
		$command = "cat " . $dh_parameters_filename;
		$dh_parameters = `$command`;
				
		$this->SaveCertToDB($this->ssl_cert_type . "_dh_parameters",$dh_parameters);
	}
	*/
	
	
	//=========================================================================================
	public function ValidateSSLCert($cert_filename)
	{
			$command="openssl x509 -in " . $cert_filename . " -text -noout";

			$descriptorspec = array(
				0 => array('pipe', 'r'), // stdin
				1 => array('pipe', 'w'), // stdout
				2 => array('pipe', 'w') // stderr
			);

			$proc = proc_open($command, $descriptorspec, $pipes);

			//fwrite($pipes[0], $input); //writing to std_in
			//fclose($pipes[0]);

			$is_valid_cert=false;

			if (is_resource($proc)) {

			$error=trim(stream_get_contents($pipes[2]));

			if($error=="")
			{
				$is_valid_cert=true;
			}
			else
			{
				$is_valid_cert=false;
			}

			// close pipe
			fclose($pipes[2]) ;
			
			// close process
			proc_close($proc) ;
			}

			return $is_valid_cert;
		}

	//=========================================================================================
	public function ValidateSSLKey($key_filename)
	{

			$command="openssl rsa -in " . $key_filename . " -check";

			$descriptorspec = array(
				0 => array('pipe', 'r'), // stdin
				1 => array('pipe', 'w'), // stdout
				2 => array('pipe', 'w') // stderr
			);

			$proc = proc_open($command, $descriptorspec, $pipes);

			//fwrite($pipes[0], $input); //writing to std_in
			//fclose($pipes[0]);

			$is_valid_key=false;

			if (is_resource($proc)) {

			$error=trim(stream_get_contents($pipes[2]));
			//echo $error;

			if(substr_count($error,'unable to load Private Key')>0)
			{
				$is_valid_key=false;
			}
			else
			{
				$is_valid_key=true;
			}

			// close pipe
			fclose($pipes[2]) ;
			
			// close process
			proc_close($proc) ;
			}

			return $is_valid_key;
		

	}
	
	//=========================================================================================
	public function ValidateDHParameters($dh_filename)
	{

			$command="openssl dhparam -inform PEM -in " . $dh_filename . " -check";

			$descriptorspec = array(
				0 => array('pipe', 'r'), // stdin
				1 => array('pipe', 'w'), // stdout
				2 => array('pipe', 'w') // stderr
			);

			$proc = proc_open($command, $descriptorspec, $pipes);

			//fwrite($pipes[0], $input); //writing to std_in
			//fclose($pipes[0]);

			$is_valid_key=false;

			if (is_resource($proc)) {

			$error=trim(stream_get_contents($pipes[2]));
			//echo $error;

			if(substr_count($error,'unable to load DH parameters')>0)
			{
				$is_valid_key=false;
			}
			else
			{
				$is_valid_key=true;
			}

			// close pipe
			fclose($pipes[2]) ;
			
			// close process
			proc_close($proc) ;
			}

			return $is_valid_key;
		

	}


	//=========================================================================================
	private function CreateSSLCertDirectories($dir)
	{
		if ( !is_dir($dir) )
		{
			mkdir($dir, 0755, true);
		}
	}
	//=========================================================================================
	
	private function GetCertDir()
	{
		global $SYSTEM_SETTING;
		$dir="";
		if ( strcmp($this->ssl_cert_type,"https")==0 )
		{
			$dir=$SYSTEM_SETTING["https_ssl_certs_directory"];
		}
		elseif ( strcmp($this->ssl_cert_type,"radius")==0 ) {
			$dir=$SYSTEM_SETTING["radius_ssl_certs_directory"];
		}
		
		return $dir;
	}
	
	
	//=========================================================================================

}


?>
