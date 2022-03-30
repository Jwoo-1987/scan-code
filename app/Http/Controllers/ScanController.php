<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
//use Config\Wechat;
use App\Models\Counters;
use App\Common\Toast;
use App\Models\Code;

class ScanController extends Controller
{



    public function test(){
        $config = config('wechat.official_account');
        $app = Factory::officialAccount($config['default']);
        $data = '{"debug":false,"beta":false,"jsApiList":["das","dada"],"openTagList":[],"appId":"wx7d62dd68f98515d9","nonceStr":"Io6p5zqvsk","timestamp":1648262392,"url":"https://laravel-uodp-1752042-1310541127.ap-shanghai.run.tcloudbase.com/scan/test","signature":"40042bce92b66aa05c5dce2bd0264c2a5c3b1883"}';
        //$data = $app->jssdk->buildConfig(['das','dada'], $debug = false, $beta = false, $json = true);
        $data = (json_decode($data,true));
        return view('scan', ['str' => $data]);
    }

    public function save(Request $request){
        $code = $request->input('code');
        $codeObj = new Code();
        $codeObj->code = $code;
        if(!$codeObj->save()){
            $res = [
                "data" => [],
                "errorMsg" => ("保存错误")
            ];
            return Toast::error($res);
        }
        $res = [
            'id'=>$codeObj->id
        ];
        return Toast::api($res);
    }

    public function list(Request $request){
        $page = $request->input('page',1);
        $pageSize = 10;
        $data = Code::orderBy('creat_time', 'desc');
        $data = $data->paginate($pageSize, ['*'], 'page', $page)->toArray();
        if($data){
            $list = $data['data'];
            $res = array(
                'items' => $list,
                'currentPage' => $data['current_page'],
                'lastPage' => $data['last_page'],
                'perPage' => $data['per_page'],
                'total' => $data['total'],
                'nextPageUrl' => $data['next_page_url']
            );
        }
        return Toast::api(
            $list
        );


    }
}
