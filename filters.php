<?php

/**
 * http://marchibbins.com/dev/gd/
 * @author Marc Hibbins
 */
/**
 * Modified by: Winnie Tong
 * Date: 4/21/13
 * Time: 4:27 PM
 */

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

/** Apply and deliver the image and clean up */
function gd_filter_image($image_path, $filter_name)
{
    $filter = 'gd_filter_' . $filter_name;
    if (function_exists($filter)) {
        list($width, $height) = getimagesize($image_path);

        $im = imagecreatetruecolor($width, $height);
        $src = imagecreatefromjpeg($image_path);
        imagecopyresampled($im, $src, 0, 0, 0, 0, $width, $height, $width, $height);

        $im = $filter($im);

        header('Content-type: image/jpeg');
        imagejpeg($im, null, 100);
        imagedestroy($im);
        imagedestroy($src);
    }
}

function gd_process_image ($src) {
    $filters = array('antique','blackwhite','boost','sepia');
    $filtered_images = array();
    foreach ($filters as $filter_name) {

        $filter = 'gd_filter_' . $filter_name;
        if (function_exists($filter)) {
            $im = gd_crop_image($src);
            $filtered_image = $filter($im);

        ob_start();
        imagejpeg($filtered_image);
        $filtered_image = ob_get_contents();
        ob_end_clean();

        array_push($filtered_images, $filtered_image);
        }
    }
    return $filtered_images;
}

function gd_crop_image($src) {
    $width = imagesx($src);
    $height = imagesy($src);
    $center_x = round($width / 2);
    $center_y = round($height / 2);

    if ($height < $width) { // landscape orientation
        $target_dimension = $height;
    } else if ($height > $width) { // portrait orientation
        $target_dimension = $width;
    }

    if ($height != $width) {
        $target_dimension_half = round($target_dimension/2);
        $x1 = max(0, $center_x - $target_dimension_half);
        $y1 = max(0, $center_y - $target_dimension_half);
        //$x2 = min($width, $center_x + $target_dimension_half);
        //$y2 = min($height, $center_y + $target_dimension_half);
        $im = imagecreatetruecolor($target_dimension, $target_dimension);
        imagecopyresampled($im, $src,0, 0,$x1, $y1,$target_dimension,$target_dimension,$target_dimension,$target_dimension);
    }
    return $im; // image is already a square
}

/** Apply 'Dreamy' preset */
function gd_filter_dreamy($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 20);
    imagefilter($im, IMG_FILTER_CONTRAST, -35);
    imagefilter($im, IMG_FILTER_COLORIZE, 60, -10, 35);
    imagefilter($im, IMG_FILTER_SMOOTH, 7);
    $im = gd_apply_overlay($im, 'scratch', 10);
    $im = gd_apply_overlay($im, 'vignette', 100);
    return $im;
}

/** Apply 'Blue Velvet' preset */
function gd_filter_velvet($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 5);
    imagefilter($im, IMG_FILTER_CONTRAST, -25);
    imagefilter($im, IMG_FILTER_COLORIZE, -10, 45, 65);
    $im = gd_apply_overlay($im, 'noise', 45);
    $im = gd_apply_overlay($im, 'vignette', 100);
    return $im;
}

/** Apply 'Chrome' preset */
function gd_filter_chrome($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 15);
    imagefilter($im, IMG_FILTER_CONTRAST, -15);
    imagefilter($im, IMG_FILTER_COLORIZE, -5, -10, -15);
    $im = gd_apply_overlay($im, 'noise', 45);
    $im = gd_apply_overlay($im, 'vignette', 100);
    return $im;
}

/** Apply 'Lift' preset */
function gd_filter_lift($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 50);
    imagefilter($im, IMG_FILTER_CONTRAST, -25);
    imagefilter($im, IMG_FILTER_COLORIZE, 75, 0, 25);
    $im = gd_apply_overlay($im, 'emulsion', 100);
    return $im;
}

