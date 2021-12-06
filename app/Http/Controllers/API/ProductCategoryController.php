<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $name = $request->input('name');
        $show_product = $request->input('show_product');

        if ($id) {
            $category = ProductCategory::with(['products'])->find($id);

            if ($category) {
                return ResponseFormatter::success(
                    $category,
                    "Data kategori berhasil diambil"
                );
            } else {
                return ResponseFormatter::error(
                    null,
                    "Data kategori tidak ditemukan",
                    404
                );
            }
        }

        $categories = ProductCategory::query();

        if ($name) {
            $categories->where('name', 'like', '%' . $name . '%');
        }

        if ($show_product) {
            $categories->with('products');
        }

        return ResponseFormatter::success(
            $categories->paginate($limit),
            "Data list kategori berhasil diambil"
        );
    }
}
