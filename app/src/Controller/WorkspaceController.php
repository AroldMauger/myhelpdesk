<?php

namespace App\Controller;

use App\Service\AnythingLLMService;
use App\Service\SessionService;

class WorkspaceController extends AbstractController
{
    public function displayWorkspaces(): void {

        $service = new AnythingLLMService();

        $workspaces = $service->getWorkspaces();

        echo $this->render('workspaces/workspaces.html.twig', [
            'workspaces' => $workspaces,
            'success_message' => $this->sessionService->getMessage('success_message'),
            'error_message' => $this->sessionService->getMessage('error_message'),
        ]);
    }

    public function createWorkspace(array $workspaceTitle): void {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $workspaceTitle = $_POST['title'];

            $service = new AnythingLLMService();

            $response = $service->createWorkspace($workspaceTitle);

            if ($response === false) {
                $this->sessionService->setMessage('error_message', 'Une erreur est survenue lors de la création du workspace.');
                $this->displayWorkspaces();
                return;
            }
            $this->sessionService->setMessage('success_message','Espace de travail créé avec succès !');

            $this->displayWorkspaces();
        }
    }


    public function delete(array $workspaceSlug):void
    {
        if (is_array($workspaceSlug) && isset($workspaceSlug['slug'])) {
            $workspaceSlug = $workspaceSlug['slug'];
        }

        $service = new AnythingLLMService();
        $response = $service->deleteWorkspace($workspaceSlug);

        if ($response === false) {
            $this->sessionService->setMessage('error_message', "Erreur lors de la suppression de l'espace de travail.");
        } else {
            $this->sessionService->setMessage('success_message',"Espace de travail supprimé avec succès.");
        }
        $this->displayWorkspaces();

    }

    public function deleteDocument($params):void {

        $service = new AnythingLLMService();
        $httpCode = $service->deleteDocument($params);

        if ($httpCode === 200) {
            $this->sessionService->setMessage('success_message', 'Document supprimé avec succès.');
        } else {
            $this->sessionService->setMessage('error_message', 'Erreur lors de la suppression du document.');
        }

        $this->displayWorkspaces();
    }

    public function upload(array $workspaceSlug):void
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

        $this->sessionService->setMessage('success_message', 'Fichier téléchargé avec succès.');
        $this->displayWorkspaces();
    }
}
