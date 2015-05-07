# youtube-api-v3

Esta classe foi criada para obter os dados de vídeos através da API v3.

A API KEY agora deve ser criada no console de desenvolvedor do Google
https://console.developers.google.com/
Para correta utilização deverá ser criado o projeto e obter a API_KEY, como se segue: 

- Primeiro cria-se o projeto, e confirma se a permissão do usuário da conta esta OK
- vá em "APIs e Autenticações > APIs" localize a do Youtube e ative
- apos vá em "Credenciais" e crie uma "chave de acesso público"
- confirme que ela esteja com a opção "Qualquer IP permitido" ativo 
- o valor retornado e a API_KEY	

Atenção: 
Requer o modulo CURL instalado no PHP caso contrario deverá ser modificado o método getData() para utilização do file_get_contents

