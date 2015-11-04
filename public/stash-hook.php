<?php

/**
 * Stash pushes commit info to this intermediate file.
 *
 * This file parses commit info and calls PHPCI webhook to
 * start the build process.
 */

/*
 * This is what stash sends us
 * See https://confluence.atlassian.com/bitbucketserver/post-service-webhook-for-bitbucket-server-776640367.html
 */
$commit = json_decode(file_get_contents('php://input'), 1);
$refId = $commit['refChanges'][0]['refId'];

// if this is not a commit into branch we're not interested in
// building
if (strpos($refId, 'refs/heads/') === false) {
    return;
}

$qstring = [];

$qstring['branch'] = str_replace('refs/heads/', '', $refId);
$qstring['newrev'] = $commit['changesets']['values'][0]['toCommit']['id'];
$qstring['committer'] = $commit['changesets']['values'][0]['toCommit']['author']['emailAddress'];
$qstring['message'] = $commit['changesets']['values'][0]['toCommit']['message'];

$projectId = $commit['repository']['slug'];

/**
 * Url of the phpci testing server
 */
$url = "http://" . $_SERVER['HTTP_HOST']
        . '/webhook/git/' . $projectId . '?'
        . http_build_query($qstring);

echo "OK";

$ret = file_get_contents($url);

//error_log("\n".$url."\n".$ret."\n", 3, "/tmp/stash.log");
