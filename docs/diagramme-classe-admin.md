# Diagramme de Classes - Interface Admin

## Architecture de l'Application

```mermaid
classDiagram
    %% ============ MODÈLES ============
    
    class User {
        +int id
        +string name
        +string email
        +string password
        +string role
        +boolean active
        +timestamp email_verified_at
        +timestamp created_at
        +timestamp updated_at
        --
        +createdMissions() Mission[]
        +assignedAffectations() Affectation[]
        +updatedReferences() ReferencePoint[]
    }

    class Technicien {
        +int id
        +int user_id
        +string nom
        +string prenom
        +string telephone
        +string zone_intervention
        +string competences
        +boolean disponible
        +timestamp created_at
        +timestamp updated_at
        --
        +user() User
        +affectations() Affectation[]
    }

    class Mission {
        +int id
        +int reference_id
        +string type_mission
        +string priorite
        +text description
        +string statut
        +int created_by
        +datetime due_at
        +datetime started_at
        +datetime completed_at
        +timestamp created_at
        +timestamp updated_at
        --
        +referencePoint() ReferencePoint
        +creator() User
        +affectations() Affectation[]
        +currentAffectation() Affectation
    }

    class Affectation {
        +int id
        +int mission_id
        +int technicien_id
        +int assigned_by
        +datetime assigned_at
        +text rapport
        +timestamp created_at
        +timestamp updated_at
        --
        +mission() Mission
        +technicien() Technicien
        +assignedBy() User
    }

    class ReferencePoint {
        +int id
        +string reference
        +decimal latitude
        +decimal longitude
        +string adresse
        +string gouvernorat
        +string delegation
        +decimal precision_m
        +string statut
        +int updated_by
        +timestamp deleted_at
        +timestamp created_at
        +timestamp updated_at
        --
        +missions() Mission[]
        +updatedBy() User
    }

    %% ============ CONTRÔLEURS ============
    
    class DashboardController {
        +index(Request request) View
        --
        Gère le tableau de bord admin
        Affiche les statistiques
        Liste missions non affectées
    }

    class TechnicienController {
        +index() View
        +create() View
        +store(Request) RedirectResponse
        +edit(Technicien) View
        +update(Request, Technicien) RedirectResponse
        +destroy(Technicien) RedirectResponse
        --
        Gestion CRUD des techniciens
    }

    class MissionAssignmentController {
        +assign(Request, Mission) RedirectResponse
        --
        Affectation des missions
        aux techniciens
    }

    %% ============ RELATIONS ENTRE MODÈLES ============
    
    User "1" -- "0..*" Technicien : a un profil >
    User "1" -- "0..*" Mission : crée >
    User "1" -- "0..*" Affectation : assigne >
    User "1" -- "0..*" ReferencePoint : met à jour >
    
    Technicien "1" -- "0..*" Affectation : reçoit >
    
    Mission "1" -- "0..*" Affectation : a >
    Mission "0..*" -- "1" ReferencePoint : se réfère à >
    
    ReferencePoint "1" -- "0..*" Mission : génère >

    %% ============ RELATIONS CONTRÔLEURS-MODÈLES ============
    
    DashboardController ..> User : utilise
    DashboardController ..> Technicien : utilise
    DashboardController ..> Mission : utilise
    DashboardController ..> Affectation : utilise
    
    TechnicienController ..> Technicien : gère
    TechnicienController ..> User : utilise
    
    MissionAssignmentController ..> Mission : utilise
    MissionAssignmentController ..> Technicien : utilise
    MissionAssignmentController ..> Affectation : crée

    %% ============ NOTES ============
    
    note for User "Rôles: admin, technicien, user
    Authentification Laravel Breeze"
    
    note for Mission "Statuts: en_attente, en_cours,
    terminée, annulée
    Priorités: haute, normale, basse"
    
    note for ReferencePoint "SoftDeletes activé
    Stocke les coordonnées GPS
    et informations géographiques"
```

## Description des Entités

### 1. User (Utilisateur)
- **Rôle** : Gestion de l'authentification et des autorisations
- **Types** : admin, technicien, user
- **Relations** :
  - Crée des missions
  - Peut être associé à un profil technicien
  - Assigne des missions via affectations

### 2. Technicien
- **Rôle** : Représente un technicien de terrain
- **Attributs clés** : zone_intervention, competences, disponible
- **Relations** :
  - Lié à un utilisateur (User)
  - Reçoit des affectations de missions

### 3. Mission
- **Rôle** : Tâche à accomplir sur un point de référence
- **Statuts** : en_attente, en_cours, terminée, annulée
- **Priorités** : haute, normale, basse
- **Relations** :
  - Créée par un User
  - Référence un ReferencePoint
  - A des affectations

### 4. Affectation
- **Rôle** : Lie une mission à un technicien
- **Fonction** : Trace qui a affecté quelle mission à quel technicien et quand
- **Relations** :
  - Appartient à une Mission
  - Appartient à un Technicien
  - Créée par un User (assigned_by)

### 5. ReferencePoint
- **Rôle** : Point de référence géographique (infrastructure STEG)
- **Données géographiques** : latitude, longitude, précision
- **SoftDeletes** : Les suppressions sont logiques
- **Relations** :
  - Génère des missions
  - Mise à jour par un User

## Contrôleurs Admin

### DashboardController
- Affiche les statistiques (nombre d'utilisateurs, techniciens, missions)
- Liste les missions non affectées
- Permet l'affectation rapide des missions

### TechnicienController
- CRUD complet pour la gestion des techniciens
- Création/édition/suppression de profils techniciens

### MissionAssignmentController
- Gère l'affectation des missions aux techniciens
- Crée les enregistrements d'affectation

## Flux de Travail Admin

1. **Création de mission** → Un admin crée une mission liée à un ReferencePoint
2. **Affectation** → L'admin assigne la mission à un technicien disponible via le dashboard
3. **Suivi** → Le dashboard affiche les statistiques et missions non affectées
4. **Gestion techniciens** → CRUD complet via TechnicienController

## Autorisations

- **Gate** : `access-admin` contrôle l'accès au dashboard admin
- **Policy** : `manage-missions` contrôle la création de missions
- Basé sur le champ `role` de la table `users`
