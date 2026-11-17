<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
// Naye Imports
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImagesController extends Controller
{
    public function create(Request $request) {
        $image = $request->image;

        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();

            // Pehle database mein dummy entry karenge
            $tempImage = new TempImage();
            $tempImage->name = 'test'; // Dummy name
            $tempImage->save();

            // Ab actual unique name banayenge ID ke sath (Duplicate bug fix)
            $newName = $tempImage->id . '-' . time() . '.' . $ext;

            // Database mein actual name update karenge
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path().'/temp', $newName);

            // Generate Thumbnail (Intervention Image v3)
            $sourcePath = public_path().'/temp/'.$newName;
            $destPath = public_path().'/temp/thumb/'.$newName;

            $manager = new ImageManager(new Driver());
            $imageManager = $manager->read($sourcePath);
            $imageManager->cover(300, 275);
            $imageManager->save($destPath);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'ImagePath' => asset('/temp/thumb/'.$newName),
                'message' => 'Image uploaded successfully'
            ]);
        }
    }
}
