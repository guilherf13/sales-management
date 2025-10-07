<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório Diário de Vendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
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
        .sellers-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .sellers-table th,
        .sellers-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        .sellers-table th {
            background-color: #f8f9fa;
            font-weight: bold;
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
        <h1>Relatório Diário de Vendas</h1>
        <p>Relatório Administrativo de <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong></p>
    </div>

    <div class="content">
        <div class="summary">
            <h3>Resumo Geral</h3>
            <p><strong>Valor Total de Vendas:</strong> <span class="highlight">R$ {{ number_format($totalAmount, 2, ',', '.') }}</span></p>
            <p><strong>Número Total de Vendas:</strong> {{ $salesCount }}</p>
            <p><strong>Comissão Total Paga:</strong> R$ {{ number_format($totalAmount * 0.085, 2, ',', '.') }}</p>
        </div>

        <h3>Vendas por Vendedor</h3>
        @if(count($sellersSummary) > 0)
            <table class="sellers-table">
                <thead>
                    <tr>
                        <th>Nome do Vendedor</th>
                        <th>Email</th>
                        <th>Qtd. Vendas</th>
                        <th>Valor Total</th>
                        <th>Comissão</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sellersSummary as $summary)
                        <tr>
                            <td>{{ $summary['seller']->name }}</td>
                            <td>{{ $summary['seller']->email }}</td>
                            <td>{{ $summary['sales_count'] }}</td>
                            <td>R$ {{ number_format($summary['total_amount'], 2, ',', '.') }}</td>
                            <td>R$ {{ number_format($summary['total_commission'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Nenhuma venda foi registrada para esta data.</p>
        @endif
    </div>

    <div class="footer">
        <p>Este é um relatório automático do Sistema de Gestão de Vendas.</p>
        <p>Gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
