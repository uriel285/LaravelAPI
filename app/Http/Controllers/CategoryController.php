<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;

class CategoryController extends Controller{

    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    public function index(){
        $categories = Category::all();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    public function show($id){
        $category = Category::find($id);

        if(is_object($category)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'category' => $category
            );
        }
        else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha encontrado la categoría'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        //Levantar los datos por POST
        $json = $request->input('json', null);
        //Validar los datos
        if(!empty($json)){
            $validate = \Validator::make($json, [
                'name' => 'required'
    
            ]);
            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Solicitud inválida'
                );
            }
            else{
                //Guardar la categoría en la BD
                $category = new Category();
                $category->name = $json['name'];
                $category->save();
    
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Modelo creado con exito',
                    'category' => $category
                );
            }
        }
        else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La entrada no puede estar vacía'
            );
        }
        //Devolver el resultado
        return response()->json($data, $data['code']);
    }

    public function update($id, Request $request){
        //Levantar datos por POST
        $json = $request->input('json', null);
        //Validar datos
        if(!empty($json)){
            $validate = \Validator::make($json, [
                'name' => 'required'
            ]);
            //Quitar los campos que no quiero actualizar
            unset($json['id']);
            unset($json['created_at']);
            //Actualizar el registro
            $category = Category::where('id', $id)->update($json);

            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Modelo actualizado con exito',
                'category' => $category
            );
        }
        else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'La entrada no puede estar vacía'
            );
        }
        //Devolver el resultado
        return response()->json($data, $data['code']);
    }
}
