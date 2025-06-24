<?php

namespace App\Livewire\Categories;

use Livewire\Component;
use App\Http\Controllers\CategoryController;

class Listing extends Component
{
    public function render()
    {
        $store_id = session('store_id');
        $categoryController = app(CategoryController::class);
        $categories = $categoryController->getCategories(null, "", "", 'row_order', "ASC", '', "", "", "", $store_id);
        $categories = $categories->original;
        // dd($categories);
        $bread_crumb = 'Categories';
        return view('livewire.' . config('constants.theme') . '.categories.listing', [
            'categories' => $categories,
            'breadcrumb' => $bread_crumb,
        ]);
    }
}
