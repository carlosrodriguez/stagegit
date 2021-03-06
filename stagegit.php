<?php

$publishto = "/var/www/publishtest/";
$reporoot = "/var/www/repos/";
$stageroot = "/var/www/stagegit/";				// Root directory where repositories are synced to
$branch = "master";						// Default branch

// Testing JSON feed
// $gitdata = json_decode($_POST['payload']);
$gitdata = json_decode(file_get_contents('payload.txt'));
$stagegit = new stagegit();

$stagegit->addLog("Initiated by " . $stagegit->getRealIpAddr());

/*  Check for data before we continue  */
if(empty($gitdata)) {
	$stagegit->addLog("No git data received");
	die("No git data to submit");	
}

$stagegit->addLog("Received git data for ".$gitdata->repository->name);

$directory = $stagegit->identifyDir($gitdata->repository->name, $reporoot);
$remote = $stagegit->createRemote($gitdata->repository->owner->name, $gitdata->repository->name);

if(!file_exists($directory)):
	chdir($reporoot);

	// Create the directory for our repo
	$stagegit->addLog("git clone " . $remote);
	exec("git clone " . $remote, $output);
	
	chdir($directory);
else:
	$stagegit->addLog("Updating " . $directory);
	chdir($directory);

	exec("git pull");
endif;


/*
*
*	Data has now been pulled to server, time to read package.json
*
*/

$package = json_decode(file_get_contents($directory . '/package.json'));

if(!empty($package->git)):
	foreach($package->git as $stage):
		$publishdir = $stagegit->identifyDir($stage->url, $publishto);

		if(!file_exists($publishdir)):
			$stagegit->addLog("Creating " . $publishdir);

			mkdir($publishdir, $recursive = true);

			chdir($publishdir);
			exec("git clone " . $remote . " ./", $output);
			$stagegit->addLog(print_r($output, true));
		else:
			$stagegit->addLog("Updating " . $stage->url);
			chdir($publishdir);

			exec("git pull", $output);
			$stagegit->addLog(print_r($output, true));
		endif;

		exec("git checkout " . $stage->branch, $output);
		print_r($output);
		print_r($stage->branch);
	endforeach;
endif;



/*
*
*	Some functions that can be shared
*
*/

class stagegit {
	public function identifyDir($reponame, $root) {
		return $root . $reponame;
	}

	public function createRemote($owner, $name) {
		return "git@github.com:".$owner."/".$name.".git";
	}

	public function addLog($message) {
		clearstatcache();

		$logfile = $stageroot . "git_log.txt";

		$fo = fopen($logfile, "a");
		fwrite($fo, "[".date("Y-m-d H:i:s") . "] " . $message . "\n");
		fclose($fo);
	}

	public function getRealIpAddr()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
		//check ip from share internet
		{
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
		//to check ip is pass from proxy
		{
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
}

?>