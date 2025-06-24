<?php

namespace App\Livewire\Offers;

use App\Models\Offer;
use Livewire\Component;
use App\Models\OfferSliders;
use App\Http\Controllers\Admin\CategoryController;

class OffersSection extends Component
{
    public function render()
    {
        $store_id = session('store_id');
        $offers = $this->getOffers($store_id);
        $offers_sliders = $this->get_offers_sliders($store_id);
        $bread_crumb['page_main_bread_crumb'] = labels('front_messages.offers', 'Offers');
        return view('livewire.' . config('constants.theme') . '.offers.offers-section', [
            'offers_sliders' => $offers_sliders,
            'singleOffers' => $offers,
            'bread_crumb' => $bread_crumb
        ])->title("Offers |");
    }

    public function getOffers($store_id)
    {
        $offers = fetchDetails('offers', ['store_id' => $store_id], '*');
        $language_code = get_language_code();
        foreach ($offers as $key => $offer) {
            $image =
                !empty($offer->image) &&
                file_exists(public_path(config('constants.MEDIA_PATH') . $offer->image))
                ? getImageUrl($offer->image)
                : getImageUrl('offerPlaceHolder.png', '', '', 'image', 'NO_USER_IMAGE');
            $banner_image =
                !empty($offer->banner_image) &&
                file_exists(public_path(config('constants.MEDIA_PATH') . $offer->banner_image))
                ? getImageUrl($offer->banner_image)
                : getImageUrl('offerPlaceHolder.png', '', '', 'image', 'NO_USER_IMAGE');
            $offers[$key]->image = $image;
            $offers[$key]->title = getDynamicTranslation('offers', 'title', $offers[$key]->id, $language_code);
            $offers[$key]->banner_image = $banner_image;
            if ($offer->type == "categories") {
                $link = fetchDetails('categories', ['id' => $offer->type_id], 'slug');
                if (!empty($link)) {
                    $offers[$key]->link = customUrl('categories/' . $link[0]->slug . '/products');
                }
            } elseif ($offer->type == "brand") {
                $link = fetchDetails('brands', ['id' => $offer->type_id], 'slug');
                if (!empty($link)) {
                    $offers[$key]->link = customUrl('products/?brand=' . $link[0]->slug);
                }
            }
        }
        return $offers;
    }

