<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomController extends Controller
{
   public function homepage()
   {
      try {
         $id = $this->getPayloadJWT('id');
         $transactions = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('transactions.id as id', 'transactions.type_id as type_id', 'categories.name as category', 'transactions.note as note', 'transactions.amount as amount', 'transactions.date as date', 'transactions.created_at as created_at')
            ->where('transactions.user_id', $id)
            ->orderByDesc('created_at')
            ->get();
         $total_income = 0;
         $total_outcome = 0;
         $last_transaction = [];
         foreach ($transactions as $key => $value) {
            if ($value->type_id == 1) {
               $total_income += $value->amount;
            }
            if ($value->type_id == 2) {
               $total_outcome += $value->amount;
            }
            if ($key < 5) {
               $last_transaction[$key]['id'] = $value->id;
               $last_transaction[$key]['type'] = $value->type_id;
               $last_transaction[$key]['note'] = $value->note;
               $last_transaction[$key]['amount'] = $value->amount;
               $last_transaction[$key]['category'] = $value->category;
               $last_transaction[$key]['created_at'] = $value->created_at;
            }
         }

         $chart = [];

         $income = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
            ->where('transactions.type_id', 1)
            ->where('transactions.user_id', $id)
            ->whereYear('transactions.date', date('Y'))
            ->orderBy('categories.name')
            ->get();

         $duplicate = "";
         $index = 0;
         foreach ($income as $key => $value) {
            if ($value->key == $duplicate) {
               $chart['income'][$index - 1]['amount'] = $chart['income'][$index - 1]['amount'] + $value->amount;
               continue;
            }
            $chart['income'][$index]['id'] = $value->id;
            $chart['income'][$index]['key'] = $value->key;
            $chart['income'][$index]['amount'] = $value->amount;
            $chart['income'][$index]['fill'] = $value->fill;
            $chart['income'][$index]['icon'] = $value->icon;
            $duplicate = $value->key;
            $index++;
         }

         $outcome = DB::table('transactions')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
            ->where('transactions.type_id', 2)
            ->where('transactions.user_id', $id)
            ->whereYear('transactions.date', date('Y'))
            ->orderBy('categories.name')
            ->get();

         $duplicate = "";
         $index = 0;
         foreach ($outcome as $key => $value) {
            if ($value->key == $duplicate) {
               $chart['outcome'][$index - 1]['amount'] = $chart['outcome'][$index - 1]['amount'] + $value->amount;
               continue;
            }
            $chart['outcome'][$index]['id'] = $value->id;
            $chart['outcome'][$index]['key'] = $value->key;
            $chart['outcome'][$index]['amount'] = $value->amount;
            $chart['outcome'][$index]['fill'] = $value->fill;
            $chart['outcome'][$index]['icon'] = $value->icon;
            $duplicate = $value->key;
            $index++;
         }

         $total['income'] = $total_income;
         $total['outcome'] = $total_outcome;
         return response()->json([
            'total' => $total,
            'chart' => $chart,
            'last_transaction' => $last_transaction
         ]);
      } catch (\Throwable $e) {
         return response()->json([
            "isSuccess" => false,
            "message" => $e->getMessage()
         ], 409);
      }
   }

   public function transactionpage(Request $request)
   {

      try {
         $user_id = $this->getPayloadJWT('id');
         $type = $request->query('type');
         $filter = $request->query('filter');

         $chart = [];
         if ($filter == "daily") {
            $data = DB::table('transactions')
               ->join('categories', 'transactions.category_id', '=', 'categories.id')
               ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
               ->where('transactions.type_id', $type)
               ->where('transactions.user_id', $user_id)
               ->where(DB::raw('DATE(transactions.date)'), date('Y-m-d'))
               ->orderBy('categories.name')
               ->get();
         } elseif ($filter == "monthly") {
            $data = DB::table('transactions')
               ->join('categories', 'transactions.category_id', '=', 'categories.id')
               ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
               ->where('transactions.type_id', $type)
               ->where('transactions.user_id', $user_id)
               ->where('transactions.date', '>=', Carbon::now()->subMonth()->toDateTimeString())
               ->orderBy('categories.name')
               ->get();
         } elseif ($filter == "yearly") {
            $data = DB::table('transactions')
               ->join('categories', 'transactions.category_id', '=', 'categories.id')
               ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
               ->where('transactions.type_id', $type)
               ->where('transactions.user_id', $user_id)
               ->whereYear('transactions.date', date('Y'))
               ->orderBy('categories.name')
               ->get();
         }

         $duplicate = "";
         $index = 0;
         foreach ($data as $value) {
            if ($value->key == $duplicate) {
               $chart[$index - 1]['amount'] = $chart[$index - 1]['amount'] + $value->amount;
               continue;
            }
            $chart[$index]['id'] = $value->id;
            $chart[$index]['key'] = $value->key;
            $chart[$index]['amount'] = $value->amount;
            $chart[$index]['fill'] = $value->fill;
            $chart[$index]['icon'] = $value->icon;
            $duplicate = $value->key;
            $index++;
         }

         return response()->json($chart);
      } catch (\Throwable $e) {
         return response()->json([
            "isSuccess" => false,
            "message" => $e->getMessage()
         ], 409);
      }
   }
}
