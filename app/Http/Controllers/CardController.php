<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Input;


class CardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    private function getCard($id) {
        $user = auth()->user();
        return Card::where('id', $id)
            ->where('status', 1)
            ->where("user_id", $user->id)
            ->first();
    }
    public function index(){
        $filters = request()->input('search');
        $user = auth()->user();

        $cards = Card::where("status" , 1, )->where("user_id", $user->id )
        ->filter($filters)
        ->latest()
        ->get();

        if(count($cards) > 0){
            return response()->json([
                'status' => 200,
                'cards' => $cards
            ],200);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Card not found or Deactivated'
            ],404);
        }

    }

    public function store(Request $request){
        if (auth()->check()) {
            $user = auth()->user();
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
                'heading'=> 'required|max:191',
                'rarity' => 'required',
                'price' => 'required|max:191',
                'amount'=> 'required|max:191' ]);



                if( $validator->fails() ){
                    return response()->json([
                        'status'=> 422,
                        'errors' => $validator->errors()
                    ],422);}
                    else{
                        $validatedData = $validator->validated();
                        if ($request->hasFile('image')) {
                            $imagePath = $request->file('image')->store('cards', 'public');
                            $validatedData['image'] = $imagePath;
                        } else {
                            $imagePath = null;
                        }

                    }
                    $card = Card::create([
                        'image' => $imagePath,
                        'heading' => $request->input('heading'),
                        'rarity' => $request->input('rarity'),
                        'price' => $request->input('price'),
                        'amount' => $request->input('amount'),
                        'user_id' => $user->id,
                    ]);


                    if($card){
                        return response ()->json([
                            'status' => 200,
                            'message'=> 'Cards Created Successfully',
                            'card' => $card,
                        ],200);
                    }else{
                        return response()->json([
                            'status' => 500,
                            'message' => 'Something Went Wrong',
                        ]) ;
                    }
        } else {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }

    }

    public function show($id){
        $card = $this->getCard($id);

        if( $card){
            return response()->json([
                'status'=> 200,
                'message'=> $card, ],200) ;
    }else{
        return response()->json([
            'status'=> 404,
            'message'=> 'Card not found or Deactivated or Unauthorize'],404) ;
    }

}

