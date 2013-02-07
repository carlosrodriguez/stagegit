<?php

$stageroot = "/var/www/staging/";		// Root directory where repositories are synced to
$branch = "master";							// Name of branch you want to pull from


// Testing JSON feed
$my_file = 'payload.txt';
$handle = fopen($my_file, 'r');
$gitdata = json_decode(fread($handle,filesize($my_file)));




/*  Check for data before we continue  */
if(empty($gitdata)) die("No git data to submit");

$stagegit = new stagegit();

$directory = $stagegit->identifyDir($gitdata->repository->name, $stageroot);

$remote = $stagegit->createRemote($gitdata->repository->owner->name, $gitdata->repository->name);

if(!file_exists($directory)):

	exec("sudo -i");

	chdir($stageroot);

	// Create the directory for our repo
	exec("git clone " . $remote);

	echo "git clone " . $remote;
endif;

chdir($directory);

exec("git pull");

exec("git checkout " . $branch);




class stagegit {
	public function identifyDir($reponame, $root) {
		return $root . $reponame;
	}

	public function createRemote($owner, $name){
		return "git@github.com:".$owner."/".$name.".git";
	}
}

?>