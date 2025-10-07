<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório Diário de Comissões</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .summary {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .highlight {
            color: #007bff;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Diário de Comissões</h1>
        <p>Olá {{ $seller->name }},</p>
    </div>

    <div class="content">
        <p>Aqui está o resumo das suas vendas do dia <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong>:</p>

        <div class="summary">
            <h3>Resumo de Vendas</h3>
            <p><strong>Número de Vendas:</strong> {{ $salesCount }}</p>
            <p><strong>Valor Total das Vendas:</strong> R$ {{ number_format($totalAmount, 2, ',', '.') }}</p>
            <p><strong>Comissão Total (8,5%):</strong> <span class="highlight">R$ {{ number_format($totalCommission, 2, ',', '.') }}</span></p>
        </div>

        <p>Obrigado pelo seu trabalho! Continue com essa ótima performance de vendas.</p>
    </div>

    <div class="footer">
        <p>Este é um relatório automático do Sistema de Gestão de Vendas.</p>
        <p>Se tiver alguma dúvida, entre em contato com a administração.</p>
    </div>
</body>
</html>
