
# **B A M E S**

## **Descrição**

O **BAMES** é um projeto focado em estudos de arquitetura de software, design de sistemas e testes. Atualmente, o projeto está sendo desenvolvido com um foco específico no Brasil, e, por isso, a documentação e o desenvolvimento seguem a linguagem portuguesa.

## **Arquitetura**

O projeto segue uma arquitetura baseada em sistemas distribuídos, utilizando comunicação assíncrona. A comunicação entre os serviços é realizada por meio de mensageria, com o **RabbitMQ** atuando como broker.

### **Camadas da Arquitetura**
O projeto é dividido em três camadas principais:
- **Aplicação**: Responsável pela lógica de negócio e pela interação com o usuário.
- **Domínio**: Onde residem as regras de negócio e os agregados do sistema.
- **Infraestrutura**: Onde ficam os detalhes de implementação, como comunicação entre serviços, persistência de dados e integração com outros sistemas.

## **Design**

O design do projeto segue os princípios do **Domain Driven Design (DDD)**, promovendo uma separação clara de responsabilidades entre as camadas **Aplicação**, **Domínio** e **Infraestrutura**. Isso facilita a manutenção, escalabilidade e evolução do sistema.

## **Testes**

O projeto utiliza o **Pest** como framework de testes, oferecendo uma sintaxe simples e intuitiva para escrever testes automatizados.

### **Cobertura de Testes**
A cobertura de testes do projeto pode ser visualizada diretamente na página [Coverage](https://19950512.github.io/bames).

## **Tecnologias Utilizadas**

O **BAMES** é construído utilizando as seguintes tecnologias:
- **PHP 8.3**: Versão atual do PHP utilizada no projeto.
- **Docker Compose**: Utilizado para orquestrar os containers e facilitar o ambiente de desenvolvimento.
- **PostgreSQL**: Banco de dados relacional utilizado para armazenamento de dados.
- **Redis**: Utilizado como cache e broker de mensagens.
- **Nginx**: Servidor web utilizado para balanceamento de carga e proxy reverso.
- **Firebase JWT**: Para autenticação e controle de acesso via tokens JWT.
- **RabbitMQ**: Broker de mensagens utilizado para comunicação assíncrona entre os serviços.
- **Flutter**: Framework utilizado para o desenvolvimento de aplicativos móveis.

## **Como Rodar os Testes e Visualizar a Cobertura**

Para rodar os testes e verificar a cobertura, execute o seguinte comando:
```bash
composer coverage
```

### **Problema Comum**
Caso você encontre o seguinte erro:
```
ERROR: No code coverage driver is available.
```
Isso indica que o **xdebug** não está instalado ou configurado corretamente.

### **Resolução**

1. **Instalar o xdebug para PHP 8.3**:
    ```bash
    apt install php8.3-xdebug
    ```

2. **Configurar o xdebug no PHP**:
    - No seu sistema **Linux**, edite o arquivo `php.ini` localizado em `/etc/php/8.3/cli/php.ini`.
    - Adicione as seguintes linhas no final do arquivo:
    ```conf
    [xdebug]
    zend_extension=xdebug.so
    xdebug.mode=coverage
    xdebug.start_with_request=yes
    ```

3. **Reiniciar o servidor PHP**:
    Após a configuração, reinicie o servidor PHP para aplicar as mudanças.

Agora, a cobertura de testes deve funcionar corretamente.

## **Documentação das APIs**

### **Atualizando a Documentação**
Para atualizar a documentação das APIs, execute o comando abaixo:
```bash
npx @redocly/cli build-docs src/Infraestrutura/APIs/Autenticacao/doc.yaml -o doc-api-auth.html --title API - Auth
```

### **Links das Documentações**
- [API Auth](doc-api-auth.html)

## **Dependências**

### **Doc2PDF**
O projeto utiliza a ferramenta **unoconv** para converter arquivos DOCX em PDF. Para instalar as dependências necessárias, execute os seguintes comandos:

1. **Instalar o LibreOffice e o unoconv**:
    ```bash
    sudo apt install libreoffice
    sudo apt install unoconv
    ```

2. **Converter arquivos DOCX para PDF**:
    Após a instalação, você pode usar o comando abaixo para realizar a conversão:
    ```bash
    sudo /usr/bin/doc2pdf --headless -o /tmp/doc2pdf.pdf /path/to/documento.docx
    ```

---

### Considerações Finais
Esse documento tem como objetivo fornecer informações claras e diretas sobre o projeto **BAMES**, suas tecnologias, arquitetura e como colaborar com os testes e documentação. O projeto está em constante evolução, e qualquer contribuição é bem-vinda!
