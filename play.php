<?php
$v = @filemtime(__DIR__ . '/play/index.html') ?: time();
header('Location: play/index.html?v=' . $v, true, 302);
exit;
