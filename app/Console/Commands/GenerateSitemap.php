<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Product;
use App\Models\Category;
use App\Models\ComboProduct;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate a dynamic sitemap.xml file';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Fetch all stores
        $stores = Store::all()->keyBy('id');

        // Add Store-Specific Listing Pages & Home Page
        foreach ($stores as $store) {
            $storeSlug = $store->slug;

            // Home Page
            $sitemap->add(Url::create(url("/?store={$storeSlug}")));

            // Categories Listing Page
            $sitemap->add(Url::create(url("/categories?store={$storeSlug}")));

            // Products Listing Page
            $sitemap->add(Url::create(url("/products?store={$storeSlug}")));

            // Combo Products Listing Page
            $sitemap->add(Url::create(url("/combo-products?store={$storeSlug}")));

            // Sellers Listing Page
            $sitemap->add(Url::create(url("/sellers?store={$storeSlug}")));
        }

        // Add Dynamic URLs for Individual Products
        $products = Product::all();
        foreach ($products as $product) {
            if (isset($stores[$product->store_id])) { // Check if store exists
                $storeSlug = $stores[$product->store_id]->slug;
                $sitemap->add(Url::create(url("/products/{$product->slug}?store={$storeSlug}")));
            }
        }

        // Add Dynamic URLs for Individual Combo Products
        $comboProducts = ComboProduct::all();
        foreach ($comboProducts as $comboProduct) {
            if (isset($stores[$comboProduct->store_id])) { // Check if store exists
                $storeSlug = $stores[$comboProduct->store_id]->slug;
                $sitemap->add(Url::create(url("/combo-products/{$comboProduct->slug}?store={$storeSlug}")));
            }
        }

        // Add Dynamic URLs for Individual Categories
        $categories = Category::all();
        foreach ($categories as $category) {
            if (isset($stores[$category->store_id])) { // Check if store exists
                $storeSlug = $stores[$category->store_id]->slug;
                $sitemap->add(Url::create(url("/categories/{$category->slug}/products?store={$storeSlug}")));
            }
        }

        // Add Dynamic URLs for Seller Details Pages
        $sellerStores = DB::table('seller_store')->get();
        foreach ($sellerStores as $sellerStore) {
            if (isset($stores[$sellerStore->store_id])) { // Check if store exists
                $storeSlug = $stores[$sellerStore->store_id]->slug;
                $sitemap->add(Url::create(url("/sellers/{$sellerStore->slug}?store={$storeSlug}")));
            }
        }

        // Save the sitemap to public/sitemap.xml
        $sitemap->writeToFile(base_path('public/sitemap.xml'));
        // $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully.');
    }
}
