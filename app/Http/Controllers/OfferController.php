<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\fav_offer;
use App\Models\saved_offer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\BaseController;
use Validator;

class OfferController extends BaseController
{
    public function addOffer(Request $request)
    {
        // if (auth()->user()->role != "Business") {
        //     return $this->sendError("Only Business can add offer");
        // }

        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'category_id' => 'required',
            'actual_price' => 'required',
            'discount_price' => 'required',
            'reward_points' => 'required',
            'short_detail' => 'required',
            'desc' => 'required',
            'image' => 'mimes:jpeg,jpg,jpe,png,svg,svgz,tiff,tif,webp',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $userID = auth()->user()->id;

        $offer = new Offer();
        $offer->title = $request->title;
        $offer->category_id = (int) $request->category_id;
        if ($request->hasFile('image')) {
            $img = $request->image;
            $path = $img->store('public/offer');
            $file_path = Storage::url($path);
            $offer->image = $file_path;
        }
        $offer->owner_id = $userID;
        $offer->actual_price = (double) $request->actual_price;
        $offer->discount_price = (double) $request->discount_price;
        $offer->reward_points = (int) $request->reward_points;
        $offer->short_detail = $request->short_detail;
        $offer->description = $request->desc;

        if ($offer->save()) {
            return $this->sendResponse(array(
                "offer" => $offer
            ), 'Offer Submitted successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function editOffer(Request $request)
    {
        // $user = auth()->user();
        // if (auth()->user()->role != "Business") {
        //     return $this->sendError("Only Business can edit offer");
        // }

        $valid = Validator::make($request->all(), [
            'offer_id' => 'required',
            'image' => 'mimes:jpeg,jpg,jpe,png,svg,svgz,tiff,tif,webp',
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $offer = Offer::find($request->offer_id);

        if ($offer) {
            if ($offer->owner_id != auth()->user()->id) {
                return $this->sendError("You're not the owner of this offer.");
            }

            $data = [];
            if ($request->hasFile('image')) {
                $img = $request->image;
                $path = $img->store('public/offer');
                $file_path = Storage::url($path);
                $data['image'] = $file_path;
            }
            $data += [
                'title' => $request->title ?? $offer->title,
                'category_id' => (int) ($request->category_id ?? $offer->category_id),
                'actual_price' => (double) ($request->actual_price ?? $offer->actual_price),
                'discount_price' => (double) ($request->discount_price ?? $offer->discount_price),
                'reward_points' => (int) ($request->reward_points ?? $offer->reward_points),
                'short_detail' => $request->short_detail ?? $offer->short_detail,
                'description' => $request->desc ?? $offer->description
            ];

            if ($offer->update($data)) {
                return $this->sendResponse(array(
                    "offer" => $offer
                ), 'Offer Updated successfully.');
            } else {
                return $this->sendError("Unable to process your request at the moment.");
            }
        } else {
            return $this->sendError("Offer doesn't not exist");
        }
    }

    public function deleteOffer(Request $request)
    {
        // $user = auth()->user();
        // if (auth()->user()->role != "Business") {
        //     return $this->sendError("Only Business can edit offer");
        // }

        $valid = Validator::make($request->all(), [
            'offer_id' => 'required'
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $offer = Offer::find($request->offer_id);

        if ($offer) {
            if ($offer->owner_id != auth()->user()->id) {
                return $this->sendError("You're not the owner of this offer.");
            }

            if ($offer->delete()) {
                return $this->sendResponse(array(
                ), 'Offer Deleted successfully.');
            } else {
                return $this->sendError("Unable to process your request at the moment.");
            }
        } else {
            return $this->sendError("Offer doesn't not exist");
        }
    }

    public function getCustomers(Request $request)
    {
        $user = auth()->user();
        $page = $request->page;
        $customers = saved_offer::
            rightjoin('offers', 'saved_offers.offer_id', '=', 'offers.id')
            ->where('saved_offers.is_claimed', '1')
            ->rightjoin('users', 'saved_offers.user_id', '=', 'users.id')
            ->where('offers.owner_id', $user->id)
            ->select('users.*', DB::raw('COUNT(offers.id) as offer_count'))
            ->groupBy('users.id')
            ->orderByDesc('offer_count')
            ->limit($page ?? 10)->get();

        if ($customers != null) {
            return $this->sendResponse(
                $customers,
                'All Customers Fetched'
            );
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function getOffers(Request $request)
    {
        if ($request->type != null) {
            switch ($request->type) {
                case 'trending':
                    $valid = Validator::make($request->all(), [
                        'category_id' => 'required'
                    ]);
                    if ($valid->fails()) {
                        return $this->sendError($valid->errors()->first());
                    }
                    $user = auth()->user();
                    $page = $request->page;
                    $offer = Offer::where('category_id', $request->category_id)->limit($page ?? 10)->get();
                    if ($offer != null) {
                        $offer->map(function ($off) use ($user) {
                            $is_availed = saved_offer::where([['offer_id', $off->id], ['user_id', $user->id]])->first();
                            $off['is_claimed'] = 0;
                            if ($is_availed) {
                                $off['is_availed'] = 1;
                                $off['is_claimed'] = $is_availed->is_claimed;
                            } else {
                                $off['is_availed'] = 0;
                            }
                            $address = User::find($off->owner_id)->address;
                            $off["address"] = $address;
                            $off["category"] = Category::find($off["category_id"]);
                            return $off;
                        });
                        return $this->sendResponse(array(
                            "offers" => $offer
                        ), 'All Trending Offers Fetched');
                    } else {
                        return $this->sendError("Unable to process your request at the moment.");
                    }
                case 'fav':
                    // $user = auth()->user();
                    // if ($user->role == 'User') {
                    $page = $request->page;
                    $offer = fav_offer::with('offer')->where('user_id', "=", $user->id)->limit($page ?? 10)->get();

                    $offer = $offer->map(function ($offer) {
                        $is_availed = saved_offer::where([['offer_id', $offer->offer_id], ['user_id', $offer->user_id]])->first();
                        unset($offer["offer_id"]);
                        unset($offer["id"]);
                        $val = array_merge($offer->toArray(), $offer->offer->toArray());
                        unset($val["offer"]);
                        $address = User::find($val["owner_id"])->address;
                        $val["address"] = $address;
                        $val["category"] = Category::find($val["category_id"]);
                        $val['is_claimed'] = 0;
                        if ($is_availed) {
                            $val['is_availed'] = 1;
                            $val['is_claimed'] = $is_availed->is_claimed;
                        } else {
                            $val['is_availed'] = 0;
                        }
                        return $val;
                    });

                    if ($offer != null) {
                        return $this->sendResponse(array(
                            "offers" => $offer
                        ), 'All Favorite Offers Fetched');
                    } else {
                        return $this->sendError("Unable to process your request at the moment.");
                    }
                // } else {
                //     return $this->sendError("Only User can fetch Favorite Offers");
                // }

                default:
                    return $this->sendError("Unable to process your request at the moment.");

            }
        } else if ($request->bus_id != null) {
            $user = auth()->user();
            // if ($user->role == "User") {
            $page = $request->page;
            $offer = Offer::where('owner_id', "=", $request->bus_id)->limit($page ?? 10)->get();
            $offer = $offer->map(function ($offer) use ($user) {
                $address = User::find($offer->owner_id)->address;
                $offer["address"] = $address;
                $offer["category"] = Category::find($offer["category_id"]);
                $is_availed = saved_offer::where([['offer_id', $offer->id], ['user_id', $user->id]])->first();
                $offer['is_claimed'] = 0;
                if ($is_availed) {
                    $offer['is_availed'] = 1;
                    $offer['is_claimed'] = $is_availed->is_claimed;
                } else {
                    $offer['is_availed'] = 0;
                }
                return $offer;

            });
            if ($offer != null) {
                return $this->sendResponse(array(
                    "offers" => $offer
                ), 'All Offers Fetched');
            } else {
                return $this->sendError("Unable to process your request at the moment.");
            }
            // } else {
            //     return $this->sendError("Only Users can fetch other businesses offers");
            // }
        } else {
            $user = auth()->user();
            if ($user->role == "Business") {
                if ($request->switch == "User") {

                    $page = $request->page;
                    $offer = saved_offer::with('offer')->where('user_id', "=", $user->id)->limit($page ?? 10)->get();

                    $flattenedResults = $offer->map(function ($offer) {
                        unset($offer["offer"]["id"]);
                        $val = array_merge($offer->toArray(), $offer->offer->toArray());
                        unset($val["offer"]);
                        $address = User::find($val["owner_id"])->address;
                        $val["address"] = $address;
                        $val["category"] = Category::find($val["category_id"]);
                        $val['is_availed'] = 1;
                        return $val;
                    });

                    if ($offer != null) {
                        return $this->sendResponse(array(
                            "offers" => $flattenedResults
                        ), 'All Saved Offers Fetched');
                    } else {
                        return $this->sendError("Unable to process your request at the moment.");
                    }

                } else {
                    if ($request->switch_user_id != null) {
                        $page = $request->page;
                        $offer = saved_offer::with('offer')->where([['user_id', "=", $request->switch_user_id], ['is_claimed', '=', '1']])->limit($page ?? 10)->get();
                        $flattenedResults = $offer->map(function ($offer) {
                            unset($offer["offer"]["id"]);
                            $val = array_merge($offer->toArray(), $offer->offer->toArray());
                            unset($val["offer"]);
                            $address = User::find($val["owner_id"])->address;
                            $val["address"] = $address;
                            $val["category"] = Category::find($val["category_id"]);
                            $val['is_availed'] = 1;
                            return $val;
                        });

                        if ($offer != null) {
                            return $this->sendResponse(array(
                                "offers" => $flattenedResults
                            ), 'All Saved Offers Fetched');
                        } else {
                            return $this->sendError("Unable to process your request at the moment.");
                        }
                    } else {
                        $page = $request->page;
                        $offer = Offer::where('owner_id', "=", $user->id)->limit($page ?? 10)->get();
                        if ($offer != null) {
                            $offer->map(function ($offer) use ($user) {
                                $offer["address"] = $user->address;
                                $offer["category"] = Category::find($offer["category_id"]);
                                return $offer;
                            });
                            return $this->sendResponse(array(
                                "offers" => $offer
                            ), 'All Offers Fetched');
                        } else {
                            return $this->sendError("Unable to process your request at txhe moment.");
                        }
                    }
                }
            } else {
                $page = $request->page;
                $offer = saved_offer::with('offer')->where('user_id', "=", $user->id)->limit($page ?? 10)->get();

                $flattenedResults = $offer->map(function ($offer) {
                    unset($offer["offer"]["id"]);
                    $val = array_merge($offer->toArray(), $offer->offer->toArray());
                    unset($val["offer"]);
                    $address = User::find($val["owner_id"])->address;
                    $val["address"] = $address;
                    $val["category"] = Category::find($val["category_id"]);
                    $val['is_availed'] = 1;
                    return $val;
                });

                if ($offer != null) {
                    return $this->sendResponse(array(
                        "offers" => $flattenedResults
                    ), 'All Saved Offers Fetched');
                } else {
                    return $this->sendError("Unable to process your request at the moment.");
                }
            }
        }
    }

    public function favOffer(Request $request)
    {
        $user = auth()->user();
        if ($user->role != 'User') {
            return $this->sendError("Only Users can favorite offers");
        }

        $valid = Validator::make($request->all(), [
            'offer_id' => 'required'
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $favOffer = fav_offer::where([['offer_id', '=', $request->offer_id], ['user_id', '=', $user->id]])->first();

        if ($favOffer) {
            $favOffer->delete();
            return $this->sendResponse(array(), 'Offer UnFavorited successfully.');
        }

        $offer = new fav_offer();
        $offer->user_id = $user->id;
        $offer->offer_id = $request->offer_id;
        if ($offer->save()) {
            return $this->sendResponse(array(
            ), 'Offer Favorited Successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function availOffer(Request $request)
    {
        $user = auth()->user();
        // if ($user->role != 'User') {
        //     return $this->sendError("Only Users can avail offers");
        // }

        $valid = Validator::make($request->all(), [
            'offer_id' => 'required'
        ]);

        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $availOffer = saved_offer::where([['offer_id', '=', $request->offer_id], ['user_id', '=', $user->id]])->first();

        if ($availOffer) {
            return $this->sendResponse(array(), 'Offer Already Availed');
        }

        $offer = new saved_offer();
        $offer->user_id = $user->id;
        $offer->offer_id = $request->offer_id;
        if ($offer->save()) {
            return $this->sendResponse(array(
            ), 'Offer Availed Successfully.');
        } else {
            return $this->sendError("Unable to process your request at the moment.");
        }
    }

    public function claimedOffer(Request $request)
    {
        $user = auth()->user();
        // if ($user->role != 'Business') {
        //     return $this->sendError("Only thorugh Business, User can claim offer");
        // }

        $valid = Validator::make($request->all(), [
            'offer_id' => 'required',
            'user_id' => 'required'
        ]);


        if ($valid->fails()) {
            return $this->sendError($valid->errors()->first());
        }

        $offer = saved_offer::where([['offer_id', $request->offer_id], ['user_id', $request->user_id]])->first();

        if ($offer) {
            if ($offer->is_claimed == 1) {
                return $this->sendResponse(array(
                ), 'Offer Already Claimed');
            }

            $offer->is_claimed = 1;
            if ($offer->save()) {
                return $this->sendResponse(array(
                ), 'Offer Claimed Successfully.');
            } else {
                return $this->sendError("Unable to process your request at the moment.");
            }
        } else {
            return $this->sendError("Offer not Availed/Saved by User");
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(StoreOfferRequest $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Offer $offer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Offer $offer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Offer $offer)
    {
        //
    }
}
