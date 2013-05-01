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
require_once 'config.php';
require_once 'mirror-client.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_MirrorService.php';
require_once 'util.php';
require_once 'filters.php';


@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);


error_log("notify ran");

if($_SERVER['REQUEST_METHOD'] != "POST") {
  exit();
}

exec('php filters.php &');
exit();

//register_shutdown_function('filter_images');
// hack to return 200 right away and continue to process images
//header("Connection: close",true);
//header("Content-Length: 0",true);
//ob_end_flush();
//flush();
/*
// tell the client the request has finished processing
header('Location: index.php');  // redirect (optional)
header('Status: 200');          // status code
header('Connection: close');    // disconnect

// clear ob stack
@ob_end_clean();

// continue processing once client disconnects
ignore_user_abort();

ob_start();*/
/* ------------------------------------------*/
/* this is where regular request code goes.. */

/* end where regular request code runs..     */
/* ------------------------------------------*/
/*
$iSize = ob_get_length();
header("Content-Length: $iSize");

// if the session needs to be closed, persist it
// before closing the connection to avoid race
// conditions in the case of a redirect above
session_write_close();

// send the response payload to the client
@ob_end_flush();
flush();*/

/* ------------------------------------------*/
/* code here runs after the client disconnect */

//register_shutdown_function('filter_images');
//exit();

/*
//function filter_images() {

    error_log("notify got a POST");

    // Parse the request body
    $request_bytes = @file_get_contents('php://input');
    $request = json_decode($request_bytes, true);
    error_log("notify decoded json " . $request_bytes);

    // A notification has come in. If there's an attached photo, bounce it back
    // to the user
    $user_id = $request['userToken'];
    error_log("notify found user " . $user_id);

    $access_token = get_credentials($user_id);

    $client = get_google_api_client();
    $client->setAccessToken($access_token);
    error_log("notify found access token " . $access_token);

    // A glass service for interacting with the Mirror API
    $mirror_service = new Google_MirrorService($client);

    $timeline_item_id = $request['itemId'];

    print "item id: " . $timeline_item_id;

    $timeline_item = $mirror_service->timeline->get($timeline_item_id);

    $attachment = $timeline_item['attachments'][0];


    $bytes = downloadAttachment( $timeline_item_id, $attachment);


    error_log("got bytes ");

    // TODO: get a bundle id (unique number)
    //$bundle_id = 42;
    $bundle_id = md5(uniqid($_SESSION['userid'].time()));
    $original_image = imagecreatefromstring($bytes);

    error_log("filtered on image ");

    $filtered_images = gd_process_image ($original_image);
    foreach ($filtered_images as $filtered_image) {
        $timeline_item = new Google_TimelineItem();
        $timeline_item->setBundleId($bundle_id);
        //$timeline_item->setText("Glassagram");
        $menuItems = array();
        $shareMenuItem = new Google_MenuItem();
        $shareMenuItem->setAction("SHARE");
        array_push($menuItems, $shareMenuItem);
        $deleteMenuItem = new Google_MenuItem();
        $deleteMenuItem->setAction("DELETE");
        array_push($menuItems, $deleteMenuItem);
        $timeline_item->setMenuItems($menuItems);
        insertTimelineItem($mirror_service, $timeline_item, "image/jpeg", $filtered_image );
    }
*/
    // do gd on $bytes
    /*
    imagefilter($original_image, IMG_FILTER_GRAYSCALE);
    ob_start();
    imagejpeg($original_image);
    $final_image = ob_get_contents();
    ob_end_clean();
    */

    // create the item
    /*
    $timeline_item = new Google_TimelineItem();
    $timeline_item->setBundleId();
    $timeline_item->setText("now with more grayscale");
    $menuItems = array();
    $menuItem = new Google_MenuItem();
    $menuItem->setAction("SHARE");
    array_push($menuItems, $menuItem);
    $menuItem->setAction("DELETE");
    array_push($menuItems, $menuItem);
    insertTimelineItem($mirror_service, $timeline_item, "image/jpeg", $final_image );

    */

//}
