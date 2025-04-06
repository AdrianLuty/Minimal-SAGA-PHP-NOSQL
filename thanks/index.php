<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Thank You for Your Purchase</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 100px;
        }
        h1 {
            color: green;
        }
    </style>
</head>
<body>
<h1>Thank you for your purchase!</h1>
<p>Your order has been successfully registered.</p>
<script>
Object.keys(localStorage).forEach(function(key) {
    if (key.startsWith("cart_") || key.startsWith("time_")) {
        localStorage.removeItem(key);
    }
});
sessionStorage.clear();
</script>
</body>
</html>