    public function get_offers_sliders($store_id)
    {
        $store_id = ($store_id != null) ? $store_id : '';
        $language_code = get_language_code();
        $sliders = OfferSliders::orderBy('id')->where('store_id', $store_id)->where('status', '1')->get()->toArray();
        $i = 0;
        if ($sliders) {
            foreach ($sliders as $slider) {
                $offer_ids = $slider['offer_ids'];
                $offer_ids = explode(",", $offer_ids);
                $sliders[$i]['banner_image'] = $slider['banner_image'];
                $sliders[$i]['title'] = getDynamicTranslation('offer_sliders', 'title', $sliders[$i]['id'], $language_code);

                $offer_data = [];
                if (!empty($offer_ids)) {
                    $offer_data = Offer::whereIn('id', $offer_ids)
                        ->orderByRaw('FIELD(id, ' . $slider['offer_ids'] . ')')
                        ->get()
                        ->toArray();
                }
                $sliders[$i]['offer_images']  =  $offer_data;

                for ($j = 0; $j < count($sliders[$i]['offer_images']); $j++) {
                    $sliders[$i]['offer_images'][$j]['title'] = getDynamicTranslation('offers', 'title', $sliders[$i]['offer_images'][$j]['id'], $language_code);
                    $sliders[$i]['offer_images'][$j]['link'] = (isset($sliders[$i]['offer_images'][$j]['link']) && !empty($sliders[$i]['offer_images'][$j]['link'])) ? $sliders[$i]['offer_images'][$j]['link'] : "";
                    $sliders[$i]['offer_images'][$j]['min_discount'] = (isset($sliders[$i]['offer_images'][$j]['min_discount']) && !empty($sliders[$i]['offer_images'][$j]['min_discount'])) ? $sliders[$i]['offer_images'][$j]['min_discount'] : "";
                    $sliders[$i]['offer_images'][$j]['max_discount'] = (isset($sliders[$i]['offer_images'][$j]['max_discount']) && !empty($sliders[$i]['offer_images'][$j]['max_discount'])) ? $sliders[$i]['offer_images'][$j]['max_discount'] : "";
                    $sliders[$i]['offer_images'][$j]['image'] = (isset($sliders[$i]['offer_images'][$j]['image']) && !empty($sliders[$i]['offer_images'][$j]['image'])) ? getMediaImageUrl($sliders[$i]['offer_images'][$j]['image']) : "";
                    $sliders[$i]['offer_images'][$j]['banner_image'] = (isset($sliders[$i]['offer_images'][$j]['banner_image']) && !empty($sliders[$i]['offer_images'][$j]['banner_image'])) ? getMediaImageUrl($sliders[$i]['offer_images'][$j]['banner_image']) : "";

                    if (strtolower($sliders[$i]['offer_images'][$j]['type']) == 'categories') {
                        $id = (!empty($sliders[$i]['offer_images'][$j]['type_id']) && isset($sliders[$i]['offer_images'][$j]['type_id'])) ? $sliders[$i]['offer_images'][$j]['type_id'] : '';
                        // dd($id);
                        $categoryController = app(CategoryController::class);
                        $cat_res = $categoryController->getCategories($id, '1', '0', 'row_order', 'DESC', 'true');
                        $cat_res = $cat_res->original;
                        $sliders[$i]['offer_images'][$j]['data'][0]['id']  =  $cat_res['categories'][0]->id;
                        $sliders[$i]['offer_images'][$j]['data'][0]['slug']  =  $cat_res['categories'][0]->slug;
                        $sliders[$i]['offer_images'][$j]['data'][0]['name']  = ($cat_res['categories'][0]->name);
                        $sliders[$i]['offer_images'][$j]['data'][0]['image']  =  ($cat_res['categories'][0]->image);
                        $sliders[$i]['offer_images'][$j]['data'][0]['banner']  =  ($cat_res['categories'][0]->banner);
                        $sliders[$i]['offer_images'][$j]['data'][0]['children']  =  (isset($cat_res['categories'][0]->children) && !empty($cat_res['categories'][0]->children)) ? $cat_res['categories'][0]->children : [];
                    } else if (strtolower($sliders[$i]['offer_images'][$j]['type']) == 'products') {
                        $id = (!empty($sliders[$i]['offer_images'][$j]['type_id']) && isset($sliders[$i]['offer_images'][$j]['type_id'])) ? $sliders[$i]['offer_images'][$j]['type_id'] : '';
                        $pro_res = fetchProduct(NULL, NULL, $id);
                        $sliders[$i]['offer_images'][$j]['data'][0]['id']  =  $pro_res['product'][0]->id;
                        $sliders[$i]['offer_images'][$j]['data'][0]['slug']  =  $pro_res['product'][0]->slug;
                        $sliders[$i]['offer_images'][$j]['data'][0]['image']  = getMediaImageUrl($pro_res['product'][0]->image);
                    } else if (strtolower($sliders[$i]['offer_images'][$j]['type']) == 'combo_products') {
                        $id = (!empty($sliders[$i]['offer_images'][$j]['type_id']) && isset($sliders[$i]['offer_images'][$j]['type_id'])) ? $sliders[$i]['offer_images'][$j]['type_id'] : '';
                        $pro_res = fetchComboProduct(NULL, NULL, $id);
                        $sliders[$i]['offer_images'][$j]['data'][0]['id']  =  $pro_res['combo_product'][0]->id;
                        $sliders[$i]['offer_images'][$j]['data'][0]['image']  = getMediaImageUrl($pro_res['combo_product'][0]->image);
                    } else if (strtolower($sliders[$i]['offer_images'][$j]['type']) == 'brand') {
                        $id = (!empty($sliders[$i]['offer_images'][$j]['type_id']) && isset($sliders[$i]['offer_images'][$j]['type_id'])) ? $sliders[$i]['offer_images'][$j]['type_id'] : '';
                        $brand_res = fetchDetails('brands', ["id" => $id], '*');
                        $sliders[$i]['offer_images'][$j]['data'][0]['id']  =  $brand_res[0]->id;
                        $sliders[$i]['offer_images'][$j]['data'][0]['name']  =  $brand_res[0]->name;
                        $sliders[$i]['offer_images'][$j]['data'][0]['slug']  =  $brand_res[0]->slug;
                    }
                }
                $i++;
            }
            return response()->json([
                'error' => false,
                'message' => 'Sliders retrived successfully',
                'language_message_key' => 'sliders_retrived_successfully',
                'slider_images' => $sliders,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No sliders were found',
                'language_message_key' => 'no_sliders_found'
            ]);
        }
    }
}
