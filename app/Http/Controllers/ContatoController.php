<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contato\IndexRequest;
use App\Http\Requests\Contato\StoreRequest;
use App\Http\Resources\Contato\IndexCollection;
use App\Http\Resources\Contato\ShowResource;
use App\Http\Services\ContatoService;
use App\Http\Services\Document\CpfValidatorService;
use App\Http\Services\Location\GeocodingService;
use App\Http\Services\Location\ViaCepService;
use App\Models\Contato;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Log;

class ContatoController extends Controller
{
    private $arrayErrorMessage = [
        'index' => 'Erro ao listar contatos ',
        'store' => 'Erro ao criar contato ',
        'show' => 'Erro ao exibir contato ',
        'update' => 'Erro ao atualizar contato ',
        'destroy' => 'Erro ao deletar contato ',
    ];

    public function __construct(
        private ContatoService $service,
        private GeocodingService $geocodingService,
        private ViaCepService $viaCepService,
        private CpfValidatorService $cpfValidatorService,
        private Contato $model,
    ) {}

    public function index(IndexRequest $request): IndexCollection | JsonResponse
    {
        $message = $this->arrayErrorMessage['index'];
        $status = JsonResponse::HTTP_BAD_REQUEST;
        
        try {            
            return new IndexCollection($this->service->filter($request));
        } catch (\Throwable $th) {
            Log::critical($message .  $th->getMessage());
            return response()->json(['message' => $message, 'error' => $th->getMessage()], $status);
        }
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $message = $this->arrayErrorMessage['store'];
        $status = JsonResponse::HTTP_BAD_REQUEST;
        
        try {
            if (!$this->validateCPF($request->cpf)) {
                throw new \Exception('CPF Inválido');
            }

            if (!$address = $this->validateAddress($request)) {
                throw new \Exception('CEP Inválido');
            }

            if (!$addressGoogleMaps = $this->formatAddressGoogleApi($address, $request->numero)) {
                throw new \Exception('Formato inválido');
            };

            if(!$coordenates = $this->getCoordenatesGoogleApi($addressGoogleMaps)) {
                throw new \Exception('Não foi possível encontrar Latitude e Longiture');
            }

            if(!$request = $this->buildContato($request, $address, $coordenates))
            {
                throw new \Exception('Erro ao montar objeto Contato');
            }

            $contato = $this->service->create($request);

            if (!$contato) {
                return response()->json(['message' => $message,], $status);
            } else {
                return response()->json(['message' => 'Contato criado com sucesso'], JsonResponse::HTTP_CREATED);
            }
            
        } catch (\Throwable $th) {
            Log::critical($message .  $th->getMessage());
            return response()->json(['message' => $message, 'error' => $th->getMessage()], $status);
        }
    }


    public function show(Contato $contato)
    {
        $status = JsonResponse::HTTP_BAD_REQUEST;
        $message = $this->arrayErrorMessage['show'];

        try {    
            $contato = $this->service->findOrFail($contato->id);
            return  new ShowResource($contato);
        } catch (\Throwable $th) {
            Log::critical($message . $th->getMessage());
            return response()->json(['message' => $message, 'error' => $th->getMessage()], $status);
        }        
    }

    public function update(Request $request, Contato $contato)
    {
        //
    }

    public function destroy(Contato $contato)
    {
        //
    }

    private function validateCPF($cpf): bool
    {
        return $this->cpfValidatorService->execute($cpf);
    }

    private function validateAddress($request)
    {
        $cep = str_replace('-', '', $request->cep);
        return $this->viaCepService->execute($cep);
    }

    private function formatAddressGoogleApi($address, $numero)
    {
        $formated_address = $numero .' '. $address['logradouro'] .' '. $address['bairro'] .' '. $address['localidade'] .' '. $address['uf'] .' '. $address['cep'];
        $formated_address = str_replace('-', '', $formated_address);
        $formated_address = str_replace(' ', '+', $formated_address);
        return $formated_address;
    }

    private function getCoordenatesGoogleApi($addressFormated)
    {
        return $this->geocodingService->execute($addressFormated);
    }

    private function buildContato($request, $address, $coordenates)
    {
        $request->query->add(['logradouro' => $address['logradouro']]);
        $request->query->add(['bairro' => $address['bairro']]);
        $request->query->add(['localidade' => $address['localidade']]);
        $request->query->add(['uf' => $address['uf']]);
        $request->query->add(['estado' => $address['estado']]);
        $request->query->add(['latitude' => $coordenates['lat']]);
        $request->query->add(['longitude' => $coordenates['lng']]);

        return $request;
    }
}
