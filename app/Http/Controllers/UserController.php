<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User;

class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Acción de pruebas User-Controller";
    }

    public function register(Request $request){
        //Recoger datos del usuario vía post
        $json = $request->input('json', null);

        if(!empty($json)){
            //Limpiar datos
            $json = array_map('trim', $json);
            //Validar Datos
            $validate = \Validator::make($json, [
                'name' => 'required|alpha',
                'surename' => 'required|alpha',
                'email' => 'required|email|unique:users', //Verificar que el usuario no esté duplicado
                'password' => 'required'
            ]);
            if($validate->fails()){
                $data = array(
                    "status" => "error",
                    "code" => 400,
                    "message" => "No se ha creado un usuario",
                    "errors" => $validate->errors()
                );
                return response()->json($data, 400);
            }
            else{
                //Validación pasada correctamente
                //Cifrar contraseña
                $pwd = hash('sha256', $json['password']);
                //Crear el usuario
                $user = new User();
                $user->name = $json['name'];
                $user->surename = $json['surename'];
                $user->email = $json['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';
                //Guardar el usuario
                $user->save();
                //Mensaje de éxito
                $data = array(
                    "status" => "succes",
                    "code" => 200,
                    "message" => "Exito en la operacion",
                    "user" => $user
                );
            }
        }
        else{
            $data = array(
                "status" => "error",
                "code" => 400,
                "message" => "Los datos enviados no son correctos"
            );
        }
        

        return response()->json($data, $data['code']);
    }

    public function login(Request $request){

        $jwtAuth = new \JwtAuth();

        //Recibir datos por POST
        $json = $request->input('json', null);
        //Validar datos
         $validate = \Validator::make($json, [
                'email' => 'required|email',
                'password' => 'required'
            ]);
            if($validate->fails()){
                $signUp = array(
                    "status" => "error",
                    "code" => 400,
                    "message" => "Fallo en el ingreso",
                    "errors" => $validate->errors()
                );
                return response()->json($data, 400);
            }
            else{
                //Cifrar password
                $pwd = hash('sha256', $json['password']);
                //Devolver token u objeto
                $signUp = $jwtAuth->signUp($json['email'], $pwd);
                if(!empty($json['getToken'])){
                    $signUp = $jwtAuth->signUp($json['email'], $pwd, true);
                }
            }
        
        return response()->json($signUp, 200);
    }

    public function update(Request $request){
        //Comprobar si el usuario está autorizado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Obtener los datos por POST
        $json = $request->input('json', null);
        $user = $jwtAuth->checkToken($token, true);

        if($checkToken && !empty($json)){
            //Validar datos
            $validate = \Validator::make($json, [
                'name' => 'required|alpha',
                'surename' => 'required|alpha',
                'email' => 'required|email|unique:users,'.$user->sub
            ]);
            //Quitar los campos que no quiero actualizar
            unset($json['id']);
            unset($json['password']);
            unset($json['role']);
            unset($json['createt_at']);
            unset($json['remember_token']);
            //Actualizar usuario en BD
            $userUpdate = User::where('id', $user->sub)->update($json);
            //Devolver array
            $data = array(
                "status" => "success",
                "code" => 200,
                "user" => $user,
                "changes" => $userUpdate
            );
        }
        else{
            $data = array(
                "status" => "error",
                "code" => 400,
                "message" => "Usuario no autorizado"
            );
        }

        return response()->json($data, $data['code']);
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
            \Storage::disk('users')->put($image_name, \File::get($image));
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
        $isset = \Storage::disk('users')->exists($fileName);
        if($isset){
            $file = \Storage::disk('users')->get($fileName);
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
    
    public function detail($id){
        $user = User::find($id);
        if(is_object($user)){
            $data = array(
                'status' => 'success',
                'code' => 200,
                'user' => $user
            );
        }
        else{
            $data = array(
                "status" => "error",
                "code" => 400,
                "message" => "El usuario no existe"
            );
        }

        return response()->json($data, $data['code']);
    }

}
