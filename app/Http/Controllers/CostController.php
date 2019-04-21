<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class CostController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * 返回是否是助理或者经理
     */
    public function rule()
    {
        $authorize = false;
        $users = $this->UserAuthorizeCollects();
        if (count($users) > 1)
            $authorize = true;

        return response(["status" => "success", "users" => $users,"authorize" => $authorize], 200);
    }
}
