<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
// Upar ye import zaroori hai
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    public function index(Request $request) {
        $categories = Category::latest();

        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%'.$request->get('keyword').'%');
        }

        $categories = $categories->paginate(10);
        return view('admin.category.list', compact('categories'));
    }

    public function create() {
        return view('admin.category.create');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
            'status' => 'required',
            'showHome' => 'required|in:Yes,No' // Yahan showHome validation add kiya
        ]);

        if ($validator->passes()) {

            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome; // Yahan database save logic add kiya
            $category->save();

       // STORE METHOD MEIN JAHAN IMAGE SAVE HOTI HAI WAHAN YE CODE REPLACE KAREIN:
if (!empty($request->image_id)) {
    $tempImage = TempImage::find($request->image_id);
    $extArray = explode('.', $tempImage->name);
    $ext = last($extArray);

    $newImageName = $category->id . '.' . $ext;
    $sPath = public_path() . '/temp/' . $tempImage->name;
    $dPath = public_path() . '/uploads/category/' . $newImageName;

    File::copy($sPath, $dPath);

    // Generate Image Thumbnail (Intervention Image v3)
    $thumbPath = public_path() . '/uploads/category/thumb/' . $newImageName;

    $manager = new ImageManager(new Driver());
    $imageManager = $manager->read($sPath);
    $imageManager->cover(450, 600);
    $imageManager->save($thumbPath);

    $category->image = $newImageName;
    $category->save();
}
            $request->session()->flash('success', 'Category added successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($category, Request $request) {
        $category = Category::find($category);
        if (empty($category)) {
            $request->session()->flash('error', 'Category not found');
            return redirect()->route('categories.index');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update($categoryId, Request $request) {
        $category = Category::find($categoryId);
        if (empty($category)) {
            $request->session()->flash('error', 'Category not found');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,'.$category->id.',id',
            'status' => 'required',
            'showHome' => 'required|in:Yes,No' // Yahan showHome validation add kiya
        ]);

        if ($validator->passes()) {

            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->showHome = $request->showHome; // Yahan database update logic add kiya
            $category->save();

            $oldImage = $category->image;

            // Save Image Here
         // UPDATE METHOD MEIN JAHAN IMAGE UPDATE HOTI HAI WAHAN YE CODE REPLACE KAREIN:
if (!empty($request->image_id)) {
    $tempImage = TempImage::find($request->image_id);
    $extArray = explode('.', $tempImage->name);
    $ext = last($extArray);

    $newImageName = $category->id . '-' . time() . '.' . $ext;
    $sPath = public_path() . '/temp/' . $tempImage->name;
    $dPath = public_path() . '/uploads/category/' . $newImageName;

    File::copy($sPath, $dPath);

    // Generate Image Thumbnail (Intervention Image v3)
    $thumbPath = public_path() . '/uploads/category/thumb/' . $newImageName;

    $manager = new ImageManager(new Driver());
    $imageManager = $manager->read($sPath);
    $imageManager->cover(450, 600);
    $imageManager->save($thumbPath);

    $category->image = $newImageName;
    $category->save();

    // Delete old images
    File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
    File::delete(public_path() . '/uploads/category/' . $oldImage);
}

            $request->session()->flash('success', 'Category updated successfully');

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($categoryId, Request $request) {
        $category = Category::find($categoryId);
        if (empty($category)) {
            $request->session()->flash('error', 'Category not found');
            return response()->json([
                'status' => true,
                'message' => 'Category not found'
            ]);
        }

        // Delete Old Images Here
        File::delete(public_path().'/uploads/category/thumb/'.$category->image);
        File::delete(public_path().'/uploads/category/'.$category->image);

        $category->delete();

        $request->session()->flash('success', 'Category deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
