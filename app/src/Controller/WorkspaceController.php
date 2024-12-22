<?php

namespace App\Controller;

class WorkspaceController extends AbstractController
{
    public function displayWorkspaces() {

        $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;

        unset($_SESSION['success_message']);
        unset($_SESSION['error_message']);

        $workspaces = $this->getWorkspaces();

        echo $this->render('workspaces/workspaces.html.twig', [
            'workspaces' => $workspaces['workspaces'] ?? [],
            'success_message' => $success_message,
            'error_message' => $error_message,
        ]);
    }


    public function createWorkspace($workspaceTitle) {
        $successMessage = null;
        $errorMessage = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $workspaceTitle = $_POST['title'];
            $apiUrl = 'http://172.27.144.1:3001/api/v1/workspace/new';
            $queryRefusalResponse = 'Je suis désolé, je ne peux vous aider concernant cette recherche. Veuillez ouvrir un ticket GLPI en vous adressant au service support informatique.';
            $accessToken = getenv('JWT_SECRET');

            // Structure de la requête pour l'API AnythingLLM
            $data = [
                'name' => $workspaceTitle,
                "similarityThreshold" => 0.7,
                "openAiTemp"=> 0.7,
                "openAiHistory"=> 20,
                "openAiPrompt"=> "Custom prompt for responses",
                "queryRefusalResponse"=> $queryRefusalResponse,
                "chatMode"=> "chat",
                "topN"=> 4
            ];
            // Requête CURL
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Exécution de la requête
            $response = curl_exec($ch);

            curl_close($ch);

            // Vérification du code de statut HTTP
            if ($response === false) {
                $errorMessage = 'Une erreur est survenue lors de la création du workspace.';
            } else {
                $successMessage = 'Espace de travail créé avec succès !';
            }
        }
        $workspaces = $this->getWorkspaces();

        echo $this->render('workspaces/workspaces.html.twig', [
            'success_message' => $successMessage,
            'error_message' => $errorMessage,
            'workspaces' => $workspaces['workspaces'] ?? [],
        ]);
    }

    public function getWorkspaces() {
        $apiUrl = 'http://172.27.144.1:3001/api/v1/workspaces';
        $accessToken = getenv('JWT_SECRET');

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $accessToken,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response !== false) {
            $workspaces = json_decode($response, true);
            return $workspaces;
        }

        return null;
    }
    public function delete($workspaceSlug)
    {
        //  $workspaceSlug est un tableau
        if (is_array($workspaceSlug) && isset($workspaceSlug['slug'])) {
            $workspaceSlug = $workspaceSlug['slug'];
        } else {
            return $this->render('workspaces/confirmation.html.twig', [
                'error_message' => 'Slug non valide ou absent.',
                'workspaces' => $this->getWorkspaces(),
            ]);
        }

        $apiUrl = 'http://172.27.144.1:3001/api/v1/workspace/' . $workspaceSlug;
        $accessToken = getenv('JWT_SECRET');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: */*',
            'Authorization: Bearer ' . $accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $response = curl_exec($ch);

        curl_close($ch);

        if ($response === false || empty($response)) {
            $error_message = "Erreur lors de la suppression de l'espace de travail.";
            $_SESSION['error_message'] = $error_message;
            return $this->displayWorkspaces();
        } else {
            $success_message = "Espace de travail supprimé avec succès.";
            $_SESSION['success_message'] = $success_message;
            return $this->displayWorkspaces();

        }
    }
}