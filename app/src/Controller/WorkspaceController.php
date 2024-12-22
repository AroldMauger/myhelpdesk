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

        // Décoder les métadonnées JSON pour chaque document
        foreach ($workspaces as &$workspace) {
            if (isset($workspace['documents']) && is_array($workspace['documents'])) {
                foreach ($workspace['documents'] as &$document) {
                    if (isset($document['metadata'])) {
                        $document['metadata'] = json_decode($document['metadata'], true);
                    }
                }
            }
        }

        echo $this->render('workspaces/workspaces.html.twig', [
            'workspaces' => $workspaces,
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
            $workspacesData = json_decode($response, true);
            $workspaces = $workspacesData['workspaces'] ?? [];

            // Récupérer les documents pour chaque workspace
            foreach ($workspaces as &$workspace) {
                $workspace['documents'] = $this->getWorkspaceDocuments($workspace['slug']);
            }

            return $workspaces;
        }
        return [];
    }


    private function getWorkspaceDocuments($slug) {
        $apiUrl = "http://172.27.144.1:3001/api/v1/workspace/{$slug}";
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
            $workspaceData = json_decode($response, true);
            if (isset($workspaceData['workspace'][0]['documents'])) {
                return $workspaceData['workspace'][0]['documents'];
            }
        }

        return [];
    }



    public function delete($workspaceSlug)
    {
        //  $workspaceSlug est un tableau
        if (is_array($workspaceSlug) && isset($workspaceSlug['slug'])) {
            $workspaceSlug = $workspaceSlug['slug'];
        } else {
            $error_message = "Slug non valide ou absent.";
            $_SESSION['error_message'] = $error_message;
            return $this->displayWorkspaces();
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

    public function upload($workspaceSlug)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {

            $file = $_FILES['document'];
            $apiUrl = 'http://172.27.144.1:3001/api/v1/document/upload';
            $accessToken = getenv('JWT_SECRET');

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $accessToken,
                'Accept: application/json',
            ]);


            $postFields = [
                'file' => new \CURLFile($file['tmp_name'], $file['type'], $file['name'])
            ];
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

            $response = curl_exec($ch);

            // Vérifier si la requête a échoué
            if (curl_errno($ch)) {
                $error_message = 'Erreur cURL: ' . curl_error($ch);
                curl_close($ch);
                return $this->render('workspaces/workspaces.html.twig', [
                    'error_message' => $error_message,
                ]);
            }
            curl_close($ch);
            $data = json_decode($response, true);

            if ($response === false || empty($response)) {
                $error_message = "Erreur lors du téléchargement du fichier.";
                $_SESSION['error_message'] = $error_message;
                return $this->displayWorkspaces();
            } else {
                $documentLocation = $data['documents'][0]['location'];
                $this->updateWorkspace($workspaceSlug, $documentLocation);
                $success_message = "Fichier téléchargé avec succès.";
                $_SESSION['success_message'] = $success_message;
                return $this->displayWorkspaces();
            }
        }
    }

    public function updateWorkspace($workspaceSlug, $documentLocation)
    {
        if (is_array($workspaceSlug) && isset($workspaceSlug['slug'])) {
            $workspaceSlug = $workspaceSlug['slug'];
        } else {
            $error_message = "Slug non valide ou absent.";
            $_SESSION['error_message'] = $error_message;
            return $this->displayWorkspaces();
        }
        $updateApiUrl = "http://172.27.144.1:3001/api/v1/workspace/$workspaceSlug/update-embeddings";
        $accessToken = getenv('JWT_SECRET');

        $updatePostData = json_encode([
            'adds' => [
                $documentLocation,
            ]
        ]);

        $updateCh = curl_init($updateApiUrl);
        curl_setopt($updateCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($updateCh, CURLOPT_POST, true);
        curl_setopt($updateCh, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($updateCh, CURLOPT_POSTFIELDS, $updatePostData);

        $updateResponse = curl_exec($updateCh);
        $updateHttpCode = curl_getinfo($updateCh, CURLINFO_HTTP_CODE);
        curl_close($updateCh);

        $updateData = json_decode($updateResponse, true);

        return $updateHttpCode === 200 && isset($updateData['success']) && $updateData['success'];
    }


}