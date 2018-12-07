<?php

namespace App\Http\Controllers;

use App\PotentialProject;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Requests\PotentialProjectStoreRequest;

class PotentialProjectController extends Controller
{
    public function store(PotentialProjectStoreRequest $request)
    {
        $data = $request->all();
        $data['user_id'] = $this->getUserId();

        try {
            if (PotentialProject::create($data)) {
                return response(['status' => 'success'], 200);
            }
        }
        catch (QueryException $e) {
            return response(['status' => 'error', 'errmsg' => $e->getMessage()]);
        }
    }
}
