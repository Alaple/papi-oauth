<?php
use Slim\Views\Twig;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';
$data = json_encode('/data.json');

$app = AppFactory::create();
$client = new GuzzleHttp\Client();
$twig = Twig::create('views/pages', ['cache' => false]);

$app->addRoutingMiddleware();
$app->add(TwigMiddleware::create($app, $twig));
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$access_token = '';
$clientID = 'f99b9221a8bd0082c463';
$clientSecret = 'c3e63b184223e18cc6cc3fe78e0fa0cbb16e3a01';

$app->get('/', function ($request, $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'index.html', [
        'clientid' => $GLOBALS['clientID']
    ]);
});

$app->get('/github/callback', function ($request, $response, $args) {    
    // The req.query object has the query params that were sent to this route.
    $requestToken = $request->query->code;
    $clientID = $GLOBALS['clientID'];
    $clientSecret = $GLOBALS['clientSecret'];

    echo $clientID;
    echo $clientSecret;
    echo $requestToken;

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

    $GLOBALS['access_token'] = $result->data->access_token;
    return $response->withRedirect('/success');
});

$app->get('/success', function ($request, $response, $args) {
    $access_token = $GLOBALS['access_token'];

    echo $access_token;

    $userData = $GLOBALS['client']->request(
        'GET',
        'https://api.github.com/user',
        [
            'headers' => [
                'Authorization' => "token $access_token"
            ]
        ]
    );

    $result = $GLOBALS['client']->request(
        'POST',
        'https://api.github.com/user',
        [
            'headers' => [
                'Authorization' => "token $access_token"
            ],
            'body' => $GLOBAL['data']
        ]
    );

    echo "Response: $result";
    return $view->render($response, 'success.html', [
        'userData' => $userData
    ]);
});

$app->run();