<?php

namespace App\Http\Middleware;

use Auth;
use App\User;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

// 注意，我们要继承的是 jwt 的 BaseMiddleware
class RefreshTokenMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     *
     * @throws \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 检查此次请求中是否带有 token，如果没有则抛出异常。 
        if (! $token = $this->auth->setRequest($request)->getToken()) {
            // return $this->respond('tymon.jwt.absent', 'token_not_provided', 400);
            return $this->respond('tymon.jwt.absent', $request, 400);
        }

        try {

            $user = $this->auth->authenticate($token);


        } catch (TokenExpiredException $e) {
            // $response = $next($request);
            // $response->header('Authorization', $newToken);
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
            
            
            // $user = $this->auth->setToken($newToken)->toUser();
            // // 使用一次性登录以保证此次请求的成功
            // // $id = $this->auth->getPayload($newToken)->get('sub');
            // // $result = Auth::login($user, false);
            // // $user = User::find($id);
            // // var_dump($result);
            // // $result = Auth::guard('api')->setUser($user);
            // $result = Auth::guard('api')->onceUsingId($user->id);
            // // var_dump($user);
            

            // return $response;

        } catch (JWTException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        }

        if (! $user) {
            return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        //距离失效时间10分钟内刷新 exp: 单位秒
        $response = $next($request);
        // $payload = $this->auth->getPayload($this->auth->getToken());

        // if (($payload->get('exp') - time()) < 60*10) {
        //     // 刷新用户的 token
        //     $newToken = $this->auth->parseToken()->refresh();
        //     $response->header('Authorization', $newToken);
        // }

        return $response;
    }
}
