<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Categories::all();
        return response()->json($categories);
    }

    public function find($id)
    {
        $category = Categories::find($id);
        if (!$category) $category = [];
        return response()->json($category);
    }

    public function findByType($id)
    {
        $category = Categories::where('type_id', $id)->get();
        if (!$category) $category = [];
        return response()->json($category);
    }

    public function create(Request $request)
    {
        try {

            $this->validate($request, [
                'name' => 'required|string',
                'type_id' => 'required',
                'icon' => 'required'
            ]);

            $category = new Categories();
            $category->name = $request->input("name");
            $category->type_id = $request->input("type_id");
            $category->icon = $request->input("icon");
            $category->fill = $request->input("fill");
            $category->save();

            return response()->json([
                "isSuccess" => true,
                "message" => "Category Successfuly Created",
                "data" => $category
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "isSuccess" => false,
                "message" => $e->getMessage()
            ], 409);
        }
    }

    public function update($id, Request $request)
    {
        try {

            $category = Categories::find($id);
            if ($request->has("name")) $category->name = $request->input("name");
            if ($request->has("icon")) $category->icon = $request->input("icon");
            if ($request->has("fill")) $category->fill = $request->input("fill");
            $category->save();

            return response()->json([
                "isSuccess" => true,
                "message" => "Category Successfuly Updated",
                "data" => $category
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                "isSuccess" => false,
                "message" => $e->getMessage()
            ], 409);
        }
    }

    public function delete($id)
    {
        $category = Categories::find($id);
        if (!$category) {
            return response()->json([
                "isSuccess" => false,
                "message" => "Data not available"
            ]);
        }
        $category->delete();

        return response()->json([
            "isSuccess" => true,
            "message" => "Category Successfuly Deleted"
        ]);
    }
}
