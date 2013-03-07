<?php

$stageroot = "/var/www/stagegit/";				// Root directory where repositories are synced to
$branch = "master";						// Name of branch you want to pull from

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

// exec("git checkout " . $branch);


/*
*
*	Data has now been pulled to server, time to read package.json
*
*/

$package = json_decode(file_get_contents($directory . '/package.json'));

echo $directory . '/package.json';
echo "<pre>";
var_dump($package->git);

foreach($package->git as $stage):
	echo "Branch :: " . $stage->branch;
	echo "url :: " . $stage->url;
endforeach;




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