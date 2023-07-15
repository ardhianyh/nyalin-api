<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::all();
        return response()->json($transactions);
    }

    public function find($id)
    {
        $transaction = Transaction::find($id);
        if (!$transaction) $transaction = [];
        return response()->json($transaction);
    }

    public function findByUserId(Request $request)
    {
        try {
            $id = $this->getPayloadJWT('id');
            $limit = $request->query('limit');
            $type = $request->query('type');
            $keyword = $request->query('keyword');
            if ($type && $type !== "" && $type !== null) {
                if ($keyword && $keyword !== "" && $keyword !== null) {
                    $transaction = DB::table('transactions')
                        ->join('categories', 'transactions.category_id', '=', 'categories.id')
                        ->select('transactions.id as id', 'transactions.type_id as type_id', 'categories.id as category_id', 'categories.name as category', 'transactions.note as note', 'transactions.amount as amount', 'transactions.date as date', 'transactions.created_at as created_at')
                        ->where('transactions.user_id', $id)
                        ->where('transactions.type_id', $type)
                        ->where('transactions.note', 'like', '%' . $keyword . '%')
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();
                } else {
                    $transaction = DB::table('transactions')
                        ->join('categories', 'transactions.category_id', '=', 'categories.id')
                        ->select('transactions.id as id', 'transactions.type_id as type_id', 'categories.id as category_id', 'categories.name as category', 'transactions.note as note', 'transactions.amount as amount', 'transactions.date as date', 'transactions.created_at as created_at')
                        ->where('transactions.user_id', $id)
                        ->where('transactions.type_id', $type)
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();
                }
            } else {
                if ($keyword && $keyword !== "" && $keyword !== null) {
                    $transaction = DB::table('transactions')
                        ->join('categories', 'transactions.category_id', '=', 'categories.id')
                        ->select('transactions.id as id', 'transactions.type_id as type_id', 'categories.id as category_id', 'categories.name as category', 'transactions.note as note', 'transactions.amount as amount', 'transactions.date as date', 'transactions.created_at as created_at')
                        ->where('transactions.user_id', $id)
                        ->where('transactions.note', 'like', '%' . $keyword . '%')
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();
                } else {
                    $transaction = DB::table('transactions')
                        ->join('categories', 'transactions.category_id', '=', 'categories.id')
                        ->select('transactions.id as id', 'transactions.type_id as type_id', 'categories.id as category_id', 'categories.name as category', 'transactions.note as note', 'transactions.amount as amount', 'transactions.date as date', 'transactions.created_at as created_at')
                        ->where('transactions.user_id', $id)
                        ->orderByDesc('created_at')
                        ->limit($limit)
                        ->get();
                }
            }
            if (!$transaction) $transaction = [];
            return response()->json($transaction);
        } catch (\Throwable $e) {
            return response()->json([
                "isSuccess" => false,
                "message" => $e->getMessage()
            ], 409);
        }
    }

    public function findByCategory($id)
    {
        try {
            $user_id = $this->getPayloadJWT('id');
            $transaction = DB::table('transactions')
                ->join('categories', 'transactions.category_id', '=', 'categories.id')
                ->select('transactions.id as id', 'transactions.type_id as type_id', 'categories.name as category', 'transactions.note as note', 'transactions.amount as amount', 'transactions.date as date', 'transactions.created_at as created_at')
                ->where('transactions.user_id', $user_id)
                ->where('transactions.category_id', $id)
                ->orderByDesc('created_at')
                ->get();
            if (!$transaction) $transaction = [];
            return response()->json($transaction);
        } catch (\Throwable $th) {
            return response()->json([
                "isSuccess" => false,
                "message" => $th->getMessage()
            ], 409);
        }
    }

    public function filter(Request $request)
    {
        try {
            $user_id = $this->getPayloadJWT('id');
            $range = $request->query('range');
            $type = $request->query('type');
            $chart = [];

            if ($range == "daily") {
                $data = DB::table('transactions')
                    ->join('categories', 'transactions.category_id', '=', 'categories.id')
                    ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
                    ->where('transactions.type_id', $type)
                    ->where('transactions.user_id', $user_id)
                    ->where(DB::raw('DATE(transactions.date)'), date('Y-m-d'))
                    ->orderBy('categories.name')
                    ->get();
            } elseif ($range == "monthly") {
                $data = DB::table('transactions')
                    ->join('categories', 'transactions.category_id', '=', 'categories.id')
                    ->select('categories.id as id', 'categories.name as key', 'categories.fill as fill', 'categories.icon as icon', 'transactions.amount')
                    ->where('transactions.type_id', $type)
                    ->where('transactions.user_id', $user_id)
                    ->where('transactions.date', '>=', Carbon::now()->subMonth()->toDateTimeString())
                    ->orderBy('categories.name')
                    ->get();
            } elseif ($range == "yearly") {
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

    public function create(Request $request)
    {
        try {
            $this->validate($request, [
                'user_id' => 'required',
                'type_id' => 'required',
                'category_id' => 'required',
                'note' => 'required|string',
                'amount' => 'required',
                'date' => 'required'
            ]);

            $transaction = new Transaction();
            $transaction->user_id = $request->input("user_id");
            $transaction->type_id = $request->input("type_id");
            $transaction->category_id = $request->input("category_id");
            $transaction->note = $request->input("note");
            $transaction->amount = $request->input("amount");
            $transaction->date = $request->input("date");
            $transaction->save();

            return response()->json([
                "isSuccess" => true,
                "message" => "Transaction Successfuly Created",
                "data" => $transaction
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

            $transaction = Transaction::find($id);
            if ($request->has("note")) $transaction->note = $request->input("note");
            if ($request->has("amount")) $transaction->amount = $request->input("amount");
            if ($request->has("date")) $transaction->date = $request->input("date");
            $transaction->save();

            return response()->json([
                "isSuccess" => true,
                "message" => "Transaction Successfuly Updated",
                "data" => $transaction
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
        $transaction = Transaction::find($id);
        if (!$transaction) {
            return response()->json([
                "isSuccess" => false,
                "message" => "Data not available"
            ]);
        }
        $transaction->delete();
        return response()->json([
            "isSuccess" => true,
            "message" => "Transaction Successfuly Deleted"
        ]);
    }
}
