<?php

namespace phpbb;

require(__DIR__ . '/../vendor/autoload.php');

config::root(__DIR__ . '/..');
config::logLevel(getenv('LOG_LEVEL'));
