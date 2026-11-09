<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::post('/register', function (Request $request) {
    $dados = $request->validate([
        'name' => 'required|string|max:150',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $dados['name'],
        'email' => $dados['email'],
        'password' => Hash::make($dados['password']),
    ]);

    return response()->json([
        'user' => $user,
        'token' => $user->createToken('planejador')->plainTextToken,
    ], 201);
})->middleware('throttle:10,1');

Route::post('/login', function (Request $request) {
    $dados = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $dados['email'])->first();

    if (!$user || !Hash::check($dados['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Credenciais inválidas.'],
        ]);
    }

    return response()->json([
        'user' => $user,
        'token' => $user->createToken('planejador')->plainTextToken,
    ]);
})->middleware('throttle:5,1');

Route::post('/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logout realizado com sucesso.',
    ]);
})->middleware('auth:sanctum');
