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

$client = get_google_api_client();

// Authenticate if we're not already
if (!isset($_SESSION['userid']) || get_credentials($_SESSION['userid']) == null) {
  header('Location: ' . $base_url . '/oauth2callback.php');
  exit;
} else {
  $client->setAccessToken(get_credentials($_SESSION['userid']));
}

// A glass service for interacting with the Mirror API
$mirror_service = new Google_MirrorService($client);
/*
// But first, handle POST data from the form (if there is any)
switch ($_POST['operation']) {
  case "deleteContact":
    deleteContact($mirror_service, $_POST['id']);
    $message = "Contact deleted.";
    break;
}

//Load cool stuff to show them.
$timeline = $mirror_service->timeline->listTimeline(array('maxResults'=>'3'));
try {
  $contact = $mirror_service->contacts->get("php-quick-start");
} catch (Exception $e) {
  // no contact found. Meh
  $contact = null;
}
$subscriptions = $mirror_service->subscriptions->listSubscriptions();
$timelineSubscriptionExists = false;
$locationSubscriptionExists = false;
foreach ($subscriptions['items'] as $subscription) {
  if ($subscription['id'] == 'timeline') {
    $timelineSubscriptionExists = true;
  } elseif ($subscription['id'] == 'location') {
    $locationSubscriptionExists = true;
  }
}
*/
?>
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glassagram</title>
  <link href="./static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
  <style>
    .tile {
      border-left: 1px solid #444;
      padding: 5px;
      list-style: none;
    }
    .row {margin-top:3em;}
    .row ol li {font-size:31.5px;}
      .row .logo {text-align: center;}
      .row ol li p {font-size:0.5em;}
  </style>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
  <div class="navbar-inner">
    <div class="container">
      <a class="brand" href="#">Glassagram</a>
      <div class="nav-collapse collapse">
        <form class="navbar-form pull-right" action="signout.php" method="post">
          <button type="submit" class="btn">Sign out</button>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="container">
    <div class="row">

        <div class="logo"><img src="static/images/glassagram-lol.png"/></div>
        <ol>
            <li>
                <div>
                <h2>Enable Sharing Contact</h2>
                <p>Please enable Glassagram on
                <a href="https://glass.google.com/myglass/share">MyGlass</a></p>

                </div>
            </li>
            <!--li>
                <div>
                    <h2>Choose filters</h2>
                    <p>Select up to 5 of your favorite filters to use with Glassagram. You can always come back to change up your selection.</p>
                </div>
            </li-->
            <li>
                <div>
                    <h2>Take a picture!</h2>
                    <p></p>
                </div>
            </li>
            <li>
                <div>
                    <h2>Share your picture with Glassagram</h2>
                    <p>Glassagram will apply all the filters you selected and send a bundle of images back to you.</p>
                </div>
            </li>
            <li>
                <div>
                    <h2>Share your filtered photo with friends</h2>
                    <p>Choose and share the best one with your friends!</p>
                </div>
            </li>

        </ol>
    </div>
</div>
<script
    src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/static/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">

    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-40492558-2']);
    _gaq.push(['_trackPageview']);

    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();

</script>
</body>
</html>
