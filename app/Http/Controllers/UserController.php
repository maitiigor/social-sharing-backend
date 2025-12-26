<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        return response()->json(["data" => User::with('roles')->get()], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validated = $request->validated();
        $user = User::create($validated);
        $user->assignRole($request->role);

        return response()->json(["data" => $user], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
        return response()->json(["data" => $user->load('roles')], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
        $validated = $request->validated();
        $user->update($validated);

        return response()->json(["data" => $user], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
        $user->delete();

        return response()->json(null, 204);
    }

    public function search(Request $request)
    {
        //
        $query_string = $request->input('query');
    
        $users = User::select('id', 'name', 'email')->where(function($query) use ($query_string) {
            $query->where('name', 'like', "%$query_string%")->orWhere('email', 'like', "%$query_string%");
        })->where('id', "!=", auth()->user()->id)->get();

        return response()->json(["data" => $users->toArray()], 200);
    }
}
