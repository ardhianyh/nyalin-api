<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Type;

class TypeController extends Controller
{
   public function index()
   {
      $type = Type::all();
      return response()->json($type);
   }

   public function find($id)
   {
      $type = Type::find($id);
      if (!$type) $type = [];
      return response()->json($type);
   }

   public function create(Request $request)
   {
      try {
         $this->validate($request, [
            'name' => 'required|string|unique:type'
         ]);

         $type = new Type();
         $type->name = $request->input("name");
         $type->save();

         return response()->json([
            "isSuccess" => true,
            "message" => "Type Successfuly Created",
            "data" => $type
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
         $this->validate($request, [
            'name' => 'required|string|unique:type'
         ]);

         $type = Type::find($id);
         $type->name = $request->input("name");
         $type->save();

         return response()->json([
            "isSuccess" => true,
            "message" => "Type Successfuly Updated",
            "data" => $type
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
      $type = Type::find($id);
      if (!$type) {
         return response()->json([
            "isSuccess" => false,
            "message" => "Data not available"
         ]);
      }
      $type->delete();
      return response()->json([
         "isSuccess" => true,
         "message" => "Type Successfuly Deleted"
      ]);
   }
}
