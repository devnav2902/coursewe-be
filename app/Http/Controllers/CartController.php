<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartType;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    private function getCart()
    {
        $queryGetCart = Cart::setEagerLoads([])
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
            }]);


        $cart = null;
        if (!Auth::check()) {
            $cart = $queryGetCart
                ->where('session_id', Session::get('anonymous_cart'));
        } else {
            $cart = $queryGetCart
                ->where('user_id', Auth::user()->id);
        }

        $cart = $cart->get(["user_id", "cart_type_id", "course_id", "coupon_code"]);

        $cart->transform(function ($item) {
            $item->course_coupon;

            return $item;
        });

        $cartType = CartType::get();

        $list = [];
        $cartType->each(function ($item) use ($cart, &$list) {
            $data = [];
            $sum_original_price = 0.0; // Tổng giá trong giỏ hàng
            $current_price = 0.0; // Tổng giá sau khi apply khuyến mãi trong giỏ hàng

            $dataCart = $cart->where('cart_type_id', $item->id);
            $dataCart->each(function ($item) use (&$data, &$sum_original_price, &$current_price) {
                $filtered = $item->only(['coupon_code', 'course_coupon']);
                $course = $item->course->toArray();
                $merge = array_merge($course, $filtered);

                array_push($data, $merge);

                $original_price = $merge['price']['original_price'];
                $sum_original_price += $original_price;

                $has_coupon = isset($merge['course_coupon']) ? true : false;
                $discount_price = $has_coupon
                    ? str_replace('.', '', $merge['course_coupon']['discount_price'])
                    : $original_price;

                $current_price += ($has_coupon && $discount_price == $original_price ? 0.0 : $discount_price);
            });

            $value = ['cartType' => $item, 'data' => $data];

            $value['original_price'] = number_format($sum_original_price, 0, '.', '.');
            $value['current_price'] = $current_price == 0
                ? '0'
                : number_format($current_price, 0, '.', '.');
            $value['discount'] = number_format($sum_original_price - $current_price, 0, '.', '.');

            $list[] = $value;
        });

        return $list;
    }

    private function getCartSession()
    {
        return Session::get('anonymous_cart');
    }

    function delete($course_id)
    {
        if (!Auth::check()) {
            Cart::where('course_id', $course_id)
                ->where('session_id', $this->getCartSession())
                ->delete();
        } else {
            Cart::where('course_id', $course_id)
                ->where('user_id', Auth::user()->id)
                ->delete();
        }

        $list = $this->getCart();

        return response(['success' => true, 'shoppingCart' => $list]);
    }

    function get()
    {
        $list = $this->getCart();

        if (!Auth::check())
            return response()->json(['shoppingCart' => $list]);

        return response()->json(['shoppingCart' => $list, 'user_id' => Auth::user()->id]);
    }

    function cart(Request $request)
    {
        $request->validate(['course_id' => 'required']);

        if (empty($this->getCartSession())) {
            Session::put('anonymous_cart', Session::getId());
        }

        $cartType = CartType::firstWhere('type', 'cart');
        $course_id = $request->input('course_id');

        if (Auth::check()) {
            $user_id = Auth::user()->id;

            $existed = Cart::where('course_id', $course_id)->firstWhere('user_id', $user_id);
        } else {
            $existed = Cart::where('course_id', $course_id)
                ->firstWhere('session_id', $this->getCartSession());
        }

        if (!$existed) {
            $idCourseInCart = null;

            if (Auth::check()) {
                $idCourseInCart = Cart::create([
                    'session_id' => $this->getCartSession(),
                    'course_id' => $course_id,
                    'user_id' => $user_id,
                    'cart_type_id' => $cartType->id
                ])->course_id;
            } else {
                $idCourseInCart = Cart::create([
                    'course_id' => $course_id,
                    'session_id' => $this->getCartSession(),
                    'cart_type_id' => $cartType->id
                ])->course_id;
            }

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
        }

        if (Auth::check()) {
            Cart::where('course_id', $course_id)
                ->where('user_id', $user_id)
                ->update(
                    [
                        'cart_type_id' => $cartType->id
                    ]
                );
        } else {
            Cart::where('course_id', $course_id)
                ->where('session_id', $this->getCartSession())
                ->update(
                    [
                        'cart_type_id' => $cartType->id
                    ]
                );
        }

        $list = $this->getCart();

        return response(['success' => true, 'shoppingCart' => $list]);
    }

    function savedForLater(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
        ]);

        try {
            $type = CartType::firstWhere('type', 'saved_for_later');

            if (!Auth::check()) {
                Cart::where('course_id', $request->input('course_id'))
                    ->where('session_id', $this->getCartSession())
                    ->update(['cart_type_id' => $type->id]);
            } else {
                Cart::where('course_id', $request->input('course_id'))
                    ->where('user_id', Auth::user()->id)
                    ->update(['cart_type_id' => $type->id]);
            }

            $list = $this->getCart();

            return response(['success' => true, 'shoppingCart' => $list]);
        } catch (\Throwable $th) {
            return response(['success' => false, 'message' => 'Lỗi']);
        }
    }
}
