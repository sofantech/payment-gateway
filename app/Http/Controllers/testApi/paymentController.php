<?php

namespace App\Http\Controllers\testApi;

use App\Http\Controllers\Controller;
use App\Http\Traits\general_trait;
use App\Models\Orders_invoice;
use App\Models\User;
use Dotenv\Exception\ValidationException;
use Illuminate\Http\Request;

class paymentController extends Controller
{
    use general_trait;
    public function payment_order(Request $request){
        // try{
        //     $this->validate($request,[
        //         'secret_key'=>'required',
        //         'public_key'=>'required',
        //         'products'=>'required',
        //         'total_amout'=>'required',
        //         'currency'=>'required',
    
        //     ]);

        // }
        // catch(ValidationException $e){
        //     return response()->json($e->validator->errors(),422);
        // }
        
        $info=array('refrence_id'=>1,'products'=>[array('id'=>1,'name'=>'laptop','quantity'=>2,'unint_amount'=>3000),array('id'=>3,'name'=>'laptop','quantity'=>5,'unint_amount'=>100)]);
        $private_key=$request->header('private_key');
        $public_key=$request->header('public_key');
        $products=json_decode($request->input('products'),true);
        $order_reference=$request->input('order_reference');
        $total_amount=$request->input('total_amout');
        $currency=$request->input('currency');
        $meta_data=$request->input('meta_data');
        $sucess_url=$request->input('success_url');
        $cancel_url=$request->input('cancel_url');
        $order_details=$request->all();
        if(!is_array($products))
        return $this->errors(300,5100,'invalid products array format');
        if($private_key==null|| $public_key==null)
        return $this->errors(500,5200,'invalid credintical keys');

        if($private_key=='1234567890' && $public_key=='abcde')
        {
            return $this->create_order($order_details,$public_key,$private_key);
        }
        else
        {
            return $this->returnError('408',"تاكد من كتابة البيانات بشكل صحيح");
        }
        //  return response($this->create_order($info),200);

    }
    public function generate_string($strength = 16) {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($input);
        $random_string = '';
        for($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
     
        return $random_string;
    }
    public function create_order($order_details,$public_key,$private_key){
        $merchant_data=User::where('private_key',$private_key)->first();
        $feedback=array("invoice_referance"=>$this->generate_string(10),"expires_on"=>date("h:i:s a m/d/Y",strtotime('+24 hours')));
        $order_invoice_url=array("success_url"=>"http://localhost:8000/api/test/merchant/do_payment_order/".$feedback['invoice_referance'],"cancel_url"=>"http://localhost:8000/api/test/merchant/cancel_payment_order/{".$feedback['invoice_referance']."}/cancel_payment");
        $orders=array_merge($feedback,$order_details,$order_invoice_url);
        $invoice=new Orders_invoice();
        $invoice->invoice_referance=$orders['invoice_referance'];
        $invoice->user_id=$merchant_data->id;
        $invoice->products=$orders['products'];
        $invoice->order_reference=$orders['order_reference'];
        $invoice->total_amout=$orders['total_amout'];
        $invoice->currency=$orders['currency'];
        $invoice->success_url=$orders['success_url'];
        $invoice->cancel_url=$orders['cancel_url'];
        if($invoice->save()){
            return $this->returnData('invoice',$invoice,'invoice created successfuly');

        }
        
        

    }
    public function errors($response_code,$code,$message){
        return response()->json(array('code'=>$code,'message'=>$message),$response_code);
    }
    public function do_payment($invoice_referance){
        $invoice_data=Orders_invoice::where('invoice_referance',$invoice_referance)->get();
        // return $invoice_data;
        return view('paymentView.paymentView')->with('invoice_data', $invoice_data);

    }
}
