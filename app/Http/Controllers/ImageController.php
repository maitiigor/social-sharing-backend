<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Http\Requests\UpdateImageRequest;
use App\Models\Image;
use Illuminate\Container\Attributes\Storage;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreImageRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        //


        $content = \Illuminate\Support\Facades\Storage::disk($image->disk)->get($image->url);
        return response($content, 200, [
            "Content-Type" => $this->getContentType(explode(".", $image->url)[0])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateImageRequest $request, Image $image)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        //
        $image->delete();

        return response()->json(null, 204);
    }

    public function getContentType($extension)
    {

        switch ($extension) {
            case 'png':
                # code...
                return 'image/png';
            case 'jpeg':
                return 'image/jpeg';
            case 'jpg':
                return 'image/jpg';
            default:
                # code...
                break;
        }
    }
}
