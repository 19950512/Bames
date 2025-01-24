# Variáveis Necessárias para a Pipeline

Abaixo estão listadas as variáveis de ambiente que devem ser configuradas nos **GitHub Secrets** para que a pipeline funcione corretamente.

| Nome da Variável           | Descrição                                                                 |
|----------------------------|---------------------------------------------------------------------------|
| `GITGUARDIAN_API_KEY`      | Chave de API para o **GitGuardian** para verificar segredos no código.    |
| `SSH_PRIVATE_KEY`          | Chave privada SSH usada para acessar o servidor.                         |
| `SERVER_IP`                | Endereço IP do servidor onde o deploy será realizado.                    |
| `DEPLOY_PATH`              | Caminho no servidor onde o projeto está localizado.                      |
| `BACKUP_PATH`              | Caminho no servidor para armazenar os backups.                          |
| `DOCKER_COMPOSE_FILE`      | Caminho completo para o arquivo `docker-compose.yml` no servidor.        |
| `CLOUDFLARE_ZONE_ID`       | ID da zona no Cloudflare para limpar o cache.                            |
| `CLOUDFLARE_API_TOKEN`     | Token de API do Cloudflare com permissões para gerenciar o cache.        |
| `DISCORD_WEBHOOK_URL`      | URL do webhook do Discord para enviar notificações.                      |

Certifique-se de configurar todas as variáveis antes de executar a pipeline.
