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

//引入外界配置
$configLocal = "xxxx";
if( file_exists($configLocal)){
    $local = require ($configLocal);
    if(!empty($local['components'])){
        $configApp['components'] = array_merge($local['components']);
    }
    if(isset($local['envDev'])){
        $configApp['envDev'] = $local['envDev'];
    }
}
return $configApp;