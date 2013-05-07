<?php
/*
 * Copyright (C) 2013 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
//  Author: Jenny Murphy - http://google.com/+JennyMurphy
// Modified by: Winnie Tong



// Always respond with a 200 right away and then terminate the connection to prevent notification
// retries. How this is done depends on your HTTP server configs. I'll try a few common techniques
// here, but if none of these work, start troubleshooting here.

// First try: the content length header
header("Content-length: 2");

// Next, assuming it didn't work, attempt to close the output buffer by setting the time limit.
ignore_user_abort(true);
set_time_limit(0);



// And one more thing to try: forking the heavy lifting into a new process. Yeah, crazy eh?

if(function_exists('pcntl_fork')) {
  $pid = pcntl_fork();
  if ($pid == -1) {
    error_log("could not fork!");
    echo "OK";
    exit();
  } else if ($pid) {
    // fork worked! but I'm the parent. time to exit.
    echo "OK";
    exit();
  }
}



require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';
require_once 'filters.php';

if($_SERVER['REQUEST_METHOD'] != "POST") {
  echo "method not supported";
  exit();
}

// Parse the request body
$request_bytes = @file_get_contents('php://input');
$request = json_decode($request_bytes, true);
//error_log("notify decoded json " . $request_bytes);

// A notification has come in. If there's an attached photo, bounce it back
// to the user
$user_id = $request['userToken'];
//error_log("notify found user " . $user_id);

$access_token = get_credentials($user_id);

$client = get_google_api_client();
$client->setAccessToken($access_token);
//error_log("notify found access token " . $access_token);

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);

$timeline_item_id = $request['itemId'];

//print "item id: " . $timeline_item_id;


$timeline_item = new Google_TimelineItem($mirror_service->timeline->get($timeline_item_id));

$attachments = $timeline_item->getAttachments();
$attachment = $attachments[0];

$bytes = downloadAttachment( $timeline_item_id, $attachment);

//error_log("got bytes ");

$bundle_id = md5(uniqid($_SESSION['userid'].time()));

// add the fun images in new timeline items in the same bundle
$original_image = imagecreatefromstring($bytes);

//error_log("filtered on image ");

$filtered_images = gd_process_image ($original_image);
foreach ($filtered_images as $filtered_image) {
  $bundle_card = new Google_TimelineItem();
  $bundle_card->setBundleId($bundle_id);
  $menuItems = array();
  $shareMenuItem = new Google_MenuItem();
  $shareMenuItem->setAction("SHARE");
  array_push($menuItems, $shareMenuItem);
  $deleteMenuItem = new Google_MenuItem();
  $deleteMenuItem->setAction("DELETE");
  array_push($menuItems, $deleteMenuItem);
  $bundle_card->setMenuItems($menuItems);
  insertTimelineItem($mirror_service, $bundle_card, "image/jpeg", $filtered_image );
}

// Update the original timeline item to add it to the bundle and make it the cover
// Do this last so the "ding" comes after everything else is done processing
$timeline_item->setBundleId($bundle_id);
$timeline_item->setIsBundleCover(true);
$timeline_item->setText("Glassagram made your photo better");
$notification = new Google_NotificationConfig();
$notification->setLevel("DEFAULT");
$timeline_item->setNotification($notification);


updateTimelineItem($mirror_service, $timeline_item_id, $timeline_item);