<?php


$commit = json_decode(file_get_contents('php://input'), 1);

$qstring = [];

$qstring['branch'] = str_replace('refs/heads/', '', $commit['refChanges'][0]['refId']);
$qstring['newrev'] = $commit['changesets']['values'][0]['toCommit']['id'];
$qstring['committer'] = $commit['changesets']['values'][0]['toCommit']['author']['emailAddress'];
$qstring['message'] = $commit['changesets']['values'][0]['toCommit']['message'];

$projectId = $commit['repository']['slug'];

$url = "http://" . $_SERVER['HTTP_HOST'] . "/webhook/git/$projectId?"
        . http_build_query($qstring);

echo "OK";

$ret = file_get_contents($url);

error_log("\n".$url."\n".$ret."\n", 3, "/tmp/stash.log");
