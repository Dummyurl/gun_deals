<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use ImageOptimizer;

class TestController extends Controller
{
    public function __construct()
    {
    }

    public function index(Request $request)
    {   
        $pathToImage = public_path().DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."2.jpg";
        $pathToOptimizedImage = public_path().DIRECTORY_SEPARATOR."images".DIRECTORY_SEPARATOR."BKP111_2.jpg";
        // ImageOptimizer::optimize($pathToImage);        
        ImageOptimizer::optimize($pathToImage, $pathToOptimizedImage);
        exit("1 Test RUN 1");
    }            
}
