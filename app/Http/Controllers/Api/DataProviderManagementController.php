<?php

namespace App\Http\Controllers\Api;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Http\Response;

class  DataProviderManagementController extends Controller
{

    
    public static function getDataProviderW(){
        $getDataProviderW = file_get_contents(public_path('components/DataProviderW.json'));
        $ProviderW = json_decode($getDataProviderW,true);
        return collect($ProviderW);
    }
    
    public static function getDataProviderX(){
        $getDataProviderX = file_get_contents(public_path('/components/DataProviderX.json'));
        $ProviderX = json_decode($getDataProviderX,true);
        return collect($ProviderX);
    }
    
    public static function getDataProviderY(){
        $getDataProviderY = file_get_contents(public_path('/components/DataProviderY.json'));
        $ProviderY = json_decode($getDataProviderY,true);
        return collect($ProviderY);
    }

    public function index()
    {
        //data collection
        $getDataProviderW = self::getDataProviderW();
        $getDataProviderX = self::getDataProviderX();
        $getDataProviderY = self::getDataProviderY();
        //data collection
        
        //Start if has Provider
        if (request()->has('provider')) {
            
            $content = collect(array_filter([
                ((request()->provider == 'DataProviderW')?  $getDataProviderW:null ),
                ((request()->provider == 'DataProviderX')?  $getDataProviderX:null ),
                ((request()->provider == 'DataProviderY')?  $getDataProviderY:null )
            ]));

        }else{
           
            $content = collect(array_filter([
                $getDataProviderW,
                $getDataProviderX,
                $getDataProviderY,
               
            ]));

        }
        //End if has Provider

      $data_map =   $content->map(function($item) use ($content){
                return [

                   'id' =>  (string)
                            ( (isset($item['id'])  )
                                        ? 
                                        $item['id']
                                         : 
                                         $item['transactionIdentification']
                                     ),

                   'created_at' => ( (isset($item['created_at']) )
                                        ?
                                            $item['created_at'] 
                                        :
                                            $item['transactionDate']
                                    ),
                    'phone' => ( (isset($item['phone']) )
                                        ?
                                            $item['phone'] 
                                        :
                                            $item['senderPhone']
                                    ),
                   'amount' => ( (isset($item['amount']) )
                                        ?
                                            $item['amount'] 
                                        :
                                            $item['transactionAmount']
                                    ),

                   'currency' => ( (isset($item['currency']) )
                                        ?
                                            $item['currency'] 
                                        :
                                            $item['Currency']
                                    ),
                    'status' =>
                    ((isset($item['status'])) ? 
                        self::FunStatus($item['status'])  
                        :
                        self::FunTransactionStatus($item['transactionStatus'])
                    ),
                ];
                  
            });

    $data =  $data_map;
    
    if (request()->has('statusCode')) {
        $data =  $data->where('status',request()->statusCode);
    }

    if (request()->has('currency')) {
        $data =  $data->where('currency',request()->currency);
    }

    if (request()->has('amounteMin') && request()->has('amounteMax')  ) {
        $data =  $data->whereBetween('amount',[request()->amounteMin,request()->amounteMax]);
    }


        return response()->json($data);

    }

    public static function FunStatus($status)
    {
        if (in_array($status,['done',1,100])) {
            return "paid";
        }elseif(in_array($status,['pending',2,200])){
            return "pending";
        }else{
            return "reject";
        }
    }

    public static function FunTransactionStatus($transactionStatus)
    {
        if (in_array($transactionStatus,['done',1,100])) {
            return "paid";
        }elseif(in_array($transactionStatus,['wait',2,200])){
            return "pending";
        }else{
            return "reject";
        }
    }

}
