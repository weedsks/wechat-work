<?php

namespace Weeds\WechatWork\Facades;


use Illuminate\Support\Facades\Facade;

class WechatWork extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wechatwork';
    }
}
