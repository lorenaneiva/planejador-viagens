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

// Pesquisa de cidades com Nominatim/OpenStreetMap.
Route::get('/cidades', function (Request $request) {
    $dados = $request->validate([
        'q' => 'required|string|min:3|max:100',
    ]);

    $busca = trim($dados['q']);
    $chave = 'nominatim:busca:' . sha1(mb_strtolower($busca));

    // Reutilizar resultados para evitar chamadas desnecessárias.
    $resultado = \Illuminate\Support\Facades\Cache::get($chave);

    if ($resultado !== null) {
        return response()->json($resultado);
    }

    // Limite global: no máximo uma consulta externa a cada 2 segundos.
    if (!\Illuminate\Support\Facades\Cache::add(
        'nominatim:limite-global',
        true,
        2
    )) {
        return response()->json([
            'message' => 'Aguarde alguns segundos antes de pesquisar novamente.',
        ], 429);
    }

    try {
        $resposta = \Illuminate\Support\Facades\Http::withHeaders([
            'User-Agent' => 'PlanejadorViagensAcademico/1.0',
            'Accept-Language' => 'pt-BR,pt',
        ])
            ->timeout(10)
            ->get('https://nominatim.openstreetmap.org/search', [
                'q' => $busca,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'limit' => 5,
            ]);

        if (!$resposta->successful()) {
            return response()->json([
                'message' => 'Serviço de localização indisponível.',
            ], 502);
        }

        $resultado = collect($resposta->json())
            ->map(fn ($cidade) => [
                'nome' => $cidade['display_name'],
                'latitude' => (float) $cidade['lat'],
                'longitude' => (float) $cidade['lon'],
                'osm_id' => (string) $cidade['osm_id'],
                'osm_type' => $cidade['osm_type'],
                'pais' => $cidade['address']['country'] ?? null,
                'codigo_pais' => strtoupper(
                    $cidade['address']['country_code'] ?? ''
                ),
            ])
            ->values()
            ->all();

        \Illuminate\Support\Facades\Cache::put(
            $chave,
            $resultado,
            now()->addDays(7)
        );

        return response()->json($resultado);
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        return response()->json([
            'message' => 'Não foi possível conectar ao serviço de localização.',
        ], 502);
    }
})->middleware(['auth:sanctum', 'throttle:10,1']);
