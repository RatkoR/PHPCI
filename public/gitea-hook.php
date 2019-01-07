<?php

/**
 * Stash pushes commit info to this intermediate file.
 *
 * This file parses commit info and calls PHPCI webhook to
 * start the build process.
 */


$data = json_decode(file_get_contents('php://input'), 1);

error_log(print_r($data, 1) . "\n\n", 3, "/tmp/gitea.data");

/*
 * This is what stash sends us
 * See https://confluence.atlassian.com/bitbucketserver/post-service-webhook-for-bitbucket-server-776640367.html
 */
$data = json_decode(file_get_contents('php://input'), 1);
$refId = $data['ref'];

// if this is not a commit into branch we're not interested in
// building
if ($refId !== 'refs/heads/master') {
    return;
}

$qstring = [];

$qstring['branch'] = str_replace('refs/heads/', '', $refId);
$qstring['newrev'] = $data['after'];

$commits = $data['commits'];

$committers = array_map(function ($commit) {
    return $commit['author']['email'];
}, $commits);
$committers = array_unique($committers);

$messages = array_map(function ($commit) {
    if (substr($commit['message'], 0, 12) === 'Merge branch') {
        return null;
    }
    return $commit['message'];
}, $commits);
$messages = array_filter($messages);
$messages = array_unique($messages);

$qstring['committer'] = implode(',', $committers);
$qstring['message'] = implode("\n<br>\n", $messages);

$projectId = $data['repository']['name'];

/**
 * Url of the phpci testing server
 */
$url = "http://" . $_SERVER['HTTP_HOST']
        . '/webhook/git/' . $projectId . '?'
        . http_build_query($qstring);

echo "OK";

// $ret = file_get_contents($url);

error_log("\n".$url."\n".$ret."\n".print_r($qstring, 1)."\n", 3, "/tmp/gitea.data");
