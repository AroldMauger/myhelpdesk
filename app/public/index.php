<?php
require_once "../vendor/autoload.php";



$router = new AltoRouter();

$router->map( 'GET', '/', \App\Controller\HomeController::class . "#test", 'home' );


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
