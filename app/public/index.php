<?php
require_once "../vendor/autoload.php";



$router = new AltoRouter();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
    if ($method === 'DELETE') {
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
    }
}


//Route dashboard for users
$router->map( 'GET', '/home', \App\Controller\HomeController::class . "#home", 'home' );

//Route dashboard for admin
$router->map( 'GET', '/admin', \App\Controller\AdminController::class . "#home", 'admin' );
$router->map( 'POST', '/create-workspace', \App\Controller\WorkspaceController::class . "#createWorkspace", 'create_workspace' );

//Route workspaces
$router->map( 'GET', '/workspaces', \App\Controller\WorkspaceController::class . "#displayWorkspaces", 'workspaces' );
$router->map( 'DELETE', '/delete/[:slug]', \App\Controller\WorkspaceController::class . "#delete", 'delete' );
$router->map( 'POST', '/upload-document/[:slug]', \App\Controller\WorkspaceController::class . "#upload", 'upload' );
$router->map('DELETE', '/delete-document/[*:docpath]', \App\Controller\WorkspaceController::class . "#deleteDocument", 'delete_document');

//Routes authentication
$router->map( 'GET', '/login', \App\Controller\LoginController::class . "#displayLogin", 'login-form' );
$router->map( 'GET', '/signup', \App\Controller\LoginController::class . "#displaySignup", 'signup-form' );
$router->map( 'POST', '/signup', \App\Controller\LoginController::class . "#signup", 'signup' );
$router->map( 'POST', '/login', \App\Controller\LoginController::class . "#login", 'login' );
$router->map( 'GET', '/logout', \App\Service\SessionService::class . "#logout", 'logout' );

//Routes chatbot
$router->map('POST', '/start-conversation', \App\Controller\ConversationController::class . '#startConversation', 'start_conversation');
$router->map('POST', '/add-message', \App\Controller\ConversationController::class . '#addMessage', 'add_message');
$router->map('POST', '/end-conversation', \App\Controller\ConversationController::class . '#endConversation', 'end_conversation');
$router->map('GET', '/conversation', \App\Controller\ConversationController::class . "#viewConversation", 'view_conversation');
$router->map('GET', '/previous', \App\Controller\HomeController::class . "#previousConversations", 'previous_conversations');
$router->map('POST', '/upload-file', \App\Controller\ConversationController::class . "#uploadFile", 'upload_file');



$match = $router->match();
if ($match) {
    list( $controller, $action ) = explode( '#', $match['target'] );

    if ( is_callable(array(new $controller, $action)) ) {
        call_user_func_array(array(new $controller, $action), array($match['params']));
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        echo '404 Not Found';
    }
} else {
    header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
    echo '404 Not Found';
}
