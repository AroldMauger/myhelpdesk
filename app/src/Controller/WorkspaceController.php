<?php

namespace App\Controller;

use App\Service\AnythingLLMService;

class WorkspaceController extends AbstractController
{
    public function displayWorkspaces():void {
        $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
        $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;

        unset($_SESSION['success_message']);
        unset($_SESSION['error_message']);

        $workspaces = $this->getWorkspaces();

        echo $this->render('workspaces/workspaces.html.twig', [
            'workspaces' => $workspaces,
            'success_message' => $success_message,
            'error_message' => $error_message,
        ]);
    }

    public function createWorkspace($workspaceTitle) :void {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $workspaceTitle = $_POST['title'];

            $service = new AnythingLLMService();

            $response = $service->createWorkspace($workspaceTitle);

            if ($response === false ) {
                $errorMessage = 'Une erreur est survenue lors de la création du workspace.';
                $_SESSION['error_message'] = $errorMessage;
                $this->displayWorkspaces();
                return;
            }
            $successMessage = 'Espace de travail créé avec succès !';
            $_SESSION['success_message'] = $successMessage;
            $this->displayWorkspaces();
        }
    }

    public function getWorkspaces() {

        $service = new AnythingLLMService();
        $response = $service->getWorkspaces();

        if ($response !== false) {
            $workspacesData = json_decode($response, true);
            $workspaces = $workspacesData['workspaces'] ?? [];

            foreach ($workspaces as &$workspace) {
                $workspace['documents'] = $this->getWorkspaceDocuments($workspace['slug']);
            }

            return $workspaces;
        }
        return [];
    }


    private function getWorkspaceDocuments($slug) {

        $service = new AnythingLLMService();
        $response = $service->getWorkspaceDocuments($slug);

        if ($response !== false) {
            $workspaceData = json_decode($response, true);
            if (isset($workspaceData['workspace'][0]['documents'])) {
                return $workspaceData['workspace'][0]['documents'];
            }
        }
        return [];
    }

    public function delete($workspaceSlug)
    {
        if (is_array($workspaceSlug) && isset($workspaceSlug['slug'])) {
            $workspaceSlug = $workspaceSlug['slug'];
        }
        $service = new AnythingLLMService();
        $response = $service->deleteWorkspace($workspaceSlug);


        if ($response === false || empty($response)) {
            $error_message = "Erreur lors de la suppression de l'espace de travail.";
            $_SESSION['error_message'] = $error_message;
            $this->displayWorkspaces();
        } else {
            $success_message = "Espace de travail supprimé avec succès.";
            $_SESSION['success_message'] = $success_message;
            $this->displayWorkspaces();
        }
    }

    public function deleteDocument($params) {

        $service = new AnythingLLMService();
        $httpCode = $service->deleteDocument($params);


        if ($httpCode === 200) {
            $_SESSION['success_message'] = "Document supprimé avec succès.";
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression du document.";
        }

        $this->displayWorkspaces();

    }


    public function upload($workspaceSlug)
    {
        $file = $_FILES['document'];

        $service = new AnythingLLMService();
        $response = $service->upload($file);

        $data = json_decode($response, true);

        $documentLocation = $data['documents'][0]['location'];

        if (is_array($workspaceSlug) && isset($workspaceSlug['slug'])) {
            $workspaceSlug = $workspaceSlug['slug'];
        }

        $service->updateWorkspace($workspaceSlug, $documentLocation);
        $service->updatePin($workspaceSlug, $documentLocation);
        $success_message = "Fichier téléchargé avec succès.";
        $_SESSION['success_message'] = $success_message;
        $this->displayWorkspaces();
    }
}