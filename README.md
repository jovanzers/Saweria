# Saweria
Scrap Saweria.co payment status

## Usage
With Order ID:  
`https://domain.com/saweria.php?oid=9d435e23-3658-55b4-bee3-40827230bbab`  

With URL:  
`https://domain.com/saweria.php?oid=https://saweria.co/receipt/9d435e23-3658-55b4-bee3-40827230bbab`

### Output
```
{
    "OrderId": "9d435e23-3658-55b4-bee3-40827230bbab",
    "OrderDate": "2023-02-24 10:00",
    "PaymentMethod": "QRIS",
    "CendolCount": 1,
    "AdminFees": 40,
    "Total": 5040
}
```
