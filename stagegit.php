<?php

$publishto = "/var/www/stagegit/publish/";
$stageroot = "/var/www/stagegit/";				// Root directory where repositories are synced to
$branch = "master";						// Default branch

// Testing JSON feed
// $gitdata = json_decode($_POST['payload'], true);

$gitdata = json_decode(file_get_contents('payload.txt'));


/*  Check for data before we continue  */
if(empty($gitdata)) die("No git data to submit");

$stagegit = new stagegit();

$directory = $stagegit->identifyDir($gitdata->repository->name, $stageroot);
$remote = $stagegit->createRemote($gitdata->repository->owner->name, $gitdata->repository->name);

if(!file_exists($directory)):
	chdir($stageroot);

	// Create the directory for our repo
	exec("git clone " . $remote, $output);
	$stagegit->addLog("git clone " . $remote);

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
			$stagegit->addLog("Creating " . $stage->url);
			chdir($publishto);
			mkdir($stage->url);
			chdir($publishdir);
			exec("git clone " . $remote . " ./", $output);
			print_r($output);
		else:
			$stagegit->addLog("Updating " . $stage->url);
			chdir($publishdir);

			exec("git pull", $output);
			print_r($output);
		endif;

		//exec("git checkout " . $stage->branch, $output);
		exec("git checkout forstaging", $output);
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
}

?>