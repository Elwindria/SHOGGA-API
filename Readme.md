# SHOGGA API

## Présentation

SHOGGA API est un service développé avec Symfony dans le cadre de mon stage de Licence 3 chez SHOGGA.

Il permet de collecter des leads (emails) lors d’événements (salons, stands, tablettes, jeux concours) et de les transmettre vers les outils CRM et marketing de l'entreprise.

L'application agit comme un hub central entre :

- Une interface de collecte (frontend)
- Les services tiers (Sellsy, Klaviyo, etc.)
- Un dashboard d'administration permettant de superviser l'application

Le projet dispose également d'un dashboard sécurisé permettant la consultation des logs, le suivi des traitements automatiques et la supervision technique de l'API.

---

## Objectifs

Cette application a pour but de :

- Collecter des adresses email via une interface simple
- Assurer la conformité RGPD
- Centraliser et structurer les données collectées
- Intégrer automatiquement les leads dans les outils métiers
- Protéger les endpoints contre les abus
- Fournir un dashboard de supervision technique
- Permettre l'évolution future vers de nouveaux besoins métier

---

## Architecture

Le projet est composé de deux parties principales.

### API

Fonctionnalités :

- Réception des données via API REST
- Validation et normalisation des données
- Traitement métier
- Gestion RGPD
- Intégration avec les services externes
- Protection anti-abus
- Maintenance automatisée

### Dashboard d'administration

Le dashboard est réservé aux administrateurs.

Fonctionnalités :

- Authentification sécurisée
- Consultation des logs de production
- Recherche dans les logs
- Filtrage des logs
- Pagination
- Navigation entre les fichiers de logs journaliers
- Consultation des traitements automatiques
- Statistiques des logs
- Santé système
- Suivi du Rate Limiter

Le dashboard a été conçu pour être évolutif et accueillir de futures fonctionnalités métier.

---

## Cas d'utilisation

### Collecte d'un lead

1. Un utilisateur saisit son email sur une tablette
2. Il accepte les conditions RGPD
3. Le frontend envoie les données à l'API
4. L'API :
   - Valide les données
   - Vérifie les règles métier
   - Vérifie les protections anti-spam
   - Transmet les informations aux services externes
5. Les traitements automatisés assurent le nettoyage quotidien
6. Les administrateurs peuvent superviser le fonctionnement via le dashboard

---

## Fonctionnalités

### API

- Endpoint de collecte d'emails
- Validation et normalisation des données
- Gestion du consentement RGPD
- Protection contre les abus
- Protection contre les emails dupliqués
- Protection via Symfony Rate Limiter
- Stockage temporaire des données métier
- Intégration Sellsy
- Intégration Klaviyo
- Journalisation centralisée

### Dashboard

- Authentification administrateur
- Consultation des logs
- Recherche dans les logs
- Filtrage par niveau
- Filtrage par catégorie
- Pagination
- Navigation entre les fichiers de logs Monolog
- Statistiques de logs
- Suivi des protections Rate Limiter
- Santé système

### Maintenance

- Commandes Symfony dédiées
- Maintenance automatisée via cron
- Nettoyage RGPD automatique
- Nettoyage des données temporaires
- Architecture centralisée de maintenance

---

## Dashboard d'administration

Le dashboard est développé avec Symfony et Twig.

### Vue d'ensemble

Le dashboard permet aux administrateurs de superviser rapidement l'état de l'application.

Widgets actuellement disponibles :

- Statistiques des logs
- État de santé du système
- Suivi du Rate Limiter
- Consultation des logs de production
- Historique des traitements de maintenance

### Fonctionnalités actuelles

- Authentification administrateur
- Consultation des logs de production
- Navigation entre les fichiers de logs journaliers
- Recherche et filtrage des événements
- Statistiques des logs
- Santé système
- Suivi du Rate Limiter
- Consultation des traitements de maintenance

### Technologies utilisées

- Symfony Security
- Twig
- SQLite
- Monolog
- Symfony Rate Limiter

### Philosophie

Le dashboard est conçu comme une plateforme de supervision évolutive.

Aujourd'hui, il permet principalement :

- La surveillance technique
- La consultation des logs
- Le suivi des protections applicatives
- Le suivi des tâches automatisées

À terme, il pourra accueillir :

- Monitoring applicatif
- Tableaux de bord métier
- Statistiques avancées
- Outils d'administration
- Gestion des utilisateurs
- Outils de diagnostic

---

## Conformité RGPD

Ce service a été conçu pour respecter les obligations réglementaires.

Principes appliqués :

- Consentement explicite obligatoire
- Traçabilité du consentement
- Finalité clairement définie
- Conservation limitée des données
- Nettoyage automatisé des données expirées

Évolutions possibles :

- Export des données personnelles
- Droit à l'effacement
- Gestion avancée des demandes RGPD

---

## Protection contre les abus

L'application intègre plusieurs niveaux de protection.

### Protection email

Empêche l'utilisation répétée d'une même adresse email sur certaines fonctionnalités métier.

### Protection IP

Protection via Symfony Rate Limiter :

- Limitation du nombre de requêtes par IP
- Détection des abus
- Journalisation des blocages

### Supervision

Les blocages sont consultables directement depuis le dashboard d'administration.

---

## Maintenance automatisée

Une commande Symfony centralisée exécute les tâches de maintenance quotidiennes.

Exemple :

```bash
php bin/console app:daily-maintenance
```

Cette commande est exécutée automatiquement via un cron serveur.

Exemples de tâches :

- Nettoyage RGPD
- Nettoyage des données temporaires
- Futures tâches métier

---

## Stack technique

- Backend : Symfony
- Frontend Admin : Twig
- API : REST
- Base de données : SQLite
- Logs : Monolog
- Authentification : Symfony Security
- Protection API : Symfony Rate Limiter
- Intégrations : Sellsy, Klaviyo

---

## Structure du projet

```text
src/
├── Admin/
│   ├── Controller/
│   ├── Dashboard/
│   │   ├── DTO/
│   │   └── Service/
│   └── Log/
│       ├── DTO/
│       └── Service/
│
├── GameContest/
│   ├── Controller/
│   ├── Entity/
│   ├── Repository/
│   └── Service/
│
├── Security/
│   ├── Controller/
│   ├── Entity/
│   └── Repository/
│
├── Command/
│   └── DailyMaintenanceCommand.php
│
└── Sellsy/
```

---

## Évolutions possibles

- Monitoring applicatif avancé
- Statistiques métier avancées
- Tableau de bord métier
- Alertes automatiques
- Gestion des utilisateurs administrateurs
- Reporting
- Nouvelles intégrations métier
- Système de notifications
- Historique des maintenances

---

## Nom du projet

Nom interne : **SHOGGA API**

Usage :

- Collecte de leads
- Jeux concours
- Salons et événements
- Intégrations CRM et marketing
- Outils internes SHOGGA

---

## Auteur

**Pierre Lopez**
Alias : **Elwindria**