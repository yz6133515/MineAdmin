<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace App\Http\Admin\Controller;

use App\Http\Admin\Middleware\AuthMiddleware;
use App\Http\Admin\Request\Passport\LoginRequest;
use App\Http\Admin\Request\UserRequest;
use App\Http\Common\Controller\AbstractController;
use App\Http\Common\Result;
use App\Service\Permission\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\Swagger\Annotation as OA;
use Hyperf\Swagger\Annotation\Post;
use Hyperf\Swagger\Request\SwaggerRequest;
use Psr\Http\Message\ResponseInterface;

/**
 * Class LoginController.
 */
#[OA\HyperfServer(name: 'http')]
class PassportController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ){}

    /**
     * 登录.
     */
    #[OA\Post(
        path: '/admin/passport/login',
        operationId: 'passportLogin',
        summary: '系统登录',
        tags: ['admin:passport']
    )]
    #[OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property('username', description: '用户名', type: 'string', example: 'admin',rules: 'required'),
            new OA\Property('password', description: '密码',  type: 'string', example: '123456',rules: 'required'),
        ]
    ))]
    public function login(SwaggerRequest $request): Result
    {
        $username = $request->input('username');
        $password = $request->input('password');
        return $this->success(
            $this->userService->login(
                $username,
                $password
            )
        );
    }

    /**
     * 退出.
     */
    #[PostMapping('logout')]
    #[Middleware(AuthMiddleware::class)]
    public function logout(): ResponseInterface
    {
        $this->userService->logout();
        return $this->success();
    }

    /**
     * 用户信息.
     */
    #[GetMapping('getInfo')]
    #[Middleware(AuthMiddleware::class)]
    public function getInfo(): ResponseInterface
    {
        return $this->success($this->systemUserService->getInfo());
    }

    /**
     * 刷新token.
     */
    #[PostMapping('refresh')]
    #[Middleware(AuthMiddleware::class)]
    public function refresh(LoginUser $user): Result
    {
        return $this->success(['token' => $user->refresh()]);
    }

    #[OA\Get(
        path: 'getBingBackgroundImage',
        operationId: 'getBingBackgroundImage',
        description: '获取每日的必应背景图',
    )]
    #[OA\Response(
        response: 200,
        description: '成功',
        content: new OA\JsonContent(example: '{
  "images": [
    {
      "startdate": "20240726",
      "fullstartdate": "202407261600",
      "enddate": "20240727",
      "url": "/th?id=OHR.RhinelandVineyards_ZH-CN3332101688_1920x1080.jpg&rf=LaDigue_1920x1080.jpg&pid=hp",
      "urlbase": "/th?id=OHR.RhinelandVineyards_ZH-CN3332101688",
      "copyright": "摩泽尔河谷的葡萄园，莱茵兰-法尔茨，德国 (© Jorg Greuel/Getty Images)",
      "copyrightlink": "https://www.bing.com/search?q=%E6%B3%95%E5%B0%94%E8%8C%A8%E8%91%A1%E8%90%84%E9%85%92%E4%BA%A7%E5%8C%BA&form=hpcapt&mkt=zh-cn",
      "title": "完美的葡萄酒",
      "quiz": "/search?q=Bing+homepage+quiz&filters=WQOskey:%22HPQuiz_20240726_RhinelandVineyards%22&FORM=HPQUIZ",
      "wp": true,
      "hsh": "4d0805d3edb368d9cebf56b7376cd938",
      "drk": 1,
      "top": 1,
      "bot": 1,
      "hs": [
        
      ]
    }
  ],
  "tooltips": {
    "loading": "正在加载...",
    "previous": "上一个图像",
    "next": "下一个图像",
    "walle": "此图片不能下载用作壁纸。",
    "walls": "下载今日美图。仅限用作桌面壁纸。"
  }
}')
    )]
    public function getBingBackgroundImage(): Result
    {
        try {
            $response = file_get_contents('https://cn.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1');
            $content = json_decode($response);
            if (! empty($content?->images[0]?->url)) {
                return $this->success([
                    'url' => 'https://cn.bing.com' . $content?->images[0]?->url,
                ]);
            }
            throw new \Exception();
        } catch (\Exception $e) {
            return $this->error('获取必应背景失败');
        }
    }
}
