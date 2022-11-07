<?php
use Slim\Views\Twig;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

session_start();
require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$client = new GuzzleHttp\Client();
$twig = Twig::create('views/pages', ['cache' => false]);

$app->addRoutingMiddleware();
$app->add(TwigMiddleware::create($app, $twig));
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$clientID = 'xxx';
$clientSecret = 'xxxxxxxxx';

$app->get('/', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'index.html', [
        'clientid' => $GLOBALS['clientID']
    ]);
});

$app->get('/github/callback', function ($request, $response, $args) {    
    // The req.query object has the query params that were sent to this route.
    $requestToken = explode("=",$request->getUri()->getQuery())[1];
    $clientID = $GLOBALS['clientID'];
    $clientSecret = $GLOBALS['clientSecret'];

    $url = "https://github.com/login/oauth/access_token?client_id=$clientID&client_secret=$clientSecret&code=$requestToken";
    
    $result = $GLOBALS['client']->request(
        'POST',
        $url,
        [
            'headers' => [
                'accept' => 'application/json'
            ]
        ]
    );

    $accessToken = json_decode($result->getBody())->access_token;
    $_SESSION['accessToken'] = $accessToken;
    return $response->withRedirect('/success');
});

$app->get('/success', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);
    $accessToken =  $_SESSION['accessToken'];

    $userData = $GLOBALS['client']->request(
        'GET',
        'https://api.github.com/user',
        [
            'headers' => [
                'Authorization' => "token $accessToken"
            ]
        ]
    );

    $result = $GLOBALS['client']->request(
        'POST',
        'https://privacyapi.brandyourself.com/v1/scans',
        [
            'headers' => [
                'Authorization' => "token $accessToken"
            ],
            'body' => json_encode('/data.json')
        ]
    );
    $jsonResult = json_decode($result->getBody());
    echo "Response: $jsonResult";

    return $view->render($response, 'success.html', [
        'userData' => json_decode($userData->getBody())
    ]);
});

$app->run();