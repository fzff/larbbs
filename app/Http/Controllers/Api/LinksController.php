<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Transformers\LinkTransformer;
use Illuminate\Http\Request;

class LinksController extends Controller
{
    public function index(Link $link)
    {
        $link_data = $link->getAllCached();

        return $this->response->collection($link_data, new LinkTransformer());
    }
}
