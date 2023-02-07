<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            /* Add some styling for the invoice */
            * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            }

            body {
            background-color: #f2f2f2;
            padding: 30px;
            }

            .invoice-container {
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0px 0px 10px #ccc;
            color: #333;
            margin: auto;
            max-width: 800px;
            padding: 20px;
            }

            .invoice-header {
            background-color: #8F7C5C;
            color: #fff;
            padding: 10px;
            text-align: center;
            }

            .invoice-header h1 {
            margin: 0;
            }

            .invoice-info {
            margin-top: 20px;
            text-align: center;
            }

            .invoice-info p {
            margin: 5px 0;
            }

            .invoice-table {
            margin-top: 20px;
            width: 100%;
            }

            .invoice-table th,
            .invoice-table td {
            border-bottom: 1px solid #ccc;
            padding: 10px;
            text-align: left;
            }

            .invoice-table th {
            background-color: #8F7C5C;
            color: #fff;
            }

            .invoice-total {
            margin-top: 20px;
            text-align: right;
            }

            @media only screen and (max-width: 600px) {
            /* Add responsive styles for small screens */
            .invoice-container {
                width: 100%;
            }
            }
        </style>  
    </head>
    <body>
        <!-- Add the invoice content -->
        <div class="invoice-container">
            <div class="invoice-header">
            <h1>Invoice</h1>
            </div>
            <div class="invoice-info">
            <p>Date: {{ date('Y-m-d') }}</p>
            <p>Invoice : INV000001</p>
            </div>
            <table class="invoice-table">
                <thead>
                    <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                        <tr>
                        <td>Javeriya</td>
                        <td>Premium</td>
                        <td>10 GBP</td>
                        </tr>
                </tbody>
            </table>
            <div class="invoice-total">
                <p>Total: 10 GBP</p>
            </div>
        </div>
    </body>
</html>
