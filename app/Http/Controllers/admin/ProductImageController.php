<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductImage;
use Illuminate\Support\Facades\File;
// Purana facade hata diya gaya hai
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductImageController extends Controller
{
    public function update(Request $request) {

        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        // Yeh line source path nikalegi
        $sourcePath = $image->getPathName();

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL';
        $productImage->save();

        $imageName = $request->product_id.'-'.$productImage->id.'-'.time().'.'.$ext;
        $productImage->image = $imageName;
        $productImage->save();

        // ==========================================
        // Generate Product Thumbnails (Version 3.0)
        // ==========================================

        // Large Image
        $destPathLarge = public_path() . '/uploads/product/large/' . $imageName;

        $manager = new ImageManager(new Driver());
        $imageManagerLarge = $manager->read($sourcePath);
        $imageManagerLarge->scaleDown(1400);
        $imageManagerLarge->save($destPathLarge);

        // Small Image
        $destPathSmall = public_path() . '/uploads/product/small/' . $imageName;

        $manager = new ImageManager(new Driver());
        $imageManagerSmall = $manager->read($sourcePath);
        $imageManagerSmall->cover(300, 300);
        $imageManagerSmall->save($destPathSmall);


        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset('uploads/product/small/'.$productImage->image),
            'message' => 'Image saved successfully'
        ]);
    }

    public function destroy(Request $request) {
        $productImage = ProductImage::find($request->id);

        if (empty($productImage)) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ]);
        }

        // Delete images from folder
        File::delete(public_path('uploads/product/large/'.$productImage->image));
        File::delete(public_path('uploads/product/small/'.$productImage->image));

        // Delete record from database
        $productImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
