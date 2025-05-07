<?php

namespace Tests\Unit\Controllers;

use App\Http\Controllers\ContatoController;
use App\Http\Requests\Contato\{IndexRequest, StoreRequest, UpdateRequest};
use App\Http\Resources\Contato\IndexCollection;
use App\Http\Services\ContatoService;
use App\Http\Services\Document\CpfValidatorService;
use App\Http\Services\Location\GeocodingService;
use App\Http\Services\Location\ViaCepService;
use App\Models\Contato;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ContatoControllerTest extends TestCase
{
    private $contatoServiceMock;
    private $geocodingServiceMock;
    private $viaCepServiceMock;
    private $cpfValidatorServiceMock;
    private $modelMock;
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contatoServiceMock = Mockery::mock(ContatoService::class);
        $this->geocodingServiceMock = Mockery::mock(GeocodingService::class);
        $this->viaCepServiceMock = Mockery::mock(ViaCepService::class);
        $this->cpfValidatorServiceMock = Mockery::mock(CpfValidatorService::class);
        $this->modelMock = Mockery::mock(Contato::class);

        $this->controller = new ContatoController(
            $this->contatoServiceMock,
            $this->geocodingServiceMock,
            $this->viaCepServiceMock,
            $this->cpfValidatorServiceMock,
            $this->modelMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testIndexSuccess()
    {
        $requestMock = Mockery::mock(IndexRequest::class);
        $paginatedData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        
        $this->contatoServiceMock
            ->shouldReceive('filter')
            ->with($requestMock)
            ->andReturn($paginatedData);
            
        $response = $this->controller->index($requestMock);
        
        $this->assertInstanceOf(IndexCollection::class, $response);
    }

    public function testIndexFailure()
    {
        $requestMock = Mockery::mock(IndexRequest::class);
        $exception = new \Exception('Test error');
        
        $this->contatoServiceMock
            ->shouldReceive('filter')
            ->with($requestMock)
            ->andThrow($exception);
            
        Log::shouldReceive('critical')
            ->once()
            ->with('Erro ao listar contatos Test error');
            
        $response = $this->controller->index($requestMock);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'message' => 'Erro ao listar contatos ',
            'error' => 'Test error'
        ], json_decode($response->getContent(), true));
    }

    public function testStoreSuccess()
    {
        $requestMock = Mockery::mock(StoreRequest::class);
        $requestMock->shouldReceive('all')->andReturn([
            'cpf' => '12345678909',
            'cep' => '12345678',
            'numero' => '123'
        ]);
        $requestMock->shouldReceive('merge')->andReturnSelf();
        
        $this->cpfValidatorServiceMock
            ->shouldReceive('execute')
            ->with('12345678909')
            ->andReturn(true);
            
        $this->viaCepServiceMock
            ->shouldReceive('execute')
            ->with('12345678')
            ->andReturn([
                'logradouro' => 'Rua Teste',
                'bairro' => 'Bairro Teste',
                'localidade' => 'Cidade Teste',
                'uf' => 'TS',
                'cep' => '12345678',
                'estado' => 'Test State'
            ]);
            
        $this->geocodingServiceMock
            ->shouldReceive('execute')
            ->andReturn(['lat' => 123.456, 'lng' => 789.012]);
            
        $this->contatoServiceMock
            ->shouldReceive('create')
            ->once()
            ->andReturn(true);
            
        $response = $this->controller->store($requestMock);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Contato criado com sucesso'
        ], json_decode($response->getContent(), true));
    }

    public function testStoreCpfValidationFailure()
    {
        $requestMock = Mockery::mock(StoreRequest::class);
        $requestMock->shouldReceive('all')->andReturn([
            'cpf' => 'invalid',
            'cep' => '12345678',
            'numero' => '123'
        ]);
        
        $this->cpfValidatorServiceMock
            ->shouldReceive('execute')
            ->with('invalid')
            ->andReturn(false);
            
        Log::shouldReceive('critical')
            ->once();
            
        $response = $this->controller->store($requestMock);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('CPF inválido', json_decode($response->getContent(), true)['error']);
    }

    public function testShowSuccess()
    {
        $contatoMock = Mockery::mock(Contato::class);
        $contatoMock->shouldReceive('toArray')->andReturn(['id' => 1, 'nome' => 'Teste']);
        
        $this->contatoServiceMock
            ->shouldReceive('find')
            ->with(1)
            ->andReturn($contatoMock);
            
        $response = $this->controller->show(1);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testShowNotFound()
    {
        $this->contatoServiceMock
            ->shouldReceive('find')
            ->with(999)
            ->andThrow(new ModelNotFoundException());
            
        $response = $this->controller->show(999);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Contato não encontrado'
        ], json_decode($response->getContent(), true));
    }
    
    public function testDestroySuccess()
    {
        $this->contatoServiceMock
            ->shouldReceive('delete')
            ->with(1)
            ->andReturn(true);
            
        $response = $this->controller->destroy(1);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([
            'success' => true,
            'message' => 'Contato excluído com sucesso'
        ], json_decode($response->getContent(), true));
    }

    public function testDestroyNotFound()
    {
        $this->contatoServiceMock
            ->shouldReceive('delete')
            ->with(999)
            ->andReturn(false);
            
        $response = $this->controller->destroy(999);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Contato não encontrado'
        ], json_decode($response->getContent(), true));
    }

    public function testDestroyFailure()
    {
        $this->contatoServiceMock
            ->shouldReceive('delete')
            ->with(1)
            ->andThrow(new \Exception('Test error'));
            
        Log::shouldReceive('critical')
            ->once();
            
        $response = $this->controller->destroy(1);
        
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals([
            'success' => false,
            'message' => 'Erro ao deletar contato ',
            'error' => 'Test error'
        ], json_decode($response->getContent(), true));
    }
}