<?php return array (
  'brozot/laravel-fcm' => 
  array (
    'providers' => 
    array (
      0 => 'LaravelFCM\\FCMServiceProvider',
    ),
    'aliases' => 
    array (
      'FCM' => 'LaravelFCM\\Facades\\FCM',
      'FCMGroup' => 'LaravelFCM\\Facades\\FCMGroup',
    ),
  ),
  'devinweb/laravel-hyperpay' => 
  array (
    'providers' => 
    array (
      0 => 'Devinweb\\LaravelHyperpay\\LaravelHyperpayServiceProvider',
    ),
    'aliases' => 
    array (
      'LaravelHyperpay' => 'Devinweb\\LaravelHyperpay\\Facades\\LaravelHyperpay',
    ),
  ),
  'facade/ignition' => 
  array (
    'providers' => 
    array (
      0 => 'Facade\\Ignition\\IgnitionServiceProvider',
    ),
    'aliases' => 
    array (
      'Flare' => 'Facade\\Ignition\\Facades\\Flare',
    ),
  ),
  'fideloper/proxy' => 
  array (
    'providers' => 
    array (
      0 => 'Fideloper\\Proxy\\TrustedProxyServiceProvider',
    ),
  ),
  'fruitcake/laravel-cors' => 
  array (
    'providers' => 
    array (
      0 => 'Fruitcake\\Cors\\CorsServiceProvider',
    ),
  ),
  'grimzy/laravel-mysql-spatial' => 
  array (
    'providers' => 
    array (
      0 => 'Grimzy\\LaravelMysqlSpatial\\SpatialServiceProvider',
    ),
  ),
  'intervention/image' => 
  array (
    'providers' => 
    array (
      0 => 'Intervention\\Image\\ImageServiceProvider',
    ),
    'aliases' => 
    array (
      'Image' => 'Intervention\\Image\\Facades\\Image',
    ),
  ),
  'kitloong/laravel-migrations-generator' => 
  array (
    'providers' => 
    array (
      0 => 'KitLoong\\MigrationsGenerator\\MigrationsGeneratorServiceProvider',
    ),
  ),
  'laravel/sanctum' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Sanctum\\SanctumServiceProvider',
    ),
  ),
  'laravel/scout' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Scout\\ScoutServiceProvider',
    ),
  ),
  'laravel/tinker' => 
  array (
    'providers' => 
    array (
      0 => 'Laravel\\Tinker\\TinkerServiceProvider',
    ),
  ),
  'meilisearch/meilisearch-laravel-scout' => 
  array (
    'providers' => 
    array (
      0 => 'Meilisearch\\Scout\\MeilisearchServiceProvider',
    ),
  ),
  'nesbot/carbon' => 
  array (
    'providers' => 
    array (
      0 => 'Carbon\\Laravel\\ServiceProvider',
    ),
  ),
  'nunomaduro/collision' => 
  array (
    'providers' => 
    array (
      0 => 'NunoMaduro\\Collision\\Adapters\\Laravel\\CollisionServiceProvider',
    ),
  ),
  'santigarcor/laratrust' => 
  array (
    'providers' => 
    array (
      0 => 'Laratrust\\LaratrustServiceProvider',
    ),
    'aliases' => 
    array (
      'Laratrust' => 'Laratrust\\LaratrustFacade',
    ),
  ),
  'tymon/jwt-auth' => 
  array (
    'aliases' => 
    array (
      'JWTAuth' => 'Tymon\\JWTAuth\\Facades\\JWTAuth',
      'JWTFactory' => 'Tymon\\JWTAuth\\Facades\\JWTFactory',
    ),
    'providers' => 
    array (
      0 => 'Tymon\\JWTAuth\\Providers\\LaravelServiceProvider',
    ),
  ),
);