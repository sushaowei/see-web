<?php
$configApp = [
    //项目id， 必设
    'id'=>'see',
    //项目根目录， 必设
    'basePath' => dirname(__DIR__),
    //默认false, 设置是否开发环境
    'envDev'=>true,
    //组件， 选设
    "components"=>[
        //数据库设置
        "db"=>[
            'dns'=>'mysql:dbname=see;port=3306;host=localhost;charset=utf8',
            'user'=>'test',
            'password'=>'test'
        ],
        //模板设置
        "view"=>[
            //使用smarty
            "renderers"=>[
                "tpl"=>[
                    'class'=>'\see\web\ViewRender',
                    "options"=>[
                        'left_delimiter'=>"{{",
                        "right_delimiter"=>"}}",
                    ],
                    //此项配置,会把数组中的项目assgin到smarty模板中
                    "imports"=>[
                        "H"=>'see\helper\Helper',
                    ],
                ],
            ],
            //模板扩展名， 默认php
            "defaultExtension"=>"tpl",
        ],

        //路由管理类
        "urlManager"=>[
//            'class'=>'app\helper\UrlManager',//设置URL解析类, 默认使用框架内置类
            //开启路由美化
            'pretty'=>true,
            //使用伪静态后缀
//            'suffix'=>'.html',
            //是否显示脚本文件名
            'showScriptFile'=>false,
        ],

        //缓存类，默认memcached
        "cache"=>[
            //服务器， ［ip,port,权重］
            "servers"=>[
                ["127.0.0.1", "11211", 100],
            ],
            //缓存key前缀, 默认无
            "prefix"=>"see_",
            //memcache option
            "options"=>[
//                \Memcached::OPT_COMPRESSION=>false,
//                \Memcached::OPT_BINARY_PROTOCOL=>true,
            ],
        ],
        //日志设置
        "log"=>[
            //日志名后缀使用日期
            "suffix"=>'date',
        ],
    ],
    'modules'=>[
        'test'=>[
            'class'=>'\app\modules\test\Test',
            'events'=>[
                'ModulesTestHandler'=>'\app\modules\test\events\ModulesTestHandler',
            ],
        ],
    ],
    'events'=>[
        'TestHandler'=>'\app\events\TestHandler',
    ],
];

if (isset($_SERVER['CONF_FILE']) && file_exists($_SERVER['CONF_FILE'])) {
    //引入外界配置
    \Conf::initConf($_SERVER['CONF_FILE']);
    $configApp["components"]['db'] = \Conf::get("db168", "global", $_SERVER['ENV']);
    $configApp["components"]['dbmc'] = \Conf::get("dbmc", "global", $_SERVER['ENV']);
    $configApp["components"]['log'] = \Conf::get("logTrace", "global", $_SERVER['ENV']);

    // $configApp["components"]['log']['level'] = \Conf::get("log_level", "saas", $_SERVER['ENV']);
    // $configApp["components"]['cache'] = \Conf::get("memcache", "saas", $_SERVER['ENV']);
    // $configApp['envDev'] = \Conf::get("envDev", "saas", $_SERVER['ENV']);
    // $configApp['debug'] = \Conf::get("debug", "saas", $_SERVER['ENV']);

    defined('IMG_URL_MC') or define('IMG_URL', \Conf::get("IMG_URL_MC", "global", $_SERVER['ENV']));   //图片访问url
    defined('IMG_URL_168') or define('STATIC_IMG_URL', \Conf::get("IMG_URL_168", "global", $_SERVER['ENV']));   //图片访问url
    defined('BASE_API') or define('BASE_API_HOST', \Conf::get("BASE_API", "global", $_SERVER['ENV']));   //baseapi
}
return $configApp;
