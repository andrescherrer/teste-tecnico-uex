<?php

namespace App\Http\Services;

use App\Http\Services\Service;
use App\Models\Contato;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContatoService extends Service
{
    public function __construct()
    {
        parent::__construct(new Contato());
    }

    public function filter(Request $request): LengthAwarePaginator
    {        
        $this->setLoggedUserToRequest($request);

        return $this->model->when(
            $request->anyFilled([
                'cpf',
                'nome',
                'user_id', 
            ]), function ($query) use ($request) {
                $this->functionModelName($request, $query);
            })
            ->orderBy('nome')
            ->paginate($request->per_page ?? 20);
    }

    public function create(Request $request): bool
    {
        $this->setLoggedUserToRequest($request);

        try {
            return $this->model->create($request->all());            
        } catch(\Throwable $th) {
            Log::critical("Erro ao salvar Contato: ". $th->getMessage());
            return false;
        }        
    }

    public function find($id): Model|bool
    {
        try {
            return $this->model->where('id', $id)->where('user_id', request()->user()->id)->first();
        } catch(\Throwable $th) {
            throw new ModelNotFoundException("Contato com ID {$id} não encontrado");
            Log::critical("Erro ao buscar Contato: ". $e->getMessage());
            return false;
        }        
    }

    public function update(Request $request, int $id): bool
    {
        $request->merge([
            'user_id' => $request->user()->id
        ]);

        try {
            
            $contato = $this->model->where('id', $id)
                             ->where('user_id', $request->user()->id)
                             ->first();

            if (!$contato) {
                return false;
            }
                    
            return $contato->update($request->all());

        } catch(\Throwable $th) {
            Log::critical("Erro ao atualizar Contato: ". $th->getMessage());
            return false;
        }        
    }
    
    public function delete(int $id): bool
    {
        try {
            $contato = $this->model->where('id', $id)
                                ->where('user_id', request()->user()->id)
                                ->first();

            if (!$contato) {
                Log::warning("Tentativa de excluir contato não encontrado ou não autorizado. ID: {$id}");
                return false;
            }

            $deleted = $contato->delete();

            if (!$deleted) {
                Log::error("Falha ao excluir contato ID: {$id} - Nenhuma linha afetada");
                return false;
            }

            return true;

        } catch (\Throwable $th) {
            Log::critical("Erro ao excluir Contato ID {$id}: " . $th->getMessage());
            return false;
        }
    }

    private function functionModelName($request, $query)
    {
        if ($request->filled('cpf')) {
            $query->where('cpf', 'LIKE', "%{$request->input('cpf')}%");
        }
        if ($request->filled('nome')) {
            $query->where('nome', 'LIKE', "%{$request->input('nome')}%");
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', 'LIKE', "%{$request->input('user_id')}%");
        }
    }

    private function setLoggedUserToRequest($request)
    {
        return $request->query->add(['user_id' => $request->user()->id]);
    }
}
