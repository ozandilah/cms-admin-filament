<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\Store;
use App\Models\Listing;
use App\Models\Transaction;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function index() : JsonResponse
    {
        $transactions = Transaction::with('listing')->whereUserId(auth()->id())->paginate();

        return response()->json([
            'success'=>true,
            "message"=> "Get all my transactions",
            "data"=> $transactions,
        ]);
    }
    private function _fullyBookedChecker(Store $request) : JsonResponse
    {
        $listing = Listing::find($request->listing_id);
        $runningTransactionCount = Transaction::whereListingId($listing->id)
            ->whereNot('status','canceled')
            ->where(function($query) use ($request){
                $query->whereBetween('start_date',
                [
                    $request->start_date,
                    $request->end_date
                ])->orWhereBetween('end_date',  [
                    $request->start_date,
                    $request->end_date
                ])->orWhere(function($subquery) use ($request){
                    $subquery->where('start_date', '<',$request->start_date)->where('end_date','>', $request->end_date);
                });
            })->count();

            if($runningTransactionCount >= $listing-> max_person){
                throw new HttpResponseException(
                    response()->json([
                        "success" =>false,
                        "message"=> "Listing is fully booked",
                    ],JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
                );
            }

            return true;
    }


    public function isAvailable(Store $request) :JsonResponse
    {
        $this->_fullyBookedChecker($request);

        return response()->json([
            "success"=>true,
            "message"=> "Listing ready to book"
        ]);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function store(Store $request) : JsonResponse
    {
        $this->_fullyBookedChecker($request);
        $transaction = Transaction::create(
            [
                "start_date"=> $request->start_date,
                "end_date"=> $request->end_date,
                "listing_id"=> $request->listing_id,
                "user_id"=>auth()->id()
            ]
            );
            $transaction->Listing;
            return response()->json([
                'success'=>true,
                "message"=>'New Transaction Create',
                "data"=>$transaction
            ]);
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/
    public function show(Transaction $transaction):JsonResponse
    {
        if($transaction->user_id !== auth()->id()){
            return response()->json(
                [
                    "success"=> false,
                    "message"=>"Unauthorized"
                ], JsonResponse::HTTP_UNAUTHORIZED
            );
        }
        $transaction->Listing;

        return response()->json([
            "success"=>true,
            "message"=>"Get detail transaction",
            "data"=>$transaction
        ]);
    }
}
