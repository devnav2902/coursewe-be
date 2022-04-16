<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartType;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    private function getCart()
    {
        if (!Auth::check()) {
            return response(null);
        }

        $cart = Cart::where('user_id', Auth::user()->id)
            ->setEagerLoads([])
            ->with(['course' => function ($q) {
                $q
                    ->select('id', 'author_id', 'price_id', 'slug', 'thumbnail', 'instructional_level_id', 'title')
                    ->setEagerLoads([])
                    ->with([
                        'price',
                        'instructional_level',
                        'author' => function ($q) {
                            $q->setEagerLoads([])->select('id', 'fullname', 'slug');
                        }
                    ])
                    ->withAvg('rating', 'rating')
                    ->withCount(['rating', 'lecture']);
            }])
            ->get(["user_id", "cart_type_id", "course_id", "coupon_code"]);

        $cartType = CartType::get();

        $list = [];
        $cartType->each(function ($item) use ($cart, &$list) {
            $data = [];

            $dataCart = $cart->where('cart_type_id', $item->id);
            $dataCart->each(function ($item) use (&$data) {
                $filtered = $item->only(['coupon_code']);
                $course = $item->course->toArray();
                $merge = array_merge($course, $filtered);

                array_push($data, $merge);
            });

            $list[] = ['cartType' => $item, 'data' => $data, 'user_id' => Auth::user()->id];
        });

        return $list;
    }

    function delete($course_id)
    {
        if (!Auth::check()) {
            return response(['success' => false], 400);
        }

        Cart::where('course_id', $course_id)->where('user_id', Auth::user()->id)->delete();

        $list = $this->getCart();

        return response(['success' => true, 'shoppingCart' => $list]);
    }

    function get()
    {
        $list = $this->getCart();

        return response()->json(['shoppingCart' => $list]);
    }

    function cart(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
        ]);

        $cartType = CartType::firstWhere('type', 'cart');
        $course_id = $request->input('course_id');

        if (!$cartType || !Auth::check()) {
            return response(['success' => false], 400);
        }

        $user_id = Auth::user()->id;

        $existed = Cart::where('course_id', $course_id)->firstWhere('user_id', $user_id);
        if (!$existed) {
            $idCourseInCart = Cart::create([
                'course_id' => $course_id,
                'user_id' => $user_id,
                'cart_type_id' => $cartType->id
            ])->course_id;

            $courseInCart = Course::setEagerLoads([])
                ->select('id', 'author_id', 'price_id', 'slug', 'thumbnail', 'instructional_level_id', 'title')
                ->setEagerLoads([])
                ->with([
                    'price',
                    'instructional_level',
                    'author' => function ($q) {
                        $q->setEagerLoads([])->select('id', 'fullname', 'slug');
                    }
                ])
                ->withAvg('rating', 'rating')
                ->withCount(['rating', 'lecture'])
                ->find($idCourseInCart);

            return response(['success' => true, 'course' => $courseInCart]);
        } else {
            Cart::where('course_id', $course_id)
                ->where('user_id', $user_id)
                ->update(
                    [
                        'cart_type_id' => $cartType->id
                    ]
                );

            $list = $this->getCart();

            return response(['success' => true, 'shoppingCart' => $list]);
        }
    }

    function savedForLater(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
        ]);

        try {
            $type = CartType::firstWhere('type', 'saved_for_later');

            Cart::where('course_id', $request->input('course_id'))
                ->where('user_id', Auth::user()->id)
                ->update([
                    'cart_type_id' => $type->id
                ]);

            $list = $this->getCart();

            return response(['success' => true, 'shoppingCart' => $list]);
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => 'Lỗi']);
        }
    }
}
