<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    /**
     * index
     * 
     * @return View
     */
    public function index() : View
    {
        $product = new Product;
        $products = $product->get_product()
                            ->latest()
                            ->paginate(10);

        return view('products.index', compact('products'));
    }

    /**
     * create
     * 
     * @return View
     */
    public function create() : View
    {
        $product = new Product;
        $supplier = new Supplier;

        $data['categories'] = $product->get_category_product()->get();
        $data['suppliers'] = $supplier->get_supplier()->get();  // corrected to `suppliers`

        return view('products.create', compact('data'));
    }

    /**
     * store
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the form data
        $request->validate([
            'image'                 => 'required|image|mimes:jpeg,jpg,png|max:2048',
            'title'                 => 'required|min:5',
            'product_category_id'   => 'required|integer',
            'supplier_id'           => 'required|integer',
            'description'           => 'required|min:10',
            'price'                 => 'required|numeric',
            'stock'                 => 'required|numeric',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image->store('public/images'); // store image in public/images

            // Create a new product
            Product::create([
                'image'                 => $image->hashName(),
                'title'                 => $request->title,
                'product_category_id'   => $request->product_category_id,
                'supplier_id'           => $request->supplier_id,
                'description'           => $request->description,
                'price'                 => $request->price,
                'stock'                 => $request->stock
            ]);

            // Redirect to index with success message
            return redirect()->route('products.index')->with(['success' => 'Data Berhasil Disimpan!']);
        }

        // Redirect back with error message if image upload failed
        return redirect()->back()->with(['error' => 'Failed to upload image.']);
    }
}
