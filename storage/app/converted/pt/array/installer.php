<?php
return [
    'welcome'           => [
        'name'      => 'Pagar dinheiro',
        'version'   => 'B 1.7',
        'title'     => 'Bem vindo ao Instalador!',
        'sub-title' => 'Seu servidor tem todos os requisitos e permissões para este aplicativo. Antes de lançarmos, precisamos de algumas informações sobre seu banco de dados:',
        'item1'     => 'Nome do banco de dados',
        'item2'     => 'Database login',
        'item3'     => 'Senha do banco de dados',
        'item4'     => 'Host do banco de dados',
        'message'   => 'Usaremos essas informações para atualizar o arquivo de ambiente',
        'button'    => 'Vamos lá !',

    ],
    'database'          => [
        'title'          => 'Configuração do banco de dados',
        'sub-title'      => 'Se você não sabe como preencher este formulário, entre em contato com sua hospedagem.',
        'dbname-label'   => 'Nome do banco de dados (onde você deseja que seu aplicativo esteja)',
        'username-label' => 'Nome de usuário (seu login no banco de dados)',
        'password-label' => 'Senha (sua senha de banco de dados)',
        'host-label'     => 'Nome do host (deve ser "localhost", se não funcionar, pergunte ao seu hoster)',
        'button'         => 'Mandar',
        'wait'           => 'Um pouco de paciência ...',

    ],
    'database-error'    => [
        'title'     => 'Erro de conexão com o banco de dados',
        'sub-title' => 'Não podemos nos conectar ao banco de dados com suas configurações:',
        'item1'     => 'Você tem certeza do seu nome de usuário e senha?',
        'item2'     => 'Tem certeza do seu nome de host?',
        'item3'     => 'Tem certeza de que seu servidor de banco de dados está funcionando?',
        'message'   => 'Se você não estiver muito certo de entender todos esses termos, entre em contato com seu hospedeiro.',
        'button'    => 'Tente novamente !',

    ],
    'requirement-error' => [
        'title'       => 'Existe um erro de requisito',
        'requirement' => 'Nós não podemos instalar este aplicativo porque este requisito do PHP está faltando:',
        'php-version' => 'A versão do PHP deve ser pelo menos 5.5.9, mas sua versão é',
        'message'     => 'Você deve corrigir este erro para continuar a instalação!',

    ],
    'permission-error'  => [
        'title'     => 'Existe um erro de permissão',
        'sub-title' => 'Nós não podemos instalar este aplicativo porque esta pasta não é gravável:',
        'message'   => 'Você deve corrigir este erro para continuar a instalação!',

    ],
    'register'          => [
        'title'      => 'Criação de administrador',
        'sub-title'  => 'Agora você deve inserir informações para criar o administrador',
        'base-label' => 'Seu',
        'message'    => 'Você precisará da sua senha para fazer o login, então mantenha-a segura!',
        'button'     => 'Mandar',

    ],
    'register-fields'   => [
        'first_name' => 'Primeiro nome',
        'last_name'  => 'Último nome',
        'email'      => 'o email',
        'password'   => 'senha',

    ],
    'end'               => [
        'title'     => 'Instalação bem sucedida!',
        'sub-title' => 'O aplicativo e agora instalado e você pode usá-lo',
        'login'     => 'Seu Login :',
        'password'  => 'Sua senha :',
        'button'    => 'Login',

    ]];
