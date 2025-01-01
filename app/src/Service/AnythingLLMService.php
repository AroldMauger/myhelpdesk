<?php

namespace App\Service;

class AnythingLLMService
{
    private $apiBaseUrl;
    private $accessToken;

    public function __construct()
    {

        $this->apiBaseUrl = 'http://172.27.144.1:3001/api/v1/';
        $this->accessToken = getenv('JWT_SECRET');
    }

    public function createWorkspace(string $workspaceTitle) {

        $queryRefusalResponse = 'Je suis désolé, je ne peux vous aider concernant cette recherche. Veuillez ouvrir un ticket GLPI en vous adressant au service support informatique.';

        $data = [
            'name' => $workspaceTitle,
            "similarityThreshold" => 0.7,
            "openAiTemp"=> 0.7,
            "openAiHistory"=> 20,
            "openAiPrompt"=> "Custom prompt for responses",
            "queryRefusalResponse"=> $queryRefusalResponse,
            "chatMode"=> "query",
            "topN"=> 4
        ];
        $ch = curl_init($this->apiBaseUrl . 'workspace/new');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public function getWorkspaces() {

        $ch = curl_init($this->apiBaseUrl . 'workspaces');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function upload($file) {

        $ch = curl_init($this->apiBaseUrl . 'document/upload');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Accept: application/json',
        ]);

        $postFields = [
            'file' => new \CURLFile($file['tmp_name'], $file['type'], $file['name'])
        ];
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public function updateWorkspace($workspaceSlug, $documentLocation)
    {

        $updateApiUrl = "http://172.27.144.1:3001/api/v1/workspace/$workspaceSlug/update-embeddings";
        $accessToken = getenv('JWT_SECRET');

        $updatePostData = json_encode([
            'adds' => [
                $documentLocation,
            ]
        ]);

        $updateCh = curl_init($this->apiBaseUrl . "workspace/$workspaceSlug/update-embeddings");
        curl_setopt($updateCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($updateCh, CURLOPT_POST, true);
        curl_setopt($updateCh, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
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

    public function updatePin($workspaceSlug, $documentLocation)
    {

        $updatePostData = json_encode([
            'docPath' => $documentLocation,
            'pinStatus' => true
        ]);

        $updateCh = curl_init($this->apiBaseUrl . "workspace/$workspaceSlug/update-pin");
        curl_setopt($updateCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($updateCh, CURLOPT_POST, true);
        curl_setopt($updateCh, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
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

    public function deleteDocument($params) {

        $docpath = urldecode($params['docpath']);
        $deletePayload = json_encode([
            'names' => [$docpath]
        ]);

        $ch = curl_init($this->apiBaseUrl . 'system/remove-documents');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $deletePayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }

    public function deleteWorkspace($workspaceSlug) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . "workspace/$workspaceSlug");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: */*',
            'Authorization: Bearer ' . $this->accessToken
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public function getWorkspaceDocuments($slug) {

        $ch = curl_init($this->apiBaseUrl . "workspace/$slug");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->accessToken,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function askChatbot($userMessage, $workspaceSlug) {

            $postData = json_encode([
                'message' => $userMessage,
                'mode' => 'query'
            ]);

            $ch = curl_init($this->apiBaseUrl . "workspace/$workspaceSlug/chat");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $this->accessToken,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            if ($response !== false) {
                return json_decode($response, true);
            }

            return null;

    }
}
