<!DOCTYPE html>
<html>

<head>
    <meta charset='utf-8'>
    <title>{{ config('app.name', 'LLM Text generator') }}</title>
    <meta name="shopify-api-key" content="{{ config('shopify-app.api_key') }}" />
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
</head>

<body>
    <div id="app"></div>

   <script type="application/json" id="shop_data">{!!  json_encode( $user)  !!}</script>
   <script type="application/json" id="planConfig">{!!  json_encode( $planConfig)  !!}</script>

    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
</body>

</html>