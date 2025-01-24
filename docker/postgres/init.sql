CREATE TABLE IF NOT EXISTS businesses
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    business_name character varying,
    business_razao character varying,
    business_document character varying,
    business_email character varying,
    business_phone character varying,
    business_whatsapp character varying,
    business_address character varying,
    business_address_number character varying,
    business_address_complement character varying,
    business_address_neighborhood character varying,
    business_address_city character varying,
    business_address_state character varying,
    business_address_cep character varying,
    business_acesso_total_autorizado boolean NOT NULL DEFAULT false,
    business_pos_cadastrada_executado boolean NOT NULL DEFAULT false,
    business_creditos_saldo numeric NOT NULL DEFAULT 0,
    business_acesso_nao_autorizado_motivo character varying,
    business_acesso_nao_autorizado boolean NOT NULL DEFAULT false,
    PRIMARY KEY (codigo),
    autodata timestamp with time zone NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS business_processos_monitorado
(
    codigo serial NOT NULL,
    business_processos_monitorado_codigo character varying,
    processo_cnj character varying,
    business_id character varying,
    data_inicio_monitoramento timestamp with time zone,
    data_ultima_atualizacao timestamp with time zone,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS businesses_deletados
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    business_name character varying,
    business_razao character varying,
    business_document character varying,
    business_email character varying,
    business_phone character varying,
    business_whatsapp character varying,
    business_address character varying,
    business_address_number character varying,
    business_address_complement character varying,
    business_address_neighborhood character varying,
    business_address_city character varying,
    business_address_state character varying,
    business_address_cep character varying,
    business_acesso_total_autorizado boolean NOT NULL DEFAULT false,
    business_creditos_saldo numeric NOT NULL DEFAULT 0,
    PRIMARY KEY (codigo),
    autodata timestamp with time zone NOT NULL DEFAULT now()
);


CREATE TABLE IF NOT EXISTS accounts
(
    codigo serial NOT NULL,
    acc_id character varying NOT NULL,
    business_id character varying NOT NULL,
    acc_email character varying,
    acc_diretor_geral boolean NOT NULL DEFAULT false,
    acc_nickname character varying,
    acc_documento character varying,
    token_recuperar_senha character varying,
    acc_password character varying,
    oab character varying,
    acc_created_at timestamp with time zone NOT NULL DEFAULT now(),
    acc_email_verificado boolean NOT NULL DEFAULT false,
    acc_email_token_verificacao character varying,
    acc_acesso_bloqueado_mensagem character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS accounts_fcm_tokens
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    acc_id character varying NOT NULL,
    fcm_token character varying NOT NULL,
    momento timestamp with time zone NOT NULL DEFAULT now(),
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS accounts_deletados
(
    codigo serial NOT NULL,
    acc_id character varying NOT NULL,
    business_id character varying NOT NULL,
    acc_email character varying,
    acc_diretor_geral boolean NOT NULL DEFAULT false,
    acc_nickname character varying,
    token_recuperar_senha character varying,
    acc_password character varying,
    oab character varying,
    acc_created_at timestamp with time zone NOT NULL DEFAULT now(),
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS contas_bancarias
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    conta_bancaria_codigo character varying COLLATE pg_catalog."default",
    nome character varying COLLATE pg_catalog."default",
    ambiente character varying COLLATE pg_catalog."default",
    cedente character varying COLLATE pg_catalog."default",
    agencia character varying COLLATE pg_catalog."default",
    conta character varying COLLATE pg_catalog."default",
    posto character varying COLLATE pg_catalog."default",
    chave_api character varying COLLATE pg_catalog."default",
    client_id_api character varying COLLATE pg_catalog."default",
    cnab character varying COLLATE pg_catalog."default",
    banco character varying COLLATE pg_catalog."default",
    webhook_codigo character varying COLLATE pg_catalog."default",
    autodata timestamp with time zone NOT NULL DEFAULT now(),
    data_ultima_atualizacao timestamp with time zone,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS contas_bancarias_eventos
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    conta_bancaria_codigo character varying COLLATE pg_catalog."default",
    evento_momento character varying COLLATE pg_catalog."default",
    evento_descricao character varying COLLATE pg_catalog."default",
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS auth_jwtokens
(
    codigo serial,
    business_id character varying NOT NULL,
    token character varying COLLATE pg_catalog."default",
    acc_id character varying COLLATE pg_catalog."default",
    autodata timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT auth_jwtokens_pkey PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS cobrancas
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    cobranca_id character varying COLLATE pg_catalog."default" NOT NULL,
    cobranca_id_plataforma_api_cobranca character varying COLLATE pg_catalog."default",
    pagador_id character varying COLLATE pg_catalog."default" NOT NULL,
    pagador_nome_completo character varying COLLATE pg_catalog."default" NOT NULL,
    conta_bancaria_id character varying COLLATE pg_catalog."default" NOT NULL,
    meio_de_pagamento character varying COLLATE pg_catalog."default",
    data_vencimento DATE,
    mensagem TEXT,
    multa DECIMAL,
    juros DECIMAL,
    parcelas integer NOT NULL DEFAULT 1,
    valor_desconto_antecipacao DECIMAL,
    tipo_desconto character varying COLLATE pg_catalog."default",
    tipo_juros character varying COLLATE pg_catalog."default",
    tipo_multa character varying COLLATE pg_catalog."default",
    autodata timestamp with time zone NOT NULL DEFAULT now(),
    CONSTRAINT cobrancas_pkey PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS cobrancas_eventos
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    cobranca_id character varying COLLATE pg_catalog."default" NOT NULL,
    evento_momento character varying COLLATE pg_catalog."default" NOT NULL,
    evento_descricao character varying COLLATE pg_catalog."default" NOT NULL,
    CONSTRAINT cobrancas_eventos_pkey PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS cobrancas_composicao
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    plano_de_contas_id character varying COLLATE pg_catalog."default",
    plano_de_conta_nome character varying COLLATE pg_catalog."default",
    cobranca_id character varying COLLATE pg_catalog."default",
    descricao character varying COLLATE pg_catalog."default",
    valor character varying COLLATE pg_catalog."default",
    PRIMARY KEY (codigo),
    autodata timestamp with time zone NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS boletos
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    boleto_id character varying COLLATE pg_catalog."default" NOT NULL,
    boleto_id_plataforma_api_cobranca character varying COLLATE pg_catalog."default",
    boleto_pagador_id_plataforma_API_cobranca character varying COLLATE pg_catalog."default",
    cobranca_id_plataforma_api_cobranca character varying COLLATE pg_catalog."default",
    cobranca_id character varying COLLATE pg_catalog."default",
    cliente_id character varying COLLATE pg_catalog."default",
    conta_bancaria_id character varying COLLATE pg_catalog."default",
    boleto_foi_aceito_pela_plataforma boolean NOT NULL DEFAULT false,
    boleto_foi_liquidado_manualmente boolean NOT NULL DEFAULT false,
    boleto_quem_liquidou_manualmente character varying COLLATE pg_catalog."default",
    boleto_quando_liquidou_manualmente timestamp with time zone,
    boleto_valor DECIMAL,
    boleto_parcelas character varying COLLATE pg_catalog."default",
    boleto_resposta_completa_plataforma character varying COLLATE pg_catalog."default",
    boleto_valor_recebido DECIMAL,
    boleto_data_vencimento DATE,
    boleto_data_pagamento DATE,
    boleto_mensagem TEXT,
    boleto_multa DECIMAL,
    boleto_juros DECIMAL,
    boleto_seu_numero character varying COLLATE pg_catalog."default",
    boleto_nosso_numero character varying COLLATE pg_catalog."default",
    boleto_linha_digitavel character varying COLLATE pg_catalog."default",
    boleto_codigo_barras character varying COLLATE pg_catalog."default",
    boleto_qrcode_pix character varying COLLATE pg_catalog."default",
    boleto_url character varying COLLATE pg_catalog."default",
    boleto_status character varying COLLATE pg_catalog."default",
    CONSTRAINT boletos_pkey PRIMARY KEY (codigo)
);

CREATE TABLE plano_de_contas (
    codigo serial NOT NULL,
    plano_de_contas_codigo character varying NOT NULL,
    plano_de_contas_nome character varying NOT NULL,
    plano_de_contas_descricao character varying,
    plano_de_contas_tipo character varying NOT NULL,
    plano_de_contas_categoria character varying,
    plano_de_contas_nivel integer,
    pai_id integer,
    CONSTRAINT plano_de_contas_pkey PRIMARY KEY (codigo)
);
/* -- Inserir as contas principais (nível 1) de Receitas
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel)
SELECT * FROM (VALUES
    ('', 'Honorários por Consulta', 'Receitas provenientes de honorários cobrados pelas consultas jurídicas', 'Receita', 'Receitas', 1),
    ('', 'Honorários por Serviços Adicionais', 'Receitas de serviços jurídicos adicionais, como pareceres e consultorias', 'Receita', 'Receitas', 1),
    ('', 'Honorários de Contencioso', 'Receitas relacionadas à representação legal em processos judiciais', 'Receita', 'Receitas', 1),
    ('', 'Outras Receitas', 'Outras fontes de receitas não diretamente ligadas a serviços jurídicos', 'Receita', 'Receitas', 1)
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserir subcontas (nível 2) para Honorários por Consulta
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Consultas Presenciais', 'Valor cobrado por consultas realizadas presencialmente no escritório', 'Receita', 'Honorários por Consulta', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Consulta' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Consultas Online', 'Valor cobrado por consultas realizadas de forma remota (por telefone, videoconferência, etc.)', 'Receita', 'Honorários por Consulta', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Consulta' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Consultas Emergenciais', 'Honorários cobrados por consultas urgentes ou fora do horário comercial', 'Receita', 'Honorários por Consulta', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Consulta' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Consultas de Aconselhamento Jurídico', 'Consultas para orientação jurídica sem a abertura de processos ou litígios', 'Receita', 'Honorários por Consulta', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Consulta' AND plano_de_contas_categoria = 'Receitas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserir subcontas (nível 2) para Honorários por Serviços Adicionais
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Elaboração de Pareceres Jurídicos', 'Valor cobrado pela elaboração de pareceres ou opiniões jurídicas sobre casos específicos', 'Receita', 'Honorários por Serviços Adicionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Serviços Adicionais' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Análise de Documentos', 'Receita gerada pela análise e revisão de documentos como contratos, termos, ou acordos', 'Receita', 'Honorários por Serviços Adicionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Serviços Adicionais' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Elaboração de Contratos', 'Receita obtida com a elaboração de documentos legais, como contratos de prestação de serviços ou acordos comerciais', 'Receita', 'Honorários por Serviços Adicionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Serviços Adicionais' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Consultoria Empresarial', 'Honorários pagos por consultoria jurídica para empresas', 'Receita', 'Honorários por Serviços Adicionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários por Serviços Adicionais' AND plano_de_contas_categoria = 'Receitas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserir subcontas (nível 2) para Honorários de Contencioso
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Consultoria Pré-Processual', 'Receita de consultas realizadas antes da abertura de um processo judicial', 'Receita', 'Honorários de Contencioso', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários de Contencioso' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Honorários de Advogado de Defesa', 'Valor cobrado por representar um cliente em um processo judicial', 'Receita', 'Honorários de Contencioso', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários de Contencioso' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Honorários de Advogado de Ação', 'Honorários para a condução de uma ação judicial', 'Receita', 'Honorários de Contencioso', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários de Contencioso' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Honorários de Sucesso', 'Percentual sobre o valor obtido em uma ação judicial de sucesso', 'Receita', 'Honorários de Contencioso', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Honorários de Contencioso' AND plano_de_contas_categoria = 'Receitas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserir subcontas (nível 2) para Outras Receitas
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Cursos e Palestras', 'Receitas provenientes da realização de cursos, workshops ou palestras sobre temas jurídicos', 'Receita', 'Outras Receitas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Outras Receitas' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Publicações e Livros', 'Receita de vendas de livros, artigos ou publicações jurídicas realizadas pelo escritório', 'Receita', 'Outras Receitas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Outras Receitas' AND plano_de_contas_categoria = 'Receitas' LIMIT 1)),
    ('', 'Parcerias e Convênios', 'Valor recebido por meio de parcerias ou convênios com outras empresas ou entidades', 'Receita', 'Outras Receitas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Outras Receitas' AND plano_de_contas_categoria = 'Receitas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);





------- DESPESAS
-- Inserindo as contas principais (nível 1) de Despesas
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel)
SELECT * FROM (VALUES
    ('', 'Despesas com Pessoal', 'Despesas relacionadas aos custos de funcionários e colaboradores', 'Despesa', 'Despesas', 1),
    ('', 'Despesas Operacionais', 'Despesas com o funcionamento diário do escritório', 'Despesa', 'Despesas', 1),
    ('', 'Marketing e Publicidade', 'Despesas relacionadas à promoção e publicidade do escritório', 'Despesa', 'Despesas', 1),
    ('', 'Impostos e Taxas', 'Impostos e taxas governamentais a pagar', 'Despesa', 'Despesas', 1),
    ('', 'Serviços de Terceiros', 'Despesas com serviços contratados de terceiros', 'Despesa', 'Despesas', 1)
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserindo subcontas (nível 2) para Despesas com Pessoal
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Salários e Ordenados', 'Salários pagos a funcionários do escritório', 'Despesa', 'Despesas com Pessoal', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas com Pessoal' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Encargos Sociais', 'Contribuições e impostos sobre a folha de pagamento', 'Despesa', 'Despesas com Pessoal', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas com Pessoal' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Benefícios', 'Despesas com benefícios para funcionários (vale-transporte, alimentação, etc.)', 'Despesa', 'Despesas com Pessoal', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas com Pessoal' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Férias e 13º Salário', 'Provisões para férias e 13º salário dos funcionários', 'Despesa', 'Despesas com Pessoal', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas com Pessoal' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Treinamentos e Capacitação', 'Investimentos em treinamentos e cursos para colaboradores', 'Despesa', 'Despesas com Pessoal', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas com Pessoal' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Recrutamento e Seleção', 'Despesas com processos seletivos e contratação de funcionários', 'Despesa', 'Despesas com Pessoal', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas com Pessoal' AND plano_de_contas_categoria = 'Despesas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserindo subcontas (nível 2) para Despesas Operacionais
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Aluguel do Escritório', 'Despesas com aluguel do espaço físico do escritório', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Água e Energia', 'Despesas com consumo de água e energia elétrica', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Telefone e Internet', 'Despesas com telefonia e serviços de internet', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Material de Escritório', 'Despesas com materiais de escritório e suprimentos', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Equipamentos de Informática', 'Despesas com compra e manutenção de equipamentos de informática', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Software e Licenças', 'Despesas com aquisição e manutenção de softwares e licenças', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Seguros', 'Despesas com seguros do escritório e de seus equipamentos', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Despesas com Limpeza', 'Despesas com serviços de limpeza do escritório', 'Despesa', 'Despesas Operacionais', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Despesas Operacionais' AND plano_de_contas_categoria = 'Despesas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserindo subcontas (nível 2) para Marketing e Publicidade
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Publicidade Online', 'Despesas com anúncios e campanhas online (Google Ads, Facebook, etc.)', 'Despesa', 'Marketing e Publicidade', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Marketing e Publicidade' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Publicidade Offline', 'Despesas com anúncios e campanhas offline (jornais, revistas, outdoors)', 'Despesa', 'Marketing e Publicidade', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Marketing e Publicidade' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Promoções e Descontos', 'Despesas com promoções e descontos oferecidos aos clientes', 'Despesa', 'Marketing e Publicidade', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Marketing e Publicidade' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Material Promocional', 'Despesas com materiais promocionais (panfletos, folders, etc.)', 'Despesa', 'Marketing e Publicidade', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Marketing e Publicidade' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Eventos e Networking', 'Despesas com participação em eventos e networking', 'Despesa', 'Marketing e Publicidade', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Marketing e Publicidade' AND plano_de_contas_categoria = 'Despesas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserindo subcontas (nível 2) para Impostos e Taxas
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'ISS (Imposto Sobre Serviços)', 'Imposto municipal sobre os serviços prestados', 'Despesa', 'Impostos e Taxas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Impostos e Taxas' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'IR (Imposto de Renda)', 'Imposto de renda devido ao governo', 'Despesa', 'Impostos e Taxas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Impostos e Taxas' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Taxa de Licenciamento e Registro', 'Despesas com taxas de licenciamento e registro de atividades', 'Despesa', 'Impostos e Taxas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Impostos e Taxas' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Contribuições Federais', 'Outras contribuições e taxas federais a pagar', 'Despesa', 'Impostos e Taxas', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Impostos e Taxas' AND plano_de_contas_categoria = 'Despesas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
);

-- Inserindo subcontas (nível 2) para Serviços de Terceiros
INSERT INTO plano_de_contas (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
SELECT * FROM (VALUES
    ('', 'Consultoria Contábil', 'Despesas com serviços contábeis contratados', 'Despesa', 'Serviços de Terceiros', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Serviços de Terceiros' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Consultoria Jurídica Externa', 'Despesas com consultoria jurídica contratada externamente', 'Despesa', 'Serviços de Terceiros', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Serviços de Terceiros' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Serviços de Limpeza', 'Despesas com serviços de limpeza externos', 'Despesa', 'Serviços de Terceiros', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Serviços de Terceiros' AND plano_de_contas_categoria = 'Despesas' LIMIT 1)),
    ('', 'Tecnologia e Suporte de TI', 'Despesas com suporte técnico e manutenção de tecnologia', 'Despesa', 'Serviços de Terceiros', 2, (SELECT codigo::integer FROM plano_de_contas WHERE plano_de_contas_nome = 'Serviços de Terceiros' AND plano_de_contas_categoria = 'Despesas' LIMIT 1))
) AS new_entries (plano_de_contas_codigo, plano_de_contas_nome, plano_de_contas_descricao, plano_de_contas_tipo, plano_de_contas_categoria, plano_de_contas_nivel, pai_id)
WHERE NOT EXISTS (
    SELECT 1
    FROM plano_de_contas
    WHERE plano_de_contas.plano_de_contas_nome = new_entries.plano_de_contas_nome
      AND plano_de_contas.plano_de_contas_categoria = new_entries.plano_de_contas_categoria
); */









CREATE TABLE IF NOT EXISTS caixa_movimentacoes
(
    codigo serial NOT NULL,
    caixa_movimentacao_codigo character varying COLLATE pg_catalog."default",
    business_id character varying NOT NULL,
    pagador_codigo character varying COLLATE pg_catalog."default",
    pagador_nome_completo character varying COLLATE pg_catalog."default",
    pagador_documento character varying COLLATE pg_catalog."default",
    plano_de_contas_codigo character varying COLLATE pg_catalog."default",
    plano_de_contas_nome character varying COLLATE pg_catalog."default",
    boleto_codigo character varying COLLATE pg_catalog."default",
    cobranca_codigo character varying COLLATE pg_catalog."default",
    conta_bancaria_id character varying COLLATE pg_catalog."default",
    descricao character varying COLLATE pg_catalog."default",
    valor numeric,
    estornado boolean NOT NULL DEFAULT false,
    motivo_estorno character varying COLLATE pg_catalog."default",
    data_estorno timestamp with time zone,
    saldo_anterior numeric,
    saldo_atual numeric,
    created_at timestamp with time zone NOT NULL DEFAULT now(),
    usuario_codigo_criou character varying COLLATE pg_catalog."default",
    usuario_codigo_estornou character varying COLLATE pg_catalog."default",
    usuario_codigo_alterou character varying COLLATE pg_catalog."default",
    data_ultima_alteracao timestamp with time zone,
    CONSTRAINT caixa_movimentacoes_pkey PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS caixa_movimentacoes_eventos
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    caixa_movimentacao_codigo character varying NOT NULL,
    conta_bancaria_id character varying NOT NULL,
    evento_descricao character varying NOT NULL,
    evento_momento character varying NOT NULL,
    CONSTRAINT caixa_movimentacoes_eventos_pkey PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS emails
(
    codigo serial NOT NULL,
    email_codigo_track character varying,
    business_id character varying,
    destinatario_nome character varying,
    destinatario_email character varying,
    email_assunto character varying,
    email_mensagem text,
    email_situacao character varying,
    momento character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS atendimentos
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    cliente_id character varying NOT NULL,
    usuario_codigo_criou character varying COLLATE pg_catalog."default",
    atendimento_codigo character varying COLLATE pg_catalog."default",
    atendimento_etapa character varying COLLATE pg_catalog."default",
    atendimento_status character varying COLLATE pg_catalog."default",
    atendimento_descricao text COLLATE pg_catalog."default",
    atendimento_data_criacao character varying COLLATE pg_catalog."default",
    momento character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS atendimentos_eventos
(
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    atendimento_codigo character varying NOT NULL,
    evento_descricao character varying NOT NULL,
    momento character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS requests
(
    codigo serial NOT NULL,
    business_id character varying,
    usuario_id character varying,
    comando character varying,
    comando_payload character varying,
    request_codigo character varying,
    momento character varying,
    jwt character varying,
    total_eventos integer,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS requests_eventos
(
    codigo serial,
    request_codigo character varying,
    business_id character varying,
    evento_momento character varying,
    evento_descricao character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS agenda_eventos
(
    codigo serial NOT NULL,
    evento_codigo character varying,
    evento_plataforma_id character varying,
    titulo character varying,
    descricao character varying,
    dia_todo boolean,
    recorrencia integer,
    horario_inicio timestamp with time zone,
    horario_fim timestamp with time zone,
    momento timestamp with time zone,
    usuario_id character varying,
    business_id character varying,
    status character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS pessoas (
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    pessoa_codigo character varying NOT NULL,
    pessoa_nome character varying NOT NULL,
    pessoa_documento character varying NOT NULL,
    pessoa_data_nascimento character varying,
    pessoa_tipo character varying,
    pessoa_email character varying,
    pessoa_telefone character varying,
    pessoa_whatsapp character varying,
    pessoa_endereco character varying,
    pessoa_endereco_numero character varying,
    pessoa_endereco_complemento character varying,
    pessoa_endereco_bairro character varying,
    pessoa_endereco_cidade character varying,
    pessoa_endereco_estado character varying,
    pessoa_endereco_cep character varying,
    pessoa_nome_da_mae character varying,
    pessoa_cpf_da_mae character varying,
    pessoa_sexo character varying,
    pessoa_familiares character varying,
    pessoa_nome_pai character varying,
    pessoa_cpf_pai character varying,
    pessoa_e_cliente boolean NOT NULL DEFAULT false,
    pessoa_rg character varying,
    pessoa_pis character varying,
    pessoa_carteira_trabalho character varying,
    pessoa_telefones character varying,
    pessoa_emails character varying,
    pessoa_enderecos character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS pessoas_eventos (
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    pessoa_codigo character varying NOT NULL,
    evento_descricao character varying NOT NULL,
    evento_momento character varying NOT NULL,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS modelos_documentos (
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    modelo_codigo character varying NOT NULL,
    modelo_nome character varying NOT NULL,
    modelo_momento character varying NOT NULL,
    modelo_nome_arquivo character varying NOT NULL,
    modelo_deleted boolean NOT NULL DEFAULT false,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS modelos_documentos_eventos (
    codigo serial NOT NULL,
    business_id character varying NOT NULL,
    modelo_codigo character varying NOT NULL,
    evento_modelo_descricao character varying NOT NULL,
    evento_modelo_momento character varying NOT NULL,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos
(
    codigo serial NOT NULL,
    business_id character varying,
    processo_codigo character varying,
    processo_numero_cnj character varying,
    processo_data_ultima_movimentacao character varying,
    processo_quantidade_movimentacoes character varying,
    processo_demandante character varying,
    processo_demandado character varying,
    processo_ultima_movimentacao_descricao character varying,
    processo_ultima_movimentacao_data character varying,
    oab_consultada character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos_movimentacoes
(
    codigo serial NOT NULL,
    business_id character varying,
    movimentacao_codigo character varying,
    movimentacao_id_plataforma character varying,
    movimentacao_data character varying,
    movimentacao_tipo character varying,
    movimentacao_tipo_publicacao character varying,
    movimentacao_classificacao_predita_nome character varying,
    movimentacao_classificacao_predita_descricao character varying,
    movimentacao_classificacao_predita_hierarquia character varying,
    movimentacao_conteudo character varying,
    movimentacao_texto_categoria character varying,
    movimentacao_fonte_processo_fonte_id character varying,
    movimentacao_fonte_fonte_id character varying,
    movimentacao_fonte_nome character varying,
    movimentacao_fonte_tipo character varying,
    movimentacao_fonte_sigla character varying,
    movimentacao_fonte_grau character varying,
    movimentacao_fonte_grau_formatado character varying,
    processo_codigo character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos_consultados_por_oab
(
    codigo serial NOT NULL,
    consulta_oab_codigo character varying,
    business_id character varying,
    usuario_id character varying,
    oab_consultada character varying,
    momento timestamp with time zone,
    mensagem character varying,
    payload_request character varying,
    payload_response character varying,
    request_status character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos_consultados_por_oab_eventos
(
    codigo serial NOT NULL,
    business_id character varying,
    consulta_oab_codigo character varying,
    evento_descricao character varying,
    evento_momento character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos_fontes
(
    codigo serial NOT NULL,
    business_id character varying,
    processo_codigo character varying,
    fonte_codigo character varying,
    fonte_nome character varying,
    fonte_descricao character varying,
    fonte_link character varying,
    fonte_tipo character varying,
    fonte_data_ultima_verificacao character varying,
    fonte_data_ultima_movimentacao character varying,
    fonte_segredo_justica boolean,
    fonte_arquivado boolean,
    fonte_fisico boolean,
    fonte_sistema character varying,
    fonte_quantidade_envolvidos integer,
    fonte_quantidade_movimentacoes integer,
    fonte_grau integer,
    fonte_capa_classe character varying,
    fonte_capa_assunto character varying,
    fonte_capa_area character varying,
    fonte_capa_orgao_julgador character varying,
    fonte_capa_valor_causa character varying,
    fonte_capa_valor_moeda character varying,
    fonte_capa_data_distribuicao character varying,
    fonte_tribunal_id character varying,
    fonte_tribunal_nome character varying,
    fonte_tribunal_sigla character varying,
    fonte_informacoes_complementares character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos_envolvidos (
    codigo serial NOT NULL,
    business_id character varying,
    processo_codigo character varying,
    fonte_codigo character varying,
    envolvido_codigo character varying,
    envolvido_tipo character varying,
    envolvido_nome character varying,
    envolvido_documento character varying,
    envolvido_oab character varying,
    envolvido_tipo_pessoa character varying,
    envolviodo_quantidade_processos integer,
    pessoa_codigo character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS processos_usuarios_responsaveis
(
    codigo serial NOT NULL,
    business_id character varying,
    processo_codigo character varying,
    usuario_id character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS consultas_informacoes_documento
(
    codigo serial NOT NULL,
    business_id character varying,
    usuario_id character varying,
    documento_consultado character varying,
    momento timestamp with time zone,
    mensagem character varying,
    payload_request character varying,
    payload_response character varying,
    request_status character varying,
    PRIMARY KEY (codigo)
);

CREATE TABLE IF NOT EXISTS recebimento_webhook
(
    codigo serial NOT NULL,
    webhook_event_id_plataforma character varying,
    webhook_header character varying,
    webhook_payload character varying,
    webhook_ip character varying,
    webhook_method character varying,
    webhook_uri character varying,
    webhook_momento timestamp with time zone,
    webhook_parceiro character varying,
    webhook_user_agent character varying,
    PRIMARY KEY (codigo)
);