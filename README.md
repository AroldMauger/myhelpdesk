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

### Configuration des Variables d'Environnement

Avant de lancer l'application, assurez-vous de configurer les variables d'environnement suivantes :

- `MISTRAL_API_KEY`: Clé API générée depuis le site officiel de Mistral avec un compte gratuit.
- `JWT_SECRET`: Clé API fournie par AnythingLLM lors de la création d'un compte.
- `LLM_BACKEND_URL`: URL locale de Ollama, par exemple `http://localhost:11434`.

### Lancement de l'Application

1. **Cloner le dépôt**

   ```bash
   git clone https://github.com/AroldMauger/myhelpdesk.git
   cd myhelpdesk
   
## Lancer les services Docker

```bash
docker-compose up -d
```


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


## Composer

Le fichier `composer.json` définit les dépendances PHP nécessaires pour l'application :

```json
{
    "require": {
        "altorouter/altorouter": "^2.0",
        "twig/twig": "^3.16",
        "ext-json": "*",
        "ext-pdo": "*",
        "ext-curl": "*",
        "ext-fileinfo": "*"
    }
}
```