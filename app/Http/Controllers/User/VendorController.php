<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Rentalbro\Models\Mysql\Product;
use Rentalbro\Models\Mysql\ProductImage;
use Rentalbro\Models\Mysql\ProductPrice;
use Rentalbro\Models\Mysql\Vendor;
use Rentalbro\Models\Mysql\VendorLocation;
use Tymon\JWTAuth\JWTAuth;
use Validator;

class VendorController extends ApiController
{
	public function nicknameCheck()
	{
		$rules = [
            'nick_name'	=> 'required',
        ];

    	$validator = Validator::make(
    		$this->request->all(),
    		$rules
		);

        if ($validator->fails())
            return $this->response()->error($validator->errors()->all());

        $vendor = Vendor::where('nickname', $this->request->nick_name)->first();

        if($vendor)
        	return $this->response()->error(['Nickname Already exist'], 401);

        return $this->response()->success(true);
	}

	public function profile($nickname, JWTAuth $JWTAuth)
	{
		$user =  $JWTAuth->parseToken()->authenticate();
		$vendor = Vendor::where('nickname', $nickname)->where('user_ecommerce_id', $user->id)->first();
		if(! $vendor)
			return $this->response()->error(['Vendor Not Found'], 400);

		$token = $JWTAuth->getToken();
		return $this->response()->success($vendor, ['meta.token' => (string) $token]);
	}

	public function create(JWTAuth $JWTAuth)
	{
		$rules = [
            'nick_name'	=> 'required',
            'full_name'	=> 'required',
        ];

    	$validator = Validator::make(
    		$this->request->all(),
    		$rules
		);

        if ($validator->fails())
            return $this->response()->error($validator->errors()->all());
		
		$user =  $JWTAuth->parseToken()->authenticate();
		$vendor = new Vendor;
		$vendor->user_ecommerce_id = $user->id;
		$vendor->nickname = $this->request->nick_name;
		$vendor->full_name = $this->request->full_name;
		$vendor->motto = $this->request->motto;
		$vendor->description = $this->request->description;
		$vendor->logo = $this->request->logo;
		$vendor->save();		

		$vendorLocation = new VendorLocation;
		$vendorLocation->vendor_id = $vendor->id;
		$vendorLocation->district_id = $this->request->district;
		$vendorLocation->zip_code = $this->request->zip_code;
		$vendorLocation->detail_location = $this->request->detail_location;
		$vendorLocation->save();

		return $this->response()->success($vendor);
	}

	public function product_add($nickname, JWTAuth $JWTAuth)
    {
    	$user =  $JWTAuth->parseToken()->authenticate();
        $token = $JWTAuth->fromUser($user);
    	$vendor = Vendor::whereRaw('nickname = "'.strtolower($nickname).'"')->where('user_ecommerce_id', $user->id)->first();
		if(! $vendor)
			return $this->response()->error(['Vendor Not Found'], 400);
        
        return $this->response()->success($_POST);
        $price_type = $this->request->price_type;
        $option_value = $this->request->option_value;
        $amount = $this->request->amount;
        $price = $this->request->price;

        $product = new Product;
        $product->vendor_id = $vendor_id;
        $product->category_id = $this->request->category;
        $product->name = strtolower($this->request->name);
        $product->alias = str_replace(" ", "-", strtolower($this->request->name)."_".$user->id.date('his')) ;
        $product->quantity = (int) $this->request->quantity;
        $product->minimum_deposit = (int) str_replace(".", "", $this->request->minimum_deposit);
        $product->weight = $this->request->weight;
        $product->image = $this->request->product_image_primary;
        $product->description = $this->request->description;        
        $product->save();

        if(isset($_POST['product_images'])){
            foreach ($this->request->product_images as $key => $value) {
                $ProductImage = new ProductImage;
                $ProductImage->product_id = $product->id;
                $ProductImage->image = $value;
                $ProductImage->save();
            }    
        }
        
        foreach ($price_type as $key => $value) {
            if($value != "" AND $amount[$key] != "" AND $price[$key] != "")
            {
                $ProductPrice = new ProductPrice;
                $ProductPrice->product_id = $product->id;
                $ProductPrice->type = $value;
                $ProductPrice->amount = $amount[$key];
                $ProductPrice->price = (int) str_replace(".", "", $price[$key]);
                $ProductPrice->save();    
            }            
        }

        foreach ($option_value as $key => $value) {
           foreach ($value as $keyChild => $valueChild) {
                # code...
            } 
        }

        return $this->response()->success($product, ['meta.token' => $token]);
    }
}
