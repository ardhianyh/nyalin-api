<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
   public function index()
   {
      $notes = Note::all();
      foreach ($notes as $key => $note) {
         $tags = explode(",", $note->tags);
         $notes[$key]->tags = $tags;
      }
      return response()->json($notes);
   }

   public function find($id)
   {
      $note = Note::find($id);
      $note->tags = explode(",", $note->tags);
      return response()->json($note);
   }

   public function findByUserId(Request $request)
   {
      $id = $this->getPayloadJWT('id');
      $limit = $request->query('limit');
      $notes = DB::table('notes')
         ->where('user_id', $id)
         ->orderByDesc('created_at')
         ->limit($limit)
         ->get();
      foreach ($notes as $key => $note) {
         $tags = explode(",", $note->tags);
         $notes[$key]->tags = $tags;
      }
      return response()->json($notes);
   }

   public function findByKeyword($keyword)
   {
      $id = $this->getPayloadJWT('id');
      $notes = DB::table('notes')
         ->where('note', 'like', '%' . $keyword . '%')
         ->where('user_id', $id)
         ->get();
      foreach ($notes as $key => $note) {
         $tags = explode(",", $note->tags);
         $notes[$key]->tags = $tags;
      }
      return response()->json($notes);
   }

   public function findByTag($tag)
   {
      $id = $this->getPayloadJWT('id');
      $notes = DB::table('notes')
         ->where('tags', 'like', '%' . $tag . '%')
         ->where('user_id', $id)
         ->get();
      foreach ($notes as $key => $note) {
         $tags = explode(",", $note->tags);
         $notes[$key]->tags = $tags;
      }
      return response()->json($notes);
   }

   public function create(Request $request)
   {
      try {
         $this->validate($request, [
            'user_id' => 'required',
            'title' => 'required',
            'note' => 'required|string'
         ]);

         $note = new Note();
         $note->user_id = $request->input("user_id");
         $note->title = $request->input("title");
         $note->note = $request->input("note");

         if ($request->has("tags")) {
            $note->tags = implode(",", $request->input("tags"));
         }

         $note->save();
         $note->tags = $request->input("tags");

         return response()->json([
            "isSuccess" => true,
            "message" => "Note Successfuly Created",
            "data" => $note
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
            'note' => 'required|string'
         ]);

         $note = Note::find($id);
         $note->note = $request->input("note");
         if ($request->has("title")) $note->title = $request->input("title");
         if ($request->has("tags")) {
            $note->tags = implode(",", $request->input("tags"));
         }

         $note->save();
         $note->tags = explode(",", $note->tags);

         return response()->json([
            "isSuccess" => true,
            "message" => "Note Successfuly Updated",
            "data" => $note
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
      $note = Note::find($id);
      if (!$note) {
         return response()->json([
            "isSuccess" => false,
            "message" => "Data not available"
         ]);
      }
      $note->delete();
      return response()->json([
         "isSuccess" => true,
         "message" => "Note Successfuly Deleted"
      ]);
   }
}
