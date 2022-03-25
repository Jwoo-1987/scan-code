<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use Config\Wechat;

class ScanController extends Controller
{



    public function test(){
        $config = config('wechat.official_account');
        $app = Factory::officialAccount($config);

        var_dump($app->jssdk->buildConfig(['das','dada'], $debug = false, $beta = false, $json = true));
    }
}
