<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;
use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct(){
        $this->middleware('api.auth', ['except' => ['index', 'show', 'getImage', 'getPostsByCategory', 'getPostsByUser']]);
    }

    public function index(){
        $posts = Post::all()->load('category');

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'posts' => $posts
        ]);
    }

    public function show($id){
        $post = Post::find($id)->load('category');

        if(is_object($post)){
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        }
        else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No se ha encontrado el post'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function store(Request $request){
        //Levantar los datos por POST
        $json = $request->input('json', null);
        
        if(!empty($json)){
            //Conseguir usuario identificado
            $user = $this->getIdentity($request);
            //Validar los datos
            $validate = \Validator::make($json, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
                'image' => 'required'
    
            ]);
            if($validate->fails()){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Solicitud inválida'
                );
            }
            else{
                //Guardar el post en la BD
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $json['category_id'];
                $post->title = $json['title'];
                $post->content = $json['content'];
                $post->image = $json['image'];
                $post->save();
    
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Modelo creado con exito',
                    'post' => $post
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
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required'
            ]);
            if($validate->fails()){
                return response()->json($validate->errors(), 400);
            }
            //Quitar los campos que no quiero actualizar
            unset($json['id']);
            unset($json['user_id']);
            unset($json['created_at']);
            unset($json['user']);
            //Conseguir usuario identificado
            $user = $this->getIdentity($request);
            
            $post = Post::where('id', $id)->where('user_id', $user->sub)->first();

            if(!empty($post) && is_object($post)){
                //Actualizar el registro
                $post->update($json);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Modelo actualizado con exito',
                    'post' => $post
                );
            }
            else{
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El usuario no tiene permisos'
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

    public function destroy($id, Request $request){
        //conseguir usuario identificado
        $user = $this->getIdentity($request);
        //Conseguir el registro
        $post = Post::where('id', $id)->where('user_id', $user->sub)->first();
        if(!empty($post)){
            //Borrarlo
            $post->delete();
            //Devolver
            $data = array(
                'code' => 200,
                'status' => 'success',
                'post' => $post
            );
        }
        else{
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'El post no existe'
            );
        }
        
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request){
        $jwtAuth = new JwtAuth();
        $token = $request->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request){
        //Levantar datos de la petición
        $image = $request->file('file0');
        //Validación de la imagen
        $validate = \Validator::make($request->all(),[
            'file0' => 'required|image|mimes:jpg,png,jpeg,gif'
        ]);
        
        if(!$image || $validate->fails()){
            $data = array(
                "status" => "error",
                "code" => 400,
                "message" => "Error al subir imagen"
            );
        }
        else{
            //Guardar imagen
            $image_name = time().$image->getClientOriginalName();
            \Storage::disk('images')->put($image_name, \File::get($image));
            //Devolver el resultado
            $data = array(
                'status' => 'success',
                'code' => 200,
                'image' => $image_name
            );
        }

        return response()->json($data, $data['code']);
    }

    public function getImage($fileName){
        $isset = \Storage::disk('images')->exists($fileName);
        if($isset){
            $file = \Storage::disk('images')->get($fileName);
            return new Response($file, 200);
        }
        else{
            $data = array(
                "status" => "error",
                "code" => 400,
                "message" => "Imagen no encontrada"
            );
            return response()->json($data, $data['code']);
        }
    }

    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }

    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();
        return response()->json([
            'status' => 'success',
            'posts' => $posts
        ], 200);
    }
}
