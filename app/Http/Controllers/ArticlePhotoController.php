<?php

namespace App\Http\Controllers;

use App\User;
use App\ArticlePhoto;
use App\Http\Resources\ArticlePhotoOneResource;
use Illuminate\Http\Request;
use App\Http\Requests\ArticlePhotoStoreRequest;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Database\QueryException;

class ArticlePhotoController extends Controller
{
    protected $user;
    protected $filesystem;
    protected $photoModel;

    public function __construct(FilesystemManager $FilesystemManager, ArticlePhoto $photoModel, User $user)
    {
        $this->filesystem = $FilesystemManager->disk('public');
        $this->photoModel = $photoModel;
        $this->user = $user;
    }

    //随机返回一个图册
    public function one(Request $request)
    {
        $data = $this->photoModel->inRandomOrder()->first();
        return response(['status' => 'success','data' => new ArticlePhotoOneResource($data)], 200);
    }
    public function store(ArticlePhotoStoreRequest $request)
    {
        $data = [];
        $data['title'] = $request->title;
        $data['user_id'] = $this->getUserId();
        $data['department_id'] = $this->user->find($data['user_id'])->department_id;
        $data['counts'] = count($request->list);
        $data['content'] = json_encode($request->list);

        try {
            if ($this->photoModel->create($data)) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()], 200);
        }
    }

}