public function edit($id){
    $card = $this->getCard($id);
    if( $card ){
        return response()->json([
            'status'=> 200,
            'message'=> $card,
            ],200) ;
        }else{
            return response()->json([
                'status'=> 404,
                'message'=> 'Card not found or Deactivated or Unauthorize'],404) ;
            }
}
public function update(Request $request, $id)
{
    $card = $this->getCard($id);

    if (!$card) {
        return response()->json([
            'status' => 404,
            'message' => 'Card not found or Deactivated or Unauthorize'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        'heading' => 'required|max:191',
        'rarity' => 'required',
        'price' => 'required|max:191',
        'amount' => 'required|max:191'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'errors' => $validator->errors()
        ], 422);
    } else {
        if ($request->hasFile('image')) {
            $validatedData = $validator->validated();
            $imagePath = '/storage/' . $request->file('image')->store('/cards');
            $validatedData['photo'] = $imagePath;
            File::delete(public_path($card->image));
        } else {
            $imagePath = null;
        }

        $card->update($validatedData);

        return response()->json(['status' => 200, 'message' => 'Card updated successfully'], 200);
    }
}




    public function destroy($id){

        $card = $this->getCard($id);


        if($card){
            $card->delete();
            return response()->json([
                'status'=> 200,
                'message' => 'Card has been successfully deleted' ]);
        }else{
        return response()->json([
            'status'=> 404,
            'message' => 'Card not Found or Deactivated or Unauthorize' ]);
    }

    }

    public function addCardtoCart(Request $request, $id)
    {
        $card = $this->getCard($id);

         if(!$card){
         return response()->json([
         'status'=> 404,
         'message'=> 'Card not found or Deactivated or Unauthorize']) ;

         }

        $user = auth()->user();


        $cartItems = Cart::where('user_id', $user->id)->get();

        $cartItem = Cart::where('user_id', $user->id)->where('card_id', $id)->first();

            foreach ($cartItems as $existingCartItem) {
                if ($existingCartItem->card_id == $card->id) {
                    return response()->json([
                        'status' => 500,
                        'message' => 'Card is already added to the cart'
                    ]);
                }
            }
            for( $i = 0; $i < count($cartItems); $i++ ){
                $existingCartItem->increment('quantity');
                $existingCartItem->save();

            }

            $imagePath = '/storage/' . $request->file('image')->store('/cards');

            $cartItem = new Cart([
                'user_id' => $user->id,
                'card_id' => $card->id,
                'quantity' => 1,
                'image' => $imagePath,
                'heading' => $request->input('heading'),
                'rarity' => $request->input('rarity'),
                'price' => $request->input('price'),
                'amount' => $request->input('amount'),
            ]);


        $cartItem->save();

        return response([
            'status' => 200,
            'message' => 'Card has been added to the cart',
        ]);

    }


    public function cardCart()
    {
        $user = auth()->user();

        $cartItems = Cart::where('user_id', $user->id)->get();

        if ($cartItems->isNotEmpty()) {
            return response([
                'status' => 200,
                'cartItems' => $cartItems,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'There are no cards in the shopping cart or Unauthorize',
            ]);
        }
    }

    public function removeCardsFromCart(Request $request){
        if(auth()->check()){
            $user = auth()->user();
            $cards = Cart::where('user_id', $user->id)->get();
            if($cards->isEmpty()){
                return response()->json([
                    'status'=> 404,
                    'message'=> 'Cards are already removed or Unauthorize', ]);
            }else{
                foreach ($cards as $card) {
                    $card->delete();
                }


            return response([
                'status'=> 200,
                'message'=> 'Cards have been removed', ]);

            }
        }else{
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized',
            ], 401);
        }
    }


    public function deactivateCard($id)
    {
        $card = Card::find($id);
        $user = auth()->user();

        if ($card && $card->user_id == $user->id && $card->status == 1) {
            $card->status = 0;
            $card->save();

            return response()->json([
                'status' => 200,
                'message' => 'Card deactivated successfully',
                'card' => $card,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Card not found or unauthorized or Deactivated',
            ], 404);
        }
    }

    public function activateCard($id)
    {
        $card = Card::find($id);
        $user = auth()->user();

        if ($card && $card->user_id == $user->id && $card->status == 0) {
            $card->status = 1;
            $card->save();

            return response()->json([
                'status' => 200,
                'message' => 'Card activated successfully',
                'card' => $card,
            ], 200);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Card not found or unauthorized or Activated',
            ], 404);
        }
    }

    public function showCards()
{
    $user = auth()->user();
    $cards = Cart::where('user_id', $user->id)->get();

    $cardDetails = [];

    foreach ($cards as $card) {
        $cardDetails[] = [
            'id' => $card->id,
            'image' => $card->image,
            'heading' => $card->heading,
            'price' => $card->price,
            'amount' => $card->amount,
        ];
    }

    return response()->json([
        'status' => 200,
        'cards' => $cardDetails,
    ], 200);
}
public function updateAmount(Request $request, $id){
    $user = auth()->user();
    $card = Cart::where('card_id', $id)->where('user_id', $user->id)->first();

    if (!$card) {
        return response()->json([
            'status' => 404,
            'message' => 'Card not found or Deactivated or Unauthorize'
        ], 404);
    }

    $validator = Validator::make($request->all(), [
        'amount' => 'required|min:1,max:15'
    ]);

    if ($validator->fails() || $request->input('amount') > 15) {
        return response()->json([
            'status' => 422,
            'message'=> 'Card amount exceeds the limit 15',
            'errors' => $validator->errors()
        ], 422);
    } else {
        $validatedData = $validator->validated();


        $card->update($validatedData);

        return response()->json(['status' => 200, 'message' => 'Card Amount updated successfully'], 200);
        }
}
public function calculatePrice(){
    $user = auth()->user();
    $cards = Cart::where('user_id', $user->id)->get();

    if(!$cards){
        return response()->json([
            'status'=> 401,
            'message' => "Unauthorize" ]);
    }

    $cardDetails = [];
    $overallTotalPrice = 0;


    foreach ($cards as $card) {

        $cardTotalPrice = $card->price * $card->amount;
        $overallTotalPrice += $cardTotalPrice;

        $cardDetails[] = [
            'id' => $card->id,
            'image' => $card->image,
            'heading' => $card->heading,
            'price' => $card->price,
            'amount' => $card->amount,
            'total_price' => '$' . $cardTotalPrice,
        ];

    }
    return response()->json([
        'status' => 200,
        'cards' => $cardDetails,
        'overall_total_price' => '$' . $overallTotalPrice,
    ], 200);

}

public function purchase(){
    $user = auth()->user();
    $cards = Cart::where('user_id', $user->id)->get();
    if(auth()->check() && !$cards){
        return response()->json([
            'status'=> 200,
            'message'=> 'Payment Successful',

            ], 200);
}else{
    return response()->json([
        'status'=> 401,
        'message'=> 'Unauthorized' ],401);
}

}

}
