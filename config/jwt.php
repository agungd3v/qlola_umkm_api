<?php

return [
	'secret' => env('JWT_SECRET'),
	'keys' => [
		'public' => env('JWT_PUBLIC_KEY'),
		'private' => env('JWT_PRIVATE_KEY'),
		'passphrase' => env('JWT_PASSPHRASE'),
	],
	// 'ttl' => env('JWT_TTL', 60),
	// 'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
	'ttl' => env('JWT_TTL', null),
	'refresh_ttl' => env('JWT_REFRESH_TTL', null),
	'algo' => env('JWT_ALGO', 'HS256'),
	'required_claims' => [
		'iss',
		'iat',
		// 'exp',
		'nbf',
		'sub',
		'jti'
	],
	'persistent_claims' => [],
	'lock_subject' => true,
	'leeway' => env('JWT_LEEWAY', 0),
	'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
	'blacklist_grace_period' => env('JWT_BLACKLIST_GRACE_PERIOD', 0),
	'show_black_list_exception' => env('JWT_SHOW_BLACKLIST_EXCEPTION', true),
	'providers' => [
		'jwt' => PHPOpenSourceSaver\JWTAuth\Providers\JWT\Lcobucci::class,
		'auth' => PHPOpenSourceSaver\JWTAuth\Providers\Auth\Illuminate::class,
		'storage' => PHPOpenSourceSaver\JWTAuth\Providers\Storage\Illuminate::class,
	],
];
