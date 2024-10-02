<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

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
     * show
     * 
     * @param mixed $id
     * @return View
     */
    public function show (string $id): View
    {
        //get product ID
        $product_model = new Product;
        $product = $product_model->get_product()->where("products.id", $id)->firstOrFail();

        //render view with product
        return view('products.show', compact('product'));
    }
    
    /**
     * edit
     * 
     * @param mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        //get product by ID
        $product_model = new Product;
        $data['product'] = $product_model->get_product()->where("products.id", $id)->firstOrFail();

        $supplier_model = new Supplier;

        $data['categories'] = $product_model->get_category_product()->get();
        $data['suppliers_'] = $supplier_model->get_supplier()->get();

        //render view with product
        return view('products.edit', compact('data'));
    }

    /**
     * update
     * 
     * @param mixed $request
     * @param mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //validate form
        $request->validate([
            'image'         =>  'image|mimes:jpeg,jpg,png|max:2048',
            'title'         =>  'required|min:5',
            'description'   =>  'required|min:10',
            'price'         =>  'required|numeric',
            'stock'         =>  'required|numeric'
        ]);

        //get product by ID
        $product_model = new Product;
        $product = $product_model->get_product()->where('products.id', $id)->firstOrFail();

        //check if image is uploaded
        if ($request->hasFile('image')) {

            //upload new image
            $image = $request->file('image');
            $image->storeAs('public/images/', $image->hashName());

            //delete old image
            Storage::delete('public/images/'.$product->image);

            //update product with new image
            $product->update([
                'image'                 =>  $image->hashName(),
                'title'                 =>  $request->title,
                'product_category_id'   =>  $request->product_category_id,
                'supplier_id'           =>  $request->supplier_id,
                'description'           =>  $request->description,
                'price'                 =>  $request->price,
                'stock'                 =>  $request->stock
            ]);
        } else {
            //update product without image
            $product->update([
                'title'                 =>  $request->title,
                'product_category_id'   =>  $request->product_category_id,
                'supplier_id'           =>  $request->supplier_id,
                'description'           =>  $request->description,
                'price'                 =>  $request->price,
                'stock'                 =>  $request->stock
            ]);
        }

        //redirect to index
        return redirect()->route('products.index')->with(['success' =>  'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     * 
     * @param mixed $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        // Get product by ID
        $product_model = new Product;
        $product = $product_model->get_product()->where("products.id", $id)->firstOrFail();

        // Delete image
        Storage::delete('public/images/' . $product->image);

        // Delete product
        $product->delete();

        // Redirect to index
        return redirect()->route('products.index')->with(['success' => 'Data Berhasil Dihapus']);
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
