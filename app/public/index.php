<?php
require_once "../vendor/autoload.php";



$router = new AltoRouter();

$router->map( 'GET', '/', \App\Controller\HomeController::class . "#home", 'home' );

$router->map( 'GET', '/login', \App\Controller\LoginController::class . "#displayLogin", 'login-form' );
$router->map( 'GET', '/signup', \App\Controller\LoginController::class . "#displaySignup", 'signup-form' );
$router->map( 'POST', '/signup', \App\Controller\LoginController::class . "#signup", 'signup' );


$match = $router->match();
if ($match) {
    list( $controller, $action ) = explode( '#', $match['target'] );

    if ( is_callable(array(new $controller, $action)) ) {
        call_user_func_array(array(new $controller, $action), array($match['params']));
    } else {
        // Aucune route trouvée
        header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        echo '404 Not Found';
    }
} else {
    // Aucune route trouvée
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo '404 Not Found';
}
