<?php
require_once(dirname(__FILE__) . "/../initialize.php");
class SimpleRadius
{


	//=========================================================================================

	public function Run_Command($command_options)
	{
		global $SYSTEM_SETTING;
		$command="sudo " . $SYSTEM_SETTING["simple_radius_script"] . " " . $command_options . ">/dev/null";
		system($command);
	}

	//=========================================================================================
	public function Run_Command_With_Output($command_options)
	{
		global $SYSTEM_SETTING;
		$command="sudo " . $SYSTEM_SETTING["simple_radius_script"] . " " . $command_options;
		$result=`$command`;
		return $result;
	}
	//=========================================================================================

	public function Run_Background_Command($command_options)
	{
		global $SYSTEM_SETTING;
		$command="sudo " . $SYSTEM_SETTING["simple_radius_script"] . " " . $command_options . ">/dev/null &";
		system($command);
	}
	//=========================================================================================

}


?>
