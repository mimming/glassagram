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
require_once 'config.php';
session_start();
session_destroy();
?>
<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glassagram</title>
    <link href="./static/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <style>
        .button-icon { max-width: 75px; }
        .tile {
            border-left: 1px solid #444;
            padding: 5px;
            list-style: none;
        }

    </style>
</head>
<body>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="brand" href="#">Glassagram</a>
            <div class="nav-collapse collapse">
                <form class="navbar-form pull-right" action="<?= $base_url ?>/glassagram.php" method="post">
                    <button type="submit" class="btn">Sign in</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container">

    <div class="hero-unit">
        <h2>You have been signed out. </h2>
        <?php if ($message != "") { ?>
            <span class="label label-warning">Message: <?= $message ?> </span>
        <?php } ?>
        <div style="clear:both;"></div>
    </div>

</div>

<script
    src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="/static/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>