<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Landing;
use Chumper\Zipper\Facades\Zipper;
use Dingo\Api\Routing\Helpers;

use Illuminate\Http\Request;
use App\Classes\DeviceInspector;

class HelloController extends Controller
{
    use Helpers;

    public function index()
    {
        $landing_hash = 'vW0vR01w';
        $landing = Landing::where('hash', $landing_hash)->first()['subdomain'];
        $landing_dir = env('LANDINGS_PATH');

        Zipper::make("public/${landing}.zip")->add($landing_dir . '/cod/' . $landing)->close();
        return response()->download(public_path() . "/public/${landing}.zip");



        if (\request()->filled('test')) {
            \Log::info(print_r(request()->all(), true));
        }
        return
            <<<ASCII
<pre>
                  _.---._
              _.-(_o___o_)
              )_.'_     _'.
            _.-( (_`---'_) )-._
          .'_.-'-._`"""`_.-'-._'.
          /` |    __`"`__    | `\
         |   | .'`  ^:^  `'. |   |
         )'-./       |       \.-'(
        /   /        |        \   \
        \   |=======.=.=======|   /
         )`-|   (affninja)    |-'(
         \  \======/-\'\======/  /
          \,=(    <_/;\_|    )=,/
          /  -\      |      /-  \
          | (`-'\    |    /'-`) |
           \_`\  '.__|__.'  /`_//
            /     /     \     \
           /    /`       `\    \
          /_,="(           )"=,_\
          )-_,="\         /"=,_-(
           \    (         )    /
            \    |       |    /
             )._ |       | _.(
         _.-'   '/       \'   '-._
        (__,'  .'         '.  ',__)
           '--`             `--'
</pre>
ASCII;
    }

    public function deviceTester(Request $request, DeviceInspector $device_inspector)
    {
        if (is_null($request->get('ua'))) {
            $ua = $request->header('User-Agent');
        } else {
            $ua = $request->get('ua');
        }

        #IOS
        //$ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

        #Windows
        //$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36';
        //$ua = 'Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko';
        //$ua = 'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.12 Safari/535.11';
        //$ua = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)';
        //$ua = 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 5.1; Trident/5.0)';

        #Android
        //$ua = 'Mozilla/5.0 (Linux; Android 4.0.4; Galaxy Nexus Build/IMM76B) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.133 Mobile Safari/535.19';


        $result = $device_inspector->getDeviceInfo($ua);
        dd($result);
    }

    public function trusted_proxies()
    {
        dd([
            'ip' => request()->ip(),
            'ips' => request()->ips(),
            'getClientIp' => request()->getClientIp(),
            'getClientIps' => request()->getClientIps(),
            'server' => request()->server(),
        ]);
    }
}
