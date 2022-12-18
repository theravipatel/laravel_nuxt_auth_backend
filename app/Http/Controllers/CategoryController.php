<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $data = array();
        
        $category = Category::all();
        $data['category'] = $category;
        
        return response([
            'data' => $data,
        ], 200);
    }

    public function add_edit($id='')
    {
        $data = array();
        
        if ($id > 0) {
            $model_category = Category::find($id);
            $data['id']           = $model_category->id;
            $data['name']         = $model_category->name;
            $data['status']       = $model_category->status;
        } else {
            $data['id']           = '';
            $data['name']         = '';
            $data['status']       = '';
        }

        return response([
            'data' => $data,
        ], 200);
    }

    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required | unique:categories,name,'.$request->post('id'),
        ]);

        $msg = 'Data inserted successfully.';
        if ($request->post('id')>0) {
            $model_category = Category::find($request->post('id'));
            $msg = 'Data updated successfully.';
        } else {
            $model_category = new Category();
        }
        
        $model_category->name = $request->post("name");
        $model_category->slug = Str::slug($request->post("name"));
        $model_category->status = "active";
        $result = $model_category->save();

        return response([
            'message' => $msg,
        ], 200);
    }

    public function delete($id)
    {
        $result = Category::find($id)->delete();
        if ($result) {
            return response([
                'message' => "Data deleted successfully.",
            ], 200);
        } else {
            return response([
                'message' => "Something went wrong. Please try again.",
            ], 500);
        }
    }
}
