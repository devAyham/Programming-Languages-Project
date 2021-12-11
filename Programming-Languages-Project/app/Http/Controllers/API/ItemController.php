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
use App\Offer;
use App\User;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{

    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function items(){
        $items = Item::all();
        $offers = Offer::all();

        foreach($items as $item ){

                    foreach( $offers as $offer)
                    {
                             // 25:25   50:50   75:75   30000 7500  24/12/2021  21/3/2021 100
                    $first_Rate = $offer->First_offer; // النسبة الاولى "اصغر نسبة" من الايام تبع عمرالمنتج مثلا لما يمضى مثلا 25 بالمية من عمر المنتج
                            $first_Discount = $offer->value_of_discount_first_offer; //قيمة الخصم بالنسبة المئوية يليي بدو يصير للمنتج

                            
                            $second_Rate = $offer->Second_offer;// النسبة الثانية  من الايام تبع عمرالمنتج مثلا لما يمضى مثلا 50 بالمية من عمر المنتج
                            $second_Discount = $offer->value_of_discount_Second_offer;//قيمة الخصم بالنسبة المئوية يليي بدو يصير للمنتج

                            
                            $third_Rate = $offer->Third_offer;// النسبة التالتة  من الايام تبع عمرالمنتج مثلا لما يمضى مثلا 75 بالمية من عمر المنتج
                            $Third_Discount = $offer->value_of_discount_Third_offer;//قيمة الخصم بالنسبة المئوية يليي بدو يصير للمنتج

                       
                            $Number_Of_Remaining_Day= $item->Number_Of_Remaining_Day;/*
     هون جبت عدد ايام المنتج الكلي  من الداتا بيز 
        مثلا هوة دخل تاريخ انتهاء الصلاحية ب 23-3-2022 وانا اليوم ب 11-12-2021 بيطرح التاريخ وبيعطيني 100 يوم وهاد ثابت ما عاديتغير قيمتو
        هاد الحكي موجود ب تابع ادد ايتم */

                            $expiration_date = $item->expiration_date;   //تاريخ انتهاء الصلاحية 

                            $remaining_days = Carbon::now()->diffInDays(Carbon::parse($expiration_date));/*
                           هون طرح تاريخ انتهاء الصلاحية من تاريخ اليوم وهاد مو ثابت وكل يوم بيتغير  
                           يعني كم يوم باقي ليخلص مدتتو للمنتج */
                     

                            $number_of_day_for_first_offer  =$Number_Of_Remaining_Day-($Number_Of_Remaining_Day * $first_Rate /100); 
                            $number_of_day_for_second_offer =$Number_Of_Remaining_Day-($Number_Of_Remaining_Day * $second_Rate/100);
                            $number_of_day_for_third_offer  =$Number_Of_Remaining_Day-($Number_Of_Remaining_Day * $third_Rate /100);
                    
                         

                            if($remaining_days ==0)
                            {
                                $item->delete();
                            }
                         
                            else
                            {
                                switch ($remaining_days) 
                                        {

                                            case $remaining_days > $number_of_day_for_first_offer :
                                                $item->new_price = $item->price; 
                                                $item->save();
                                                break;

                                            case $remaining_days <= $number_of_day_for_first_offer && $remaining_days > $number_of_day_for_second_offer :/*
                                                 ازا كان عدد الايام  الباقية اصغر من ايام اول فتر_عرض واكبر من تاني فترة_عرض*/

                                                $price = $item->price;    //اخدت سعر المنتج من داتابيز وحطيتو بل برايس

                                                $value_Discount = $price * $first_Discount /100;
                                                       //عرفت متحول فاليو_ديسكاونت(قيمة الخصم) وقلت انو بساوي  
                                                    //قيمة الخصم =السعر الاصلي ضرب (قيمة الخصم بل فترة الاولى ) على 100

                                                $new_price = $price - $value_Discount;
                                                    //السعر الجديد هوة السعر القديم ناقص قيمة الخصم

                                                $item->new_price = $new_price;
                                                $item->save();
                                                    //حفظ القيمة الجديدة بل داتا بيز 
                                                break; 


                                              case$remaining_days <= $number_of_day_for_second_offer && $remaining_days > $number_of_day_for_third_offer :

                                                $price = $item->price;    //اخدت سعر المنتج من داتابيز وحطيتو بل برايس

                                                $value_Discount = $price * $second_Discount/100;  
                                                    //عرفت متحول فاليو_ديسكاونت(قيمة الخصم) وقلت انو بساوي  
                                                    //قيمة الخصم =السعر الاصلي ضرب (قيمة الخصم بل فترة التانية ) على 100
                                            
                                                $new_price = $price - $value_Discount;
                                                    //السعر الجديد هوة السعر القديم ناقص قيمة الخصم

                                                $item->new_price = $new_price; 
                                                $item->save();
                                                break;    

                                             case $remaining_days  <= $number_of_day_for_third_offer:
                                                $price = $item->price;
                                                $value_Discount = $price *$Third_Discount /100;
                                                $new_price = $price - $value_Discount;
                                                $item->new_price = $new_price;
                                                $item->save();

                                        }

                            }

                     }
    }
        return response()->json([
            'status'   => '1',
            'details'  =>$items
        ]);
    }
/**************************************A****************************************/
    public function add_discount(Request $request)
    {/*
            $validator = Validator::make($request->all(), 
                [
                    'First_offer'                   => ['required'],
                    'value_of_discount_first_offer' => ['required'],
                    'Second_offer'                  => ['required'],
                    'value_of_discount_Second_offer'=> ['required'],
                    'Third_offer'                   => ['required'],
                    'value_of_discount_Third_offer' => ['required'],
                ]);

                if($validator->fails()){
                    return response()->json([
                        'status'       => '0',
                        'details'      => $validator->errors(), 422
                    ]);
                }
        // 
        
            $data_of_discount =
                [
                    'user_id' => 1,//هون ما عرفت جيب ال ايدي تبع الايتم  +لازم يكون اسمو ايتم_ايدي
                'First_offer'                   => $request->First_offer,
                'value_of_discount_first_offer' => $request->value_of_discount_first_offer,
                'Second_offer'                  => $request->Second_offer,
                'value_of_discount_Second_offer'=> $request->value_of_discount_Second_offer,
                'Third_offer'                   => $request->Third_offer,
                'value_of_discount_Third_offer' => $request->value_of_discount_Third_offer,
                ];

        $offer= Offer::create($data_of_discount);*/
        // return response()->json(['message' => 'تم حقل الخصومات المكان بنجاح']);
    }
/**************************************A****************************************/

    public function addItem(Request $request){
        $validator = Validator::make($request->all(), [
            'contact_information' => ['required'],
            'expiration_date'     => ['required'],
            'quantity'            => ['required'],
            'price'               => ['required'],
            'item_title_ar'       => ['required'],
            'item_title_en'       => ['required'],

            'First_offer'                   => ['required'],
            'value_of_discount_first_offer' => ['required'],
            'Second_offer'                  => ['required'],
            'value_of_discount_Second_offer'=> ['required'],
            'Third_offer'                   => ['required'],
            'value_of_discount_Third_offer' => ['required']
        ]);
    
        if($validator->fails()){
            return response()->json([
                'status'       => '0',
                'details'      => $validator->errors(), 422
            ]);
        }
      $item = new Item;
      
      $user = Auth::user();
      $id = Auth::id(); 

            //   dd( Auth::id())    ;
        $data = [
             'user_id' => 1,
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
       

        $expiration_date = $request->expiration_date;//اخدت تاريخ انتهاء الصلاحية من الداتابيز

        $remaining_days = Carbon::now()->diffInDays(Carbon::parse($expiration_date)); //طرحت تاريخ انتهاء الصلاحية من تاريخ اليوم وهوة متغير

        $item = Item::create($data);
        
        $data_of_discount =
        [
            'item_id' => 1,//هون ما عرفت جيب ال ايدي تبع الايتم 
        'First_offer'                   => $request->First_offer,
        'value_of_discount_first_offer' => $request->value_of_discount_first_offer,
        'Second_offer'                  => $request->Second_offer,
        'value_of_discount_Second_offer'=> $request->value_of_discount_Second_offer,
        'Third_offer'                   => $request->Third_offer,
        'value_of_discount_Third_offer' => $request->value_of_discount_Third_offer,
        ];
        $offer= Offer::create($data_of_discount);



        $item->Number_Of_Remaining_Day  = $remaining_days; /*  هاد الريمينينغ دايس هوة فرق بين تاريخ الانتهاء وتاريخ اليوم بس طالما انا استدعيتوعند انشاء ايتم جديد
         ف هوة رح يتطبق مرة وحدة ويتسجل بل داتا بيز وخلص ما عاد يتعدل لانو ما عاد ئلو فوتة لهاد المكان مرة تانية*/

        $item->save(); // هون  عملت سيف مشان عدد الايام الكلي يروح عل داتا بيز




/*تحت مالي عامل شي ب كمالة التالبع عند الكاتيغوري */
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