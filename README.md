# MyHelpDesk

## Introduction

MyHelpDesk est une plateforme helpdesk basée sur l'intelligence artificielle, développée dans le cadre d'un projet d'étude en alternance pour le groupe Mary. Cette plateforme vise à fournir une assistance améliorée aux utilisateurs en intégrant un système d'authentification, un ChatBot alimenté par l'IA, et une interface utilisateur intuitive.

## Fonctionnalités

- **Système d'authentification utilisateur**
- **Interface utilisateur pour poser des questions au ChatBot**
- **Interaction avec l'IA (API Mistral IA) pour qualifier les demandes et proposer des solutions**
- **Ouverture de tickets dans GLPI si aucune réponse ne correspond**
- **Système de notation de la qualité des réponses**
- **Historique des demandes utilisateur**
- **Accès admin à l'ensemble des demandes et leurs notes**
- **Dashboard avec les catégories de demande**

## Rôles dans MyHelpDesk

| Rôle          | Créer un compte, s'authentifier | Poser une question au ChatBot, noter la conversation | Consulter l'historique de ses propres conversations | Consulter l'historique de toutes les conversations de l'app | Ajouter/supprimer un espace de travail et y uploader des documents | Supprimer ses propres conversations |
|---------------|----------------------------------|-------------------------------------------------------|------------------------------------------------------|-------------------------------------------------------|------------------------------------------------------------------|-------------------------------------|
| Utilisateur   | ✔️                              | ✔️                                                  | ✔️                                                 |                                                       |                                                                    |                                     |
| Administrateur | ✔️                              | ✔️                                                  | ✔️                                                 | ✔️                                                  | ✔️                                                                | ✔️                                 |

## Technologies Utilisées

- **Architecture MVC**: PHP, Twig, AltoRouter
- **Conteneurisation**: Docker
- **Base de données**: MySQL
- **Intelligence Artificielle**: AnythingLLM, Ollama, Mistral AI
- **Interactivité**: JavaScript

## Installation

### Prérequis

- Docker
- Docker Compose
- Une clé API Mistral (générée gratuite sur le site officiel https://mistral.ai/fr/)

### Configuration des Variables d'Environnement

Avant de lancer l'application, assurez-vous de configurer les variables d'environnement suivantes :

- `MISTRAL_API_KEY`: Clé API générée depuis le site officiel de Mistral avec un compte gratuit.
- `JWT_SECRET`: Clé API fournie par AnythingLLM lors de la création d'un compte (voir plus bas "Lancement de l'application 4.)
- `LLM_BACKEND_URL`: URL locale de Ollama, par exemple `http://localhost:11434`.

### Lancement de l'Application

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/AroldMauger/myhelpdesk.git
   cd myhelpdesk

2. **Lancer les services Docker**

   ```bash
   docker-compose up -d
   ```

3. **Base de données**
- Faites un import depuis phpMyAdmin de la base de données présentes dans ce repo github "myhelpdesk.sql"
- Les mots de passes sont écrits en commentaires aux lignes 69-70 du fichier pour que vous puissiez tester.

4. **Ouvrir AnythingLLM sur le port 3001**

- Lors de votre première connexion, il vous sera demandé de créer un utilisateur pour AnythingLLM.
- Suivez les étapes et créez votre compte utilisateur.
- Une fois que vous avez accès à l'interface de AnythingLLM, cliquez en bas à gauche sur le 4ème bouton avec la clé "Open settings"
- Un menu s'affiche alors à gauche. Allez dans Fournisseurs d'IA -> Préférence LLM. 
- Choisissez "MISTRAL" comme Fournisseur LLM. Collez votre Mistral API KEY généré depuis le site officiel de Mistral avec un compte gratuit.
- Choisissez "mistral-tiny" dans Chat Model Selection.
- Restez dans le menu à gauche et allez dans : Outils -> Clés API.
- Générez une nouvelle clé API. Ce sera la variable d'environnement "JWT_SECRET" que vous mettrez dans votre .env

5. **Possible erreur "Invalid Env"**

- Il se peut que lors des premières utilisations de l'applications avec AnythingLLM, une erreur indique "Invalid Env" lors de vos requêtes API. Pour régler ce problème, rendez-vous sur la doc de AnythingLLM à : http://localhost:3001/api/docs
- Vous devriez voir une documentation Swagger. Cliquez sur "Authorize" et collez votre JWT_SECRET généré auparavant dans AnythingLLM.
- Cherchez la route `/v1/system/update-env` dans la section System Settings. Cliquez sur "Try it out" puis "Execute". Cela mettra à jour les variables d'env sur AnythingLLM et l'erreur ne devrait plus se reproduire. 

6. **Possible erreur concernant "SQLite" au lancement de l'image AnythingLLM**
- La commande `sudo chmod -R 777 /chemin_vers_projet_myhelpdesk/storage` est généralement suffisante pour régler ce problème. 

## Docker Compose

Le fichier `docker-compose.yml` définit les services suivants :

- **phpfpm** : Service PHP-FPM pour l'exécution des scripts PHP.
- **nginx** : Serveur web Nginx pour servir l'application.
- **mysql** : Base de données MySQL.
- **phpmyadmin** : Interface web pour la gestion de la base de données MySQL.
- **anythingllm** : Service pour l'intelligence artificielle.
- **ollama** : Service pour l'exécution des modèles d'IA en local.

### Volumes

- **mysqldata** : Volume pour les données MySQL.
- **ollama_data** : Volume pour les données Ollama.
- **anythingllm_data** : Volume pour les données AnythingLLM.

