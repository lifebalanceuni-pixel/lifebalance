<?php
// Configurações do Banco de Dados
$host = 'sql205.infinityfree.com';
$dbname = 'if0_39680722';  // Substitua pelo nome real do seu banco
$username = 'if0_39680722';    // Substitua pelo seu usuário
$password = 'i1Yy8MJHt0Pp37';               // Substitua pela sua senha

// Conexão com o banco de dados
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criação da tabela se não existir
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome_completo VARCHAR(60) NOT NULL,
        cpf BIGINT(11) NOT NULL UNIQUE,
        telefone BIGINT(11) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        senha VARCHAR(20) NOT NULL,
        data_nascimento DATE NOT NULL,
        estado VARCHAR(100) NOT NULL,
        cidade VARCHAR(100) NOT NULL,
        endereco TEXT NOT NULL,
        avatar VARCHAR(255),
        data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

// Processamento do formulário
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Coletar dados do formulário
    $dados = [
        'nome_completo' => $_POST['nome_completo'] ?? '',
        'cpf' => str_replace(['.', '-'], '', $_POST['cpf'] ?? ''),
        'telefone' => str_replace(['(', ')', ' ', '-'], '', $_POST['telefone'] ?? ''),
        'email' => $_POST['email'] ?? '',
        'senha' => $_POST['senha'] ?? '',
        'confirmar_senha' => $_POST['confirmar_senha'] ?? '',
        'data_nascimento' => $_POST['data_nascimento'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'cidade' => $_POST['cidade'] ?? '',
        'endereco' => $_POST['endereco'] ?? ''
    ];

    // Validações
    if ($dados['senha'] !== $dados['confirmar_senha']) {
        $message = 'As senhas não coincidem!';
        $messageType = 'error';
    } elseif (strlen($dados['senha']) < 8) {
        $message = 'A senha deve ter pelo menos 8 caracteres!';
        $messageType = 'error';
    } else {
        // Processar upload da imagem
        $avatar = '';
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extensao = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $nomeArquivo = uniqid('avatar_') . '.' . $extensao;
            $caminhoCompleto = $uploadDir . $nomeArquivo;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $caminhoCompleto)) {
                $avatar = $caminhoCompleto;
            }
        }

        try {
            // Inserir no banco de dados
            $stmt = $pdo->prepare("INSERT INTO usuarios (
                nome_completo, cpf, telefone, email, senha,
                data_nascimento, estado, cidade, endereco, avatar
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $dados['nome_completo'],
                $dados['cpf'],
                $dados['telefone'],
                $dados['email'],
                $dados['senha'],
                $dados['data_nascimento'],
                $dados['estado'],
                $dados['cidade'],
                $dados['endereco'],
                $avatar
            ]);
            
            $message = 'Cadastro realizado com sucesso!';
            $messageType = 'success';
            
            // Limpar formulário após sucesso
            echo '<script>document.getElementById("cadastroForm").reset();</script>';
            
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                $message = 'Erro: CPF ou E-mail já cadastrado!';
            } else {
                $message = 'Erro no cadastro: ' . $e->getMessage();
            }
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro Profissional | Sistema Completo</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #3f37c9;
            --accent: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --success: #4cc9f0;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f5f7ff;
            color: var(--dark);
            line-height: 1.6;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .illustration-section {
            flex: 1;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .illustration-section::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            top: -100px;
            right: -100px;
        }

        .illustration-section::after {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            bottom: -50px;
            left: -50px;
        }

        .illustration-img {
            width: 70%;
            max-width: 400px;
            margin-bottom: 2rem;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .illustration-content {
            text-align: center;
            z-index: 1;
            max-width: 500px;
        }

        .illustration-content h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .illustration-content p {
            opacity: 0.9;
            font-weight: 300;
        }

        .form-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .form-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 3rem;
            width: 100%;
            max-width: 600px;
            transform: translateY(0);
            transition: var(--transition);
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .form-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-family: 'Roboto', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: #fafafa;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.2);
            background-color: white;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        .btn {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.8rem 2rem;
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .terms {
            font-size: 0.8rem;
            color: var(--gray);
            text-align: center;
            margin-top: 1.5rem;
        }

        .terms a {
            color: var(--primary);
            text-decoration: none;
        }

        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #f0f0f0;
            background-image: url('https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80');
            background-size: cover;
            background-position: center;
            margin-bottom: 1rem;
            border: 3px solid white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .avatar-upload label {
            background: var(--light);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            font-size: 0.8rem;
            cursor: pointer;
            transition: var(--transition);
            border: 1px dashed var(--gray);
        }

        .avatar-upload label:hover {
            background: #e9ecef;
        }

        .avatar-upload input[type="file"] {
            display: none;
        }

        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }

            .illustration-section {
                padding: 2rem 1rem;
            }

            .illustration-content h2 {
                font-size: 1.5rem;
            }

            .form-container {
                padding: 2rem;
            }
        }

        @media (max-width: 576px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-container {
                padding: 1.5rem;
            }

            .form-header h1 {
                font-size: 1.5rem;
            }
        }
        
        .server-response {
            padding: 15px;
            margin: 15px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <section class="illustration-section">
            <img src="https://www.rainforest-alliance.org/wp-content/uploads/2021/06/capybara-square-1-400x400.jpg.webp" alt="Cadastro" class="illustration-img">
            <div class="illustration-content">
                <h2>Junte-se à nossa comunidade</h2>
                <p>Preencha seus dados para ter acesso completo a todos os nossos serviços e recursos exclusivos para membros.</p>
            </div>
        </section>

        <section class="form-section">
            <div class="form-container">
                <div class="form-header">
                    <h1>Crie sua conta</h1>
                    <p>Preencha o formulário abaixo para se cadastrar</p>
                </div>

                <?php if ($message): ?>
                    <div class="server-response <?= $messageType ?>">
                        <?= $message ?>
                    </div>
                <?php endif; ?>

                <form id="cadastroForm" method="POST" enctype="multipart/form-data">
                    <div class="avatar-upload">
                        <div class="avatar-preview"></div>
                        <label for="avatar">Escolher foto de perfil</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="nome_completo">Nome Completo</label>
                        <input type="text" id="nome_completo" name="nome_completo" class="form-control" placeholder="Digite seu nome completo" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control" placeholder="seu@email.com" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" placeholder="(00) 00000-0000" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" class="form-control" placeholder="000.000.000-00" required>
                        </div>
                        <div class="form-group">
                            <label for="data_nascimento">Data de Nascimento</label>
                            <input type="date" id="data_nascimento" name="data_nascimento" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="endereco">Endereço Completo</label>
                        <input type="text" id="endereco" name="endereco" class="form-control" placeholder="Rua, número, complemento" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="cidade">Cidade</label>
                            <input type="text" id="cidade" name="cidade" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="estado">Estado</label>
                            <select id="estado" name="estado" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <option value="AP">Amapá</option>
                                <option value="AM">Amazonas</option>
                                <option value="BA">Bahia</option>
                                <option value="CE">Ceará</option>
                                <option value="DF">Distrito Federal</option>
                                <option value="ES">Espírito Santo</option>
                                <option value="GO">Goiás</option>
                                <option value="MA">Maranhão</option>
                                <option value="MT">Mato Grosso</option>
                                <option value="MS">Mato Grosso do Sul</option>
                                <option value="MG">Minas Gerais</option>
                                <option value="PA">Pará</option>
                                <option value="PB">Paraíba</option>
                                <option value="PR">Paraná</option>
                                <option value="PE">Pernambuco</option>
                                <option value="PI">Piauí</option>
                                <option value="RJ">Rio de Janeiro</option>
                                <option value="RN">Rio Grande do Norte</option>
                                <option value="RS">Rio Grande do Sul</option>
                                <option value="RO">Rondônia</option>
                                <option value="RR">Roraima</option>
                                <option value="SC">Santa Catarina</option>
                                <option value="SP">São Paulo</option>
                                <option value="SE">Sergipe</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha">Crie uma senha</label>
                        <input type="password" id="senha" name="senha" class="form-control" placeholder="Mínimo 8 caracteres" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha">Confirme sua senha</label>
                        <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
                    </div>

                    <button type="submit" class="btn">Cadastrar</button>

                    <p class="terms">Ao se cadastrar, você concorda com nossos <a href="#">Termos de Serviço</a> e <a href="#">Política de Privacidade</a>.</p>
                </form>
            </div>
        </section>
    </div>

    <script>
        // Validação e formatação de CPF
        document.getElementById('cpf').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 3 && value.length <= 6) {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
            } else if (value.length > 6 && value.length <= 9) {
                value = value.replace(/(\d{3})(\d{3})(\d)/, '$1.$2.$3');
            } else if (value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d)/, '$1.$2.$3-$4');
            }
            e.target.value = value;
        });

        // Formatação de telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 2) {
                    value = '(' + value;
                } else if (value.length <= 6) {
                    value = '(' + value.substring(0,2) + ') ' + value.substring(2);
                } else if (value.length <= 10) {
                    value = '(' + value.substring(0,2) + ') ' + value.substring(2,6) + '-' + value.substring(6);
                } else {
                    value = '(' + value.substring(0,2) + ') ' + value.substring(2,7) + '-' + value.substring(7,11);
                }
            }
            e.target.value = value;
        });

        // Preview da foto de perfil
        document.getElementById('avatar').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('.avatar-preview').style.backgroundImage = `url(${event.target.result})`;
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        // Validação de senha antes de enviar
        document.getElementById('cadastroForm').addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            
            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return;
            }
            
            if (senha.length < 8) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 8 caracteres!');
                return;
            }
            
            // Validação de CPF (11 dígitos)
            const cpf = document.getElementById('cpf').value.replace(/\D/g, '');
            if (cpf.length !== 11) {
                e.preventDefault();
                alert('CPF deve ter 11 dígitos!');
                return;
            }
        });
    </script>
</body>
</html>