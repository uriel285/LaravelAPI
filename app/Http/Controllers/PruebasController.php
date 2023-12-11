<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;

class PruebasController extends Controller
{
    public function index(){
        $title = "Animales";
        $animales = ["perro", "vaca", "gato"];
        return view('prueba.index', array(
            "title" => $title,
            'animales' => $animales
        ));
    }

    public function testOrm(){

        /* $posts = Post::all();
        foreach($posts as $post){
            echo "<h1>".$post->title."</h1>";
            echo "<span style='color:gray;'>{$post->user->name} - {$post->category->name}</span>";
            echo "<p>".$post->content."</p>";

            echo "<hr>"."</hr>";
        } */

        $categories = Category::all();
        foreach($categories as $category){
            echo "<h1>{$category->name}</h1>";
            foreach($category->posts as $post){
                echo "<h1>".$post->title."</h1>";
                echo "<span style='color:gray;'>{$post->user->name} - {$post->category->name}</span>";
                echo "<p>".$post->content."</p>";
            }    
            echo "<hr>";
        }
        die();
    }
}