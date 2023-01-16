<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use PhpParser\Node\Stmt\ElseIf_;
use RealRashid\SweetAlert\Facades\Alert;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order_product;

class mainController extends Controller
{
    public function Home()
    {
        return view("index");
    }

    public function userlogin(Request $request)
    {
        $user = User::where("email", $request->email)->where("password", $request->password);
        // dd($user);
        if ($user->count() == 0) {
            Alert::error('UNAUTHORIZED ACCOUNT', '')->showConfirmButton('Confirm', '#AA0F0A');
            return back();
        }
        // elseif ($user->count() > 1){
        //     return redirect()->route("foodconhome");
        // }
        else {
            return redirect()->route("home");

        }
    }

    public function kitchenexpress()
    {
        $product = Product::where("store_name", "Kitchen Express")->get();
        return view("menu1", ["product" => $product]);

    }

    public function addtocart($product_id, $userid)
    {
        $cart_count = Cart::where("user_id", $userid)->where("cart_status", "pending")->count();
        if ($cart_count == 0) {
            Cart::create([
                "user_id" => $userid,
                "cart_status" => "pending"
            ]);
        }
        $cart_id = Cart::where("user_id", $userid)->where("cart_status", "pending")->get()->last()->id;

        $product_count = Order_product::where("cart_id", $cart_id)->where("product_id", $product_id)->count();
        $product_price = Product::where("id", $product_id)->get()->first()->price;
        if ($product_count == 0) {
            Order_product::create([
                "cart_id" => $cart_id,
                "product_id" => $product_id,
                "product_quantity" => 1,
                "product_total" => $product_price,

            ]);
        } else {
            $current_quantity = Order_product::where("cart_id", $cart_id)->where("product_id", $product_id)->get()->first()->product_quantity;
            Order_product::where("cart_id", $cart_id)->where("product_id", $product_id)->update([
                "product_quantity" => $current_quantity + 1,
                "product_total" => $product_price * ($current_quantity + 1)
            ]);
        }
        toast('Item added to cart', 'success');
        $product = Product::where("store_name", "Kitchen Express")->get();
        return redirect()->route("kitchenexpress", ["product" => $product]);
    }

    public function proceedtocart()
    {
        if (Cart::where("user_id", 1)->where("cart_status", "pending")->count() == 0) {
            Alert::warning('No item was added to cart', '')->showConfirmButton('Confirm', '#FCAE28');
            $product = Product::where("store_name", "Kitchen Express")->get();
            return redirect()->route("kitchenexpress", ["product" => $product]);
        }

        $cart_id = Cart::where("user_id", 1)->where("cart_status", "pending")->get()->last()->id;
        $product = Order_product::join("product", "product.id", "=", "order_product.product_id")->where("cart_id", $cart_id)->get();
        return view("cart", ["product" => $product]);

    }

    public function addquantity($productid, $cartid)
    {
        $cart_id = Order_product::where("product_id", $productid)->where("cart_id", $cartid)->get()->first();
        $updatedquantity = $cart_id->product_quantity + 1;
        $price = Product::where('id', $productid)->get()->first()->price;
        $cart_id->update([
            "product_quantity" => $updatedquantity,
            "product_total" => $updatedquantity * $price
        ]);
        $cart_id = Cart::where("user_id", 1)->where("cart_status", "pending")->get()->last()->id;
        $product = Order_product::join("product", "product.id", "=", "order_product.product_id")->where("cart_id", $cart_id)->get();
        return redirect()->route("proceedtocart", ["product" => $product]);
    }

    public function subtractquantity($productid, $cartid)
    {
        $cart_id = Order_product::where("product_id", $productid)->where("cart_id", $cartid)->get()->first();
        if ($cart_id->product_quantity == 1) {
            $cart_id->delete();
            if (Order_product::where("cart_id", $cartid)->get()->count() == 0) {
                Cart::where("id", $cartid)->delete();
                $product = Product::where("store_name", "Kitchen Express")->get();
                return redirect()->route("kitchenexpress", ["product" => $product]);
            }
        } else {
            $updatedquantity = $cart_id->product_quantity - 1;
            $price = Product::where('id', $productid)->get()->first()->price;
            $cart_id->update([
                "product_quantity" => $updatedquantity,
                "product_total" => $updatedquantity * $price
            ]);
            $cart_id = Cart::where("user_id", 1)->where("cart_status", "pending")->get()->last()->id;
        }

        $product = Order_product::join("product", "product.id", "=", "order_product.product_id")->where("cart_id", $cart_id)->get();
        return redirect()->route("proceedtocart", ["product" => $product]);
    }

    public function payment($cartid)
    {
        $cartitems = Order_product::join("product", "product.id", "=", "order_product.product_id")->where("cart_id", $cartid)->get();
        $pendingorders = Cart::where("id", $cartid)->get();
        $pendingorders->first()->update(["cart_status" => "paid"]);
        $user = User::where("id", Cart::where("id", $cartid)->first()->user_id)->get()->first();
        return view("profile",["product" => $cartitems,"user"=>$user, "pending"=>$pendingorders]);
    }

    public function order_summary($cartid)
    {
        $cart = Cart::where("id", $cartid)->get()->last();
        $itemList = Order_product::join("product", "product.id", "=", "order_product.product_id")->where("cart_id", $cartid)->get();
      
        return view("ordersummary",["cart"=>$cart,"item"=>$itemList]);
    }

    public function profile()
    {
        $cartitems = Order_product::join("product", "product.id", "=", "order_product.product_id")->join("cart", "cart.id", "=", "order_product.cart_id")->where("cart_status", "paid")->get();
        $pendingorders = Cart::where("cart_status", "paid")->get();
        $user = User::where("id",1)->get()->first();
        // dd($cartitems[0] ->cart_id);
        return view("profile",["product" => $cartitems,"user"=>$user, "pending"=>$pendingorders]);
    }

}