<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contato\{IndexRequest, StoreRequest, UpdateRequest};
use App\Http\Resources\Contato\{IndexCollection, ShowResource};
use App\Http\Services\ContatoService;
use App\Http\Services\Document\CpfValidatorService;
use App\Http\Services\Location\GeocodingService;
use App\Http\Services\Location\ViaCepService;
use App\Models\Contato;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\{JsonResponse};
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
        try {            
            return new IndexCollection($this->service->filter($request));
        } catch (\Exception $e) {
            Log::critical($this->arrayErrorMessage['index'] .  $e->getMessage());
            return response()->json(
                [
                    'message' => $this->arrayErrorMessage['index'], 
                    'error' => $e->getMessage()
                ], 
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $this->validateInputs($request);
            
            $address = $this->processAddress($request);
            $coordinates = $this->getCoordinates($address, $request->numero);
            $contatoData = $this->buildContactData($request, $address, $coordinates);
            
            $this->service->create($contatoData);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Contato criado com sucesso'
                ],
                JsonResponse::HTTP_CREATED
            );

        } catch (\Exception $e) {
            Log::critical('Falha ao criar contato: ' . $e->getMessage());
            
            return response()->json(
                [
                    'message' => $this->arrayErrorMessage['store'],
                    'error' => $e->getMessage()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $contato = $this->service->find($id);
            
            return response()->json(
                [
                    'success' => true,
                    'data' => new ShowResource($contato)
                ]
            );
    
        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Contato não encontrado',
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
            
        } catch (\Exception $e) {
            Log::critical($this->arrayErrorMessage['show'] .  $e->getMessage());            

            return response()->json(
                [
                    'success' => false,
                    'message' => $this->arrayErrorMessage['show'],
                    'error' => $e->getMessage()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }
    
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        try {
            $this->validateInputs($request);
            
            $address = $this->processAddress($request);
            $coordinates = $this->getCoordinates($address, $request->numero);
            $contatoData = $this->buildContactData($request, $address, $coordinates);
            
            $updated = $this->service->update($contatoData, $id);

            if (!$updated) {
                throw new \Exception('Contato não encontrado para atualização');
            }

            return response()->json(
                ['message' => 'Contato atualizado com sucesso'],
                JsonResponse::HTTP_OK
            );

        } catch (\Exception $e) {
            Log::critical('Falha ao atualizar contato: ' . $e->getMessage());
            
            return response()->json(
                [
                    'message' => $this->arrayErrorMessage['update'],
                    'error' => $e->getMessage()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $deleted = $this->service->delete($id);

            if (!$deleted) {
                throw new ModelNotFoundException('Contato não encontrado');
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Contato excluído com sucesso'
                ],
                JsonResponse::HTTP_OK
            );

        } catch (ModelNotFoundException $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Contato não encontrado'
                ],
                JsonResponse::HTTP_NOT_FOUND
            );
            
        } catch (\Exception $e) {
            Log::critical($this->arrayErrorMessage['destroy'] . $e->getMessage());
            
            return response()->json(
                [
                    'success' => false,
                    'message' => $this->arrayErrorMessage['destroy'],
                    'error' => $e->getMessage()
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }

    /** Private Methods */

    private function validateInputs(StoreRequest|UpdateRequest $request)
    {
        if (!$this->validateCPF($request->cpf)) {
            throw new \Exception('CPF inválido');
        }
    }

    private function validateCPF($cpf)
    {
        return $this->cpfValidatorService->execute($cpf);
    }

    private function processAddress(StoreRequest|UpdateRequest $request)
    {
        $address = $this->validateAddress($request);
        
        if (!$address) {
            throw new \Exception('CEP inválido');
        }
        
        return $address;
    }

    private function validateAddress($request)
    {
        $cep = str_replace('-', '', $request->cep);
        return $this->viaCepService->execute($cep);
    }

    private function getCoordinates(array $address, string $number)
    {
        $formattedAddress = $this->formatAddressGoogleApi($address, $number);
        
        if (!$formattedAddress) {
            throw new \Exception('Formato de endereço inválido');
        }

        $coordinates = $this->getCoordenatesGoogleApi($formattedAddress);
        
        if (!$coordinates) {
            throw new \Exception('Não foi possível obter coordenadas geográficas');
        }

        return $coordinates;
    }

    private function formatAddressGoogleApi(array $address, string $number): string
    {
        if (array_diff(['logradouro', 'bairro', 'localidade', 'uf', 'cep'], array_keys($address))) {
            throw new \InvalidArgumentException('Campos de endereço incompletos');
        }

        return str_replace(
            ['-', ' '],
            ['', '+'],
            implode(' ', [$number, ...array_values(array_intersect_key($address, array_flip(['logradouro', 'bairro', 'localidade', 'uf', 'cep'])))])
        );
    }

    private function getCoordenatesGoogleApi($addressFormated)
    {
        return $this->geocodingService->execute($addressFormated);
    }

    private function buildContactData(StoreRequest|UpdateRequest $request, array $address, array $coordinates)
    {
        $contatoData = $this->buildContato($request, $address, $coordinates);
        
        if (!$contatoData) {
            throw new \Exception('Falha ao montar dados do contato');
        }

        return $contatoData;
    }

    private function buildContato($request, $address, $coordenates)
    {
        $request->merge([
            'logradouro' => $address['logradouro'],
            'bairro' => $address['bairro'],
            'localidade' => $address['localidade'],
            'uf' => $address['uf'],
            'estado' => $address['estado'],
            'latitude' => $coordenates['lat'],
            'longitude' => $coordenates['lng'],
        ]);

        return $request;
    }
}
