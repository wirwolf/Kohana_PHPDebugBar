<?php defined('SYSPATH') OR die('No direct script access.') ?>
<?php

$bar = \Registry::getDebugBar();
$debugBarRenderer = $bar->getJavascriptRenderer(\Kohana::$base_url.'application/vendor/maximebf/debugbar/src/DebugBar/Resources/');



?>
<html>
<head>
	<?php echo $debugBarRenderer->renderHead();?>
</head>
<body>
	<?php echo $debugBarRenderer->render()?>
</body>
</html>