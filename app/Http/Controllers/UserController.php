<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
Use App\Services\UserService;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    
    public function __construct(Private UserService $userService)
    {
        $this->userService = $userService;
    }

    public function login(Request $request){

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]); 

        $throttleKey = strtolower($credentials['email']).'|'.$request->ip();

        try{
            $this->userService->login($credentials, $request->boolean('remember'), $throttleKey);
        } catch(ValidationException $e){
            return back()->withErrors($e->errors())->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
        
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function logout(Request $request)
    {
       $this->userService->logout($request);

       return redirect()->route('login')->with('pesan',"Logout berhasil");
    }
}
