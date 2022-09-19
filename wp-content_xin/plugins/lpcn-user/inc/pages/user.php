<?php
add_shortcode('lpcn-user-center', function () {
	return <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <title>React-Bootstrap</title>
    <link href="/wp-content/plugins/lpcn-user/static/css/main.c09076b9.css" rel="stylesheet">
    <script defer="defer" src="/wp-content/plugins/lpcn-user/static/js/main.6b407022.js"></script> 
</head>
<body>
<noscript>🤷‍♂️ 您需要启用 JavaScript 才能运行此应用!<br>You need to enable JavaScript to run this app.</noscript>
<main id="root" class="body"></main>
</body>
</html>  
HTML;
});
 