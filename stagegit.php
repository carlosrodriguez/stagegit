<?php

$stageroot = "/var/www/";				// Root directory where repositories are synced to
$branch = "master";						// Name of branch you want to pull from

// Testing JSON feed
$gitdata = json_decode($_POST['payload'], true);




/*  Check for data before we continue  */
if(empty($gitdata)) die("No git data to submit");

$stagegit = new stagegit();

$stagegit->addLog("Started as " . exec('whoami'));

$directory = $stagegit->identifyDir($gitdata["repository"]["name"], $stageroot);

$remote = $stagegit->createRemote($gitdata["repository"]["owner"]["name"], $gitdata["repository"]["name"]);

$stagegit->addLog("Set namespaces");

if(!file_exists($directory)):
	$stagegit->addLog("Creating " . $directory);
	chdir($stageroot);

	// Create the directory for our repo
	exec("git clone " . $remote, $output);
	$stagegit->addLog("git clone  " . $remote);
	$stagegit->addLog(print_r($output, true));

	chdir($directory);
else:
	$stagegit->addLog("Updating");
	chdir($directory);

	exec("git pull");
	$stagegit->addLog("Updated");
endif;

exec("git checkout " . $branch);

$stagegit->addLog("Checkout");



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
		fwrite($fo, $message . "\n");
		fclose($fo);
	}
}

?>