
# Estratégia de Migrations e Snapshot de Banco de Dados

Este arquivo documenta a estratégia utilizada para gerenciar a estrutura do banco de dados no projeto. Em vez de manter um histórico contínuo de migrations, adotamos uma abordagem baseada em snapshots a cada 2 anos, utilizando um arquivo `init.sql` que contém a estrutura completa do banco de dados.

## Objetivo

A ideia por trás dessa estratégia é simplificar a manutenção do banco de dados, mantendo o projeto mais enxuto, sem a sobrecarga de gerenciar um grande número de migrations antigas. O arquivo `init.sql` serve como um "snapshot" da estrutura do banco de dados, refletindo a versão mais recente da estrutura e garantindo que novos desenvolvedores ou ambientes de produção possam configurar rapidamente o banco de dados.

## Estratégia

### **1. Snapshot Atualizado a Cada 2 Anos**
A cada 2 anos ou após uma atualização significativa da estrutura do banco, o arquivo `init.sql` é atualizado para refletir a versão mais recente do banco de dados. Durante esse processo:
- Todas as mudanças de estrutura (como a criação de tabelas, modificações de colunas, etc.) são consolidadas em um único arquivo `init.sql`.
- As migrations antigas são removidas do repositório para reduzir a complexidade.

### **2. Remoção de Migrations Antigas**
As migrations que foram aplicadas antes do snapshot mais recente são removidas. Isso simplifica o gerenciamento do banco de dados e elimina o risco de conflitos ou erros ao tentar rodar migrations antigas. Ao invés de manter uma lista de migrations históricas, o `init.sql` agora contém toda a estrutura necessária.

### **3. Atualização do `init.sql`**
O arquivo `init.sql` contém toda a estrutura do banco de dados, incluindo:
- Criação de tabelas
- Relacionamentos
- Índices
- Restrições

O arquivo `init.sql` pode ser facilmente aplicado em um novo ambiente, criando a estrutura do banco a partir do zero.

### **4. Como Configurar o Banco de Dados**
Um novo desenvolvedor ou um ambiente de produção pode configurar o banco de dados com apenas um comando, aplicando o `init.sql`. O caminho para o arquivo `init.sql` no repositório é:

```
docker/postgres/init.sql
```

Para rodar o script em um banco PostgreSQL, o desenvolvedor pode usar o seguinte comando:

```bash
psql -U jusizi -h localhost -d jusizi -a -f docker/postgres/init.sql
```

Isso cria a estrutura completa do banco no ambiente especificado.

## Benefícios

### **Simplicidade**
- Com essa abordagem, a configuração do banco de dados se torna simples e rápida. Em vez de precisar aplicar múltiplas migrations para chegar ao estado atual, o desenvolvedor só precisa rodar o `init.sql` para configurar o banco de dados do zero.

### **Menos Complexidade**
- O repositório fica mais limpo e fácil de gerenciar, já que não há a necessidade de manter o histórico completo de migrations. A atualização periódica do `init.sql` garante que a estrutura atual esteja sempre documentada.

### **Redução de Erros**
- Não há o risco de problemas ao aplicar migrations antigas em versões mais novas do banco de dados. Com o snapshot, o banco sempre estará na versão mais recente, sem depender de uma sequência de migrations.

## Desvantagens

### **Perda de Histórico Detalhado**
- A principal desvantagem dessa abordagem é que o histórico de mudanças no banco de dados se perde. Se for necessário auditar as mudanças ou entender a evolução detalhada da estrutura do banco, isso pode ser mais difícil. Porém, para a maioria dos projetos, essa abordagem é suficiente, e a complexidade de gerenciar migrations antigas pode ser evitada.

## Quando Usar Esta Estratégia?

Essa estratégia é recomendada para projetos que:
- Não necessitam de um histórico detalhado de mudanças no banco de dados.
- Buscam simplicidade e rapidez na configuração de novos ambientes.
- Não possuem requisitos rígidos de auditoria ou conformidade.

Caso a auditoria de mudanças seja necessária, considere manter um repositório separado para armazenar migrations antigas, além do `init.sql`.

## Conclusão

A estratégia de usar um `init.sql` atualizado periodicamente em vez de manter migrations contínuas permite um gerenciamento mais simples e eficaz da estrutura do banco de dados. Atualizações a cada 2 anos garantem que a estrutura do banco esteja sempre documentada, enquanto a remoção de migrations antigas mantém o projeto limpo e fácil de manter.
