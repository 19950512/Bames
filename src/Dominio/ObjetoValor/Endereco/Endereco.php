<?php

declare(strict_types=1);

namespace App\Dominio\ObjetoValor\Endereco;

use App\Dominio\ObjetoValor\TextoSimples;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Latitude;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Longitude;
use App\Dominio\ObjetoValor\Endereco\Localizacao\Localizacao;

final class Endereco
{
    public function __construct(
        public ?TextoSimples $rua = null,
        public ?TextoSimples $numero = null,
        public ?TextoSimples $bairro = null,
        public ?TextoSimples $cidade = null,
        public ?Estado $estado = null,
        public ?Pais $pais = null,
        public ?CEP $cep = null,
        public ?TextoSimples $complemento = null,
        public ?TextoSimples $referencia = null,
        public ?Localizacao $localizacao = null,
    ){}

    public function comparar(Endereco $endereco): array
    {
        $diferencas = [];
        if(is_a($this->rua, TextoSimples::class) and is_a($endereco->rua, TextoSimples::class) and $this->rua->get() !== $endereco->rua->get()){
            $diferencas['rua'] = [
                'antigo' => $this->rua->get(),
                'novo' => $endereco->rua->get(),
            ];
        }

        if(is_a($this->numero, TextoSimples::class) and is_a($endereco->numero, TextoSimples::class) and $this->numero->get() !== $endereco->numero->get()){
            $diferencas['numero'] = [
                'antigo' => $this->numero->get(),
                'novo' => $endereco->numero->get(),
            ];
        }

        if(is_a($this->bairro, TextoSimples::class) and is_a($endereco->bairro, TextoSimples::class) and $this->bairro->get() !== $endereco->bairro->get()){
            $diferencas['bairro'] = [
                'antigo' => $this->bairro->get(),
                'novo' => $endereco->bairro->get(),
            ];
        }

        if(is_a($this->cidade, TextoSimples::class) and is_a($endereco->cidade, TextoSimples::class) and $this->cidade->get() !== $endereco->cidade->get()){
            $diferencas['cidade'] = [
                'antigo' => $this->cidade->get(),
                'novo' => $endereco->cidade->get(),
            ];
        }

        if(is_a($this->estado, Estado::class) and is_a($endereco->estado, Estado::class) and $this->estado->get() !== $endereco->estado->get()){
            $diferencas['estado'] = [
                'antigo' => $this->estado->get(),
                'novo' => $endereco->estado->get(),
            ];
        }

        if(is_a($this->pais, Pais::class) and is_a($endereco->pais, Pais::class) and $this->pais->get() !== $endereco->pais->get()){
            $diferencas['pais'] = [
                'antigo' => $this->pais->get(),
                'novo' => $endereco->pais->get(),
            ];
        }

        if(is_a($this->cep, CEP::class) and is_a($endereco->cep, CEP::class) and $this->cep->get() !== $endereco->cep->get()){
            $diferencas['cep'] = [
                'antigo' => $this->cep->get(),
                'novo' => $endereco->cep->get(),
            ];
        }

        if(is_a($this->complemento, TextoSimples::class) and is_a($endereco->complemento, TextoSimples::class) and $this->complemento->get() !== $endereco->complemento->get()){
            $diferencas['complemento'] = [
                'antigo' => $this->complemento->get(),
                'novo' => $endereco->complemento->get(),
            ];
        }

        if(is_a($this->referencia, TextoSimples::class) and is_a($endereco->referencia, TextoSimples::class) and $this->referencia->get() !== $endereco->referencia->get()){
            $diferencas['referencia'] = [
                'antigo' => $this->referencia->get(),
                'novo' => $endereco->referencia->get(),
            ];
        }

        if(is_a($this->localizacao, Localizacao::class) and is_a($endereco->localizacao, Localizacao::class)){
            $diferencas['localizacao'] = $this->localizacao->comparar($endereco->localizacao);
        }

        return $diferencas;
    }

    public function setParams(array $params): void
    {
        $numero = null;
        $rua = null;
        $cep = null;
        $complemento = null;
        $bairro = null;
        $cidade = null;
        $estado = null;
        $pais = new Pais('Brazil');
        $referencia = null;
        $localizacao = null;

        if(isset($params['numero']) and !empty($params['numero'])){
            $numero = new TextoSimples($params['numero']);
        }
        if(isset($params['rua']) and !empty($params['rua'])){
            $rua = new TextoSimples($params['rua']);
        }
        if(isset($params['cep']) and !empty($params['cep'])){
            $cep = new CEP($params['cep']);
        }
        if(isset($params['complemento']) and !empty($params['complemento'])){
            $complemento = new TextoSimples($params['complemento']);
        }
        if(isset($params['bairro']) and !empty($params['bairro'])){
            $bairro = new TextoSimples($params['bairro']);
        }
        if(isset($params['cidade']) and !empty($params['cidade'])){
            $cidade = new TextoSimples($params['cidade']);
        }
        if(isset($params['estado']) and !empty($params['estado'])){
            $estado = new Estado($params['estado']);
        }
        if(isset($params['pais']) and !empty($params['pais'])){
            $pais = new Pais($params['pais']);
        }
        if(isset($params['referencia']) and !empty($params['referencia'])){
            $referencia = new TextoSimples($params['referencia']);
        }
        if(isset($params['latitude'], $params['longitude']) and !empty($params['latitude']) and !empty($params['longitude'])){
            $localizacao = new Localizacao(
                latitude: new Latitude((float) $params['latitude']),
                longitude: new Longitude((float) $params['longitude'])
            );
        }

        $this->numero = $numero;
        $this->rua = $rua;
        $this->cep = $cep;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->pais = $pais;
        $this->referencia = $referencia;
        $this->localizacao = $localizacao;
    }

	public function enderecoCompleto(): string
	{
		$informacoes = [];
		if(is_a($this->rua, TextoSimples::class) and !empty($this->rua->get())){
			$informacoes[] = $this->rua->get();
		}
		if(is_a($this->numero, TextoSimples::class) and !empty($this->numero->get())){
			$informacoes[] = $this->numero->get();
		}
		if(is_a($this->bairro, TextoSimples::class) and !empty($this->bairro->get())){
			$informacoes[] = $this->bairro->get();
		}
		if(is_a($this->cidade, TextoSimples::class) and !empty($this->cidade->get())){
			$informacoes[] = $this->cidade->get();
		}
		if(is_a($this->estado, Estado::class) and !empty($this->estado->getFull())){
			$informacoes[] = $this->estado->getFull();
		}
		if(is_a($this->pais, Pais::class) and !empty($this->pais->getFull())){
			$informacoes[] = $this->pais->getFull();
		}
		if(is_a($this->cep, CEP::class) and !empty($this->cep->get())){
			$informacoes[] = $this->cep->get();
		}
		return implode(', ', $informacoes);
	}

    public function get(): array
    {
        return [
            'rua' => is_a($this->rua, TextoSimples::class) ? $this->rua->get() : '',
            'numero' => is_a($this->rua, TextoSimples::class) ? $this->numero->get() : '',
            'bairro' => is_a($this->numero, TextoSimples::class) ? $this->bairro->get() : '',
            'cidade' => is_a($this->bairro, TextoSimples::class) ? $this->cidade->get() : '',
            'estado' => is_a($this->estado, Estado::class) ? $this->estado->get() : '',
            'pais' => is_a($this->pais, Pais::class) ? $this->pais->get() : '',
            'cep' => is_a($this->cep, CEP::class) ? $this->cep->get() : '',
            'complemento' => is_a($this->complemento, TextoSimples::class) ? $this->complemento->get() : '',
            'referencia' => is_a($this->referencia, TextoSimples::class) ? $this->referencia->get() : '',
            'localizacao' => is_a($this->localizacao, Localizacao::class) ? $this->localizacao->get() : '',
        ];
    }
}