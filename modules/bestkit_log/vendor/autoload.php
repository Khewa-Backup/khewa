<?php

spl_autoload_register(static function ($class) {
    if ($class === 'BestkitLogAuthenticationSubscriber') {
        require_once dirname(__DIR__) . '/classes/BestkitLogAuthenticationSubscriber.php';
    }
});

return true;
