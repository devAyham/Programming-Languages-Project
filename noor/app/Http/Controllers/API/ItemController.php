<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use App\Item;
use App\CategoryItem;
use App\ItemImage;
use App\Interact;
use App\Comment;
use Validator;
use Image;
use Carbon\Carbon;
use App\ItemTranslation;

class ItemController extends Controller
{
    public function items(){
        $items = Item::all();
        foreach($items as $item){
            
            $sub_end_date = $item->expiration_date;
            $remaining_days = Carbon::now()->diffInDays(Carbon::parse($sub_end_date));
            $first_offer  = $remaining_days - ($remaining_days * 30/100) ;
            $secand_offer = $remaining_days - ($remaining_days * 50/100) ;
            $third_offer  = $remaining_days - ($remaining_days * 70/100) ;
            if($remaining_days ==0){
                $item->delete();
            }else{
                switch ($remaining_days) {
                    case $remaining_days < $third_offer:
                        $price = $item->price;
                        $new = $price * 70 /100;
                        $new_price = $price - $new;
                        $item->new_price = $new_price;
                        $item->save();
                        break;
                    case $remaining_days >= $secand_offer && $remaining_days < $first_offer :
                        $price = $item->price;
                        $new = $price * 50 /100;
                        $new_price = $price - $new;
                        $item->new_price = $new_price;
                        $item->save();
                        break;                
                    case $remaining_days >= $first_offer   :
                        $price = $item->price;
                        $new = $price * 30 /100;
                        $new_price = $price - $new;
                        $item->new_price = $new_price;
                        $item->save();
                        break;
                }
            }
        }
        return response()->json([
            'status'   => '1',
            'details'  =>$items
        ]);
    }
    public function addItem(Request $request){
        $validator = Validator::make($request->all(), [
            'contact_information' => ['required'],
            'expiration_date'     => ['required'],
            'quantity'            => ['required'],
            'price'               => ['required'],
            'item_title_ar'       => ['required'],
            'item_title_en'       => ['required'],
        ]);
        if($validator->fails()){
            return response()->json([
                'status'       => '0',
                'details'      => $validator->errors(), 422
            ]);
        }
        $data = [
            'contact_information' => $request->contact_information,
            'expiration_date'     => $request->expiration_date,
            'quantity'            => $request->quantity,
            'price'               => $request->price,
       
            'ar' => [
                'title'   => $request->item_title_ar,
            ],
            'en' => [
                'title'  => $request->item_title_en,
            ]
        ];
      
        $item = Item::create($data);
        $item_categories = $request->category;
        if(!$item_categories == NULL) {
            $items_Array = explode("," , $item_categories);
            foreach($items_Array as $cat) {
                CategoryItem::insert( [
                    'category_id'=>  $cat,
                    'item_id'=> $item->id
                ]);
            }
        }         
        if($request->file('image')){
            $path = 'images/items/'.$item->id.'/';
            if(!(\File::exists($path))){
                \File::makeDirectory($path);
            } 
            $files=$request->file('image');
            foreach($files as $file) {
                $input['image'] = $file->getClientOriginalName();
                $destinationPath = 'images/items/';
                $img = Image::make($file->getRealPath());
                $img->resize(800, 750, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($path.$input['image']);
                $name = $path.$input['image'];
                ItemImage::insert( [
                    'img'=>  $name,
                    'item_id'=> $item->id
                ]);
            }
        } 
        return response()->json(['message' => 'تم اضافة المكان بنجاح']);
    }
    public function updateItem(Request $request ,$id){
        $item = Item::where('id',$id)->first();
        $validator = Validator::make($request->all(), [
            'contact_information' => ['required'],
            'quantity'            => ['required'],
            'price'               => ['required'],
            'item_title_ar'       => ['required'],
            'item_title_ar'       => ['required'],

        ]);
        if($validator->fails()){
            return response()->json([
                'status'       => '0',
                'details'      => $validator->errors(), 422
            ]);
        }
        $data = [
            'contact_information' => $request->contact_information,
            'expiration_date'     => $request->expiration_date,
            'quantity'            => $request->quantity,
            'price'               => $request->price,
            'ar' => [
                'title'   => $request->item_title_ar,
            ],
            'en' => [
                'title'  => $request->item_title_en,
            ]
        ];
        $item->update($data);
        $item_categories = CategoryItem::where('item_id',$item->id)->get();
        foreach($item_categories as $item_category){
            $item_category->delete();
        }
        $item_categories = $request->category;
        if(!$item_categories == NULL) {
            $items_Array = explode("," , $item_categories);
            foreach($items_Array as $cat) {
                CategoryItem::insert( [
                    'category_id'=>  $cat,
                    'item_id'=> $item->id
                ]);
            }
        }
        $items_image = ItemImage::where('item_id',$item->id)->get();
        foreach($items_image as $item_image){
            $item_image->delete();
        }
        if($request->file('image')){
            $path = 'images/items/'.$item->id.'/';
            if(!(\File::exists($path))){
                \File::makeDirectory($path);
            } 
            $files=$request->file('image');
            foreach($files as $file) {
                $input['image'] = $file->getClientOriginalName();
                $destinationPath = 'images/items/';
                $img = Image::make($file->getRealPath());
                $img->resize(800, 750, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($path.$input['image']);
                $name = $path.$input['image'];
                ItemImage::insert( [
                    'img'=>  $name,
                    'item_id'=> $item->id
                ]);
            }
        } 
        return response()->json([
            'status'  =>'1',
            'details' => 'تم تعديل المنتج بنجاح'
        ]);
    }
    public function deleteItem($id){
        $item = Item::where('id',$id)->first();
        $item->delete();
        return response()->json([
            'status'  =>'1',
            'details' => 'تم حذف المنتج بنجاح'
        ]);
    }
    public function itemDetails($id){
        $item = Item::with('images')->with('categories')->find($id);
        return response()->json([
            'status' =>'1',
            'details'=> $item
        ]);
        
    }
    public function search($name){
        $items = Item::whereTranslationLike('title','%'.$name.'%')->get();
        return response()->json([
            'status'   => '1',
            'details'  =>$items
        ]);
    }
    public function itemId($id){
        $item = Item::where('id',$id)->first();
        $old_views = $item->views;
        $new_views = $old_views + 1;
        $item->views = $new_views;
        $item->save();
        return response()->json([
            'status' =>'1',
            'details'=>$item
        ]);
    }
    public function addRemoveInteract($user_id,$item_id){
        $interact = Interact::where('user_id',$user_id)->where('item_id',$item_id)->first();
        if($interact == null){
            $interacts = new Interact;
            $interacts->user_id = $user_id;
            $interacts->item_id = $item_id;
            $interacts->interact = 'like';
            $interacts->save();
            return response()->json([
                'status' => '1',
                'details'=>'تم اضافة تفاعلك'
            ]);
        }else{
            $interact->delete();
            return response()->json([
                'status' => '1',
                'details'=>'تم حذف تفاعلك'
            ]);
        }
    }
    public function addComment(Request $request , $user_id,$item_id){
        
        $comment = new Comment;
        $comment->user_id = $user_id;
        $comment->item_id = $item_id;
        $comment->comment = $request->comment;
        $comment->save();
        return response()->json($comment);
        return response()->json([
            'status' => '1',
            'details'=>'تم اضافة تعليقك'
        ]);
    }
    public function removeComment($comm_id){
        $comment = Comment::where('id',$comm_id)->first();
        $comment->delete();
        return response()->json([
            'status' => '1',
            'details'=>'تم حذف تعليقك'
        ]);
    }
    public function Sort(){
        $items = Item::orderBy('created_at','ASC')->get();
        return response()->json([
            'status'  => '1',
            'details' => $items,
        ]);
    }
}