import json
import sys
import time
import urllib.request
import urllib.error

BASE = "http://127.0.0.1:8000"
SUFIXO = str(time.time_ns())

def requisicao(caminho, dados, token=None):
    headers = {
        "Content-Type": "application/json",
        "Accept": "application/json",
    }

    if token:
        headers["Authorization"] = f"Bearer {token}"

    req = urllib.request.Request(
        BASE + caminho,
        data=json.dumps(dados).encode(),
        headers=headers,
        method="POST",
    )

    try:
        with urllib.request.urlopen(req, timeout=15) as resposta:
            return resposta.status, json.load(resposta)
    except urllib.error.HTTPError as erro:
        try:
            corpo = json.load(erro)
        except Exception:
            corpo = {"erro": erro.read().decode(errors="replace")}
        return erro.code, corpo

def graphql(query, token=None):
    return requisicao("/graphql", {"query": query}, token)

def verificar(nome, condicao, resposta=None):
    if not condicao:
        print(f"FALHOU: {nome}")
        if resposta is not None:
            print(json.dumps(resposta, indent=2, ensure_ascii=False))
        sys.exit(1)

    print(f"OK: {nome}")

def executar(nome, query, token):
    status, resposta = graphql(query, token)

    verificar(
        nome,
        status == 200 and
        "errors" not in resposta and
        resposta.get("data") is not None,
        resposta,
    )

    return resposta["data"]

print("\n=== TESTANDO API GRAPHQL ===\n")

# Criar dois usuários diferentes.
tokens = []

for numero in (1, 2):
    email = f"teste{numero}_{SUFIXO}@exemplo.com"

    status, resposta = requisicao("/api/register", {
        "name": f"Teste {numero}",
        "email": email,
        "password": "SenhaTeste123!",
        "password_confirmation": "SenhaTeste123!",
    })

    verificar(
        f"Cadastro do usuário {numero}",
        status == 201 and bool(resposta.get("token")),
        resposta,
    )

    tokens.append(resposta["token"])

token1, token2 = tokens

# Verificar autenticação.
dados = executar(
    "Consulta do usuário autenticado",
    "{ me { id name email } }",
    token1,
)

verificar("Usuário correto", dados["me"]["name"] == "Teste 1")

# Criar destino.
dados = executar(
    "Cadastro de destino",
    '''
    mutation {
        criarDestino(
            nome: "Belem"
            estado: "Para"
            pais: "Brasil"
            codigo_pais: "BR"
            latitude: -1.4558
            longitude: -48.4902
            moeda: "BRL"
        ) {
            id
            nome
        }
    }
    ''',
    token1,
)

destino_id = dados["criarDestino"]["id"]

# Criar viagem.
dados = executar(
    "Cadastro de viagem",
    '''
    mutation {
        criarViagem(
            titulo: "Viagem de teste"
            data_inicio: "2026-12-10"
            data_fim: "2026-12-15"
            status: "planejada"
        ) {
            id
            titulo
        }
    }
    ''',
    token1,
)

viagem_id = dados["criarViagem"]["id"]

# Consultar viagens.
dados = executar(
    "Listagem de viagens",
    "{ minhasViagens { id titulo } }",
    token1,
)

verificar(
    "Viagem aparece na listagem",
    any(str(v["id"]) == str(viagem_id)
        for v in dados["minhasViagens"]),
)

# Verificar isolamento entre usuários.
dados = executar(
    "Consulta de viagens do segundo usuário",
    "{ minhasViagens { id } }",
    token2,
)

verificar(
    "Segundo usuário não visualiza a viagem",
    all(str(v["id"]) != str(viagem_id)
        for v in dados["minhasViagens"]),
)

dados = executar(
    "Consulta direta de viagem de outro usuário",
    f'{{ viagem(id: "{viagem_id}") {{ id }} }}',
    token2,
)

verificar(
    "Consulta direta bloqueada",
    dados["viagem"] is None,
)

# Editar viagem.
dados = executar(
    "Edição de viagem",
    f'''
    mutation {{
        atualizarViagem(
            id: "{viagem_id}"
            titulo: "Viagem atualizada"
        ) {{
            id
            titulo
        }}
    }}
    ''',
    token1,
)

verificar(
    "Título atualizado",
    dados["atualizarViagem"]["titulo"] == "Viagem atualizada",
)

# Adicionar destino à viagem.
dados = executar(
    "Adicionar destino à viagem",
    f'''
    mutation {{
        adicionarDestinoViagem(
            viagem_id: "{viagem_id}"
            destino_id: "{destino_id}"
            ordem: 1
        ) {{
            id
            ordem
        }}
    }}
    ''',
    token1,
)

parada_id = dados["adicionarDestinoViagem"]["id"]

# Consultar relacionamento.
dados = executar(
    "Consultar destinos da viagem",
    f'''
    {{
        viagem(id: "{viagem_id}") {{
            id
            destinos {{ id nome }}
        }}
    }}
    ''',
    token1,
)

verificar(
    "Destino associado corretamente",
    any(str(d["id"]) == str(destino_id)
        for d in dados["viagem"]["destinos"]),
)

# Favoritos.
dados = executar(
    "Adicionar favorito",
    f'''
    mutation {{
        adicionarFavorito(destino_id: "{destino_id}") {{
            id
            destino {{ nome }}
        }}
    }}
    ''',
    token1,
)

dados = executar(
    "Consultar favoritos",
    "{ meusFavoritos { id destino { nome } } }",
    token1,
)

verificar(
    "Favorito encontrado",
    any(f["destino"]["nome"] == "Belem"
        for f in dados["meusFavoritos"]),
)

# Excluir favorito.
dados = executar(
    "Remover favorito",
    f'''
    mutation {{
        removerFavorito(destino_id: "{destino_id}")
    }}
    ''',
    token1,
)

verificar(
    "Favorito removido",
    dados["removerFavorito"] is True,
)

# Excluir parada.
dados = executar(
    "Remover destino da viagem",
    f'''
    mutation {{
        removerDestinoViagem(id: "{parada_id}")
    }}
    ''',
    token1,
)

verificar(
    "Destino removido da viagem",
    dados["removerDestinoViagem"] is True,
)

# Excluir viagem.
dados = executar(
    "Excluir viagem",
    f'''
    mutation {{
        excluirViagem(id: "{viagem_id}")
    }}
    ''',
    token1,
)

verificar(
    "Viagem excluída",
    dados["excluirViagem"] is True,
)

# Confirmar exclusão.
dados = executar(
    "Verificar exclusão da viagem",
    f'{{ viagem(id: "{viagem_id}") {{ id }} }}',
    token1,
)

verificar(
    "Viagem não existe mais",
    dados["viagem"] is None,
)

# Revogar tokens criados pelo teste.
for numero, token in enumerate(tokens, 1):
    status, resposta = requisicao("/api/logout", {}, token)

    verificar(
        f"Logout do usuário {numero}",
        status == 200,
        resposta,
    )

print("\nTODOS OS TESTES PRINCIPAIS PASSARAM!\n")
print("Observação: usuários e destino de teste permanecem no banco.")