/** Apply 'Canvas' preset */
function gd_filter_canvas($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 25);
    imagefilter($im, IMG_FILTER_CONTRAST, -25);
    imagefilter($im, IMG_FILTER_COLORIZE, 50, 25, -35);
    $im = gd_apply_overlay($im, 'canvas', 100);
    return $im;
}

/** Apply 'Vintage 600' preset */
function gd_filter_vintage($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 15);
    imagefilter($im, IMG_FILTER_CONTRAST, -25);
    imagefilter($im, IMG_FILTER_COLORIZE, -10, -5, -15);
    imagefilter($im, IMG_FILTER_SMOOTH, 7);
    $im = gd_apply_overlay($im, 'scratch', 7);
    return $im;
}

/** Apply 'Monopin' preset */
function gd_filter_monopin($im)
{
    imagefilter($im, IMG_FILTER_GRAYSCALE);
    imagefilter($im, IMG_FILTER_BRIGHTNESS, -15);
    imagefilter($im, IMG_FILTER_CONTRAST, -15);
    $im = gd_apply_overlay($im, 'vignette', 100);
    return $im;
}

/** Apply 'Antique' preset */
function gd_filter_antique($im)
{
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 0);
    imagefilter($im, IMG_FILTER_CONTRAST, -30);
    imagefilter($im, IMG_FILTER_COLORIZE, 75, 50, 25);
    return $im;
}

/** Apply 'Black & White' preset */
function gd_filter_blackwhite($im)
{
    imagefilter($im, IMG_FILTER_GRAYSCALE);
    imagefilter($im, IMG_FILTER_BRIGHTNESS, 10);
    imagefilter($im, IMG_FILTER_CONTRAST, -20);
    return $im;
}

/** Apply 'Colour Boost' preset */
function gd_filter_boost($im)
{
    imagefilter($im, IMG_FILTER_CONTRAST, -35);
    imagefilter($im, IMG_FILTER_COLORIZE, 25, 25, 25);
    return $im;
}

/** Apply 'Sepia' preset */
function gd_filter_sepia($im)
{
    imagefilter($im, IMG_FILTER_GRAYSCALE);
    imagefilter($im, IMG_FILTER_BRIGHTNESS, -10);
    imagefilter($im, IMG_FILTER_CONTRAST, -20);
    imagefilter($im, IMG_FILTER_COLORIZE, 60, 30, -15);
    return $im;
}

/** Apply 'Partial blur' preset */
function gd_filter_blur($im)
{
    imagefilter($im, IMG_FILTER_SELECTIVE_BLUR);
    imagefilter($im, IMG_FILTER_GAUSSIAN_BLUR);
    imagefilter($im, IMG_FILTER_CONTRAST, -15);
    imagefilter($im, IMG_FILTER_SMOOTH, -2);
    return $im;
}

/** Apply a PNG overlay */
function gd_apply_overlay($im, $type, $amount)
{
    $width = imagesx($im);
    $height = imagesy($im);
    $filter = imagecreatetruecolor($width, $height);

    imagealphablending($filter, false);
    imagesavealpha($filter, true);

    $transparent = imagecolorallocatealpha($filter, 255, 255, 255, 127);
    imagefilledrectangle($filter, 0, 0, $width, $height, $transparent);

    $overlay = 'filters/' . $type . '.png';
    $png = imagecreatefrompng($overlay);
    imagecopyresampled($filter, $png, 0, 0, 0, 0, $width, $height, $width, $height);

    $comp = imagecreatetruecolor($width, $height);
    imagecopy($comp, $im, 0, 0, 0, 0, $width, $height);
    imagecopy($comp, $filter, 0, 0, 0, 0, $width, $height);
    imagecopymerge($im, $comp, 0, 0, 0, 0, $width, $height, $amount);

    imagedestroy($comp);
    return $im;
}