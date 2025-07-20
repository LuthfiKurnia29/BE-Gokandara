<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Refrensi;
use Illuminate\Http\Request;

class RefrensiController extends Controller
{
    //
    public function getAllRefrence(){
        $dataReference = Refrensi::all();
        if($dataReference != null){
            return response()->json([
                'success' => true,
                'message' => 'success get all data reference',
                'data' => $dataReference
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'error when get data',
            ], 403);
        }
    }
}
