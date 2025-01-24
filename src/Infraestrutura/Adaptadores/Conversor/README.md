# Docx to PDF

Instruções para a instalação no servidor, tanto para o ambiente de desenvolvimento quanto para o ambiente de produção.

Instalar o Universal Office Converter (unoconv) no servidor:
```bash
sudo apt install unoconv
```

Criar e dar as permissões necessárias para as pastas de cache do dconf e do libreoffice:
```bash
sudo mkdir -p /var/www/.cache/dconf && chown -R www-data:www-data /var/www/.cache && mkdir -p /var/www/.config/libreoffice && chown -R www-data:www-data /var/www/.config/libreoffice && chmod -R 777 /tmp /var/tmp /var/www/.cache /var/www/.config/libreoffice
```
# Configurar variáveis de ambiente para o LibreOffice
````bash
sudo nano /etc/environment
````
Adicione as seguintes variáveis de ambiente no final do arquivo:
````env
SAL_USE_VCLPLUGIN=gtk
LIBREOFFICE_USER_PROFILE=/tmp/.libreoffice
````

Salve, feche o arquivo e execute o comando abaixo para aplicar as alterações:
````bash
source /etc/environment
````
