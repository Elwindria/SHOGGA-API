# Shogga - API

## Présentation

Ce projet est un service développé avec Symfony dans le cadre de mon stage chez SHOGGA (Licence 3eme année).

Il permet de collecter des leads (emails) lors d’événements (salons, stands, tablettes) et de les transmettre vers les outils CRM et marketing de SHOGGA.

Il agit comme un **hub central** entre une interface de collecte (frontend), des services tiers (Sellsy, Klaviyo, etc.) et un back-office interne permettant de superviser l'application.

Le projet dispose également d'un dashboard d'administration sécurisé permettant notamment la consultation des logs de production, le suivi des traitements automatiques et l'ajout futur d'outils métier.

---

## Objectif

Ce service a pour but de :

* Collecter des adresses email via une interface simple (tablette / kiosk)
* Assurer la conformité RGPD (gestion du consentement, traçabilité)
* Centraliser et structurer les données collectées
* Transmettre automatiquement les leads vers des outils externes (Sellsy, Klaviyo)
* Fournir un dashboard d'administration pour le suivi technique
* Permettre l'évolution de la plateforme vers d'autres besoins métier

---

## Architecture

Le projet est composé de deux parties principales :

### API

* Réception des données (formulaire, webhooks)
* Validation et traitement des leads
* Protection contre les abus et le spam
* Intégration avec des services externes
* Gestion des traitements métier

### Dashboard d'administration

Le projet embarque un dashboard d'administration sécurisé accessible aux administrateurs.

Fonctionnalités actuelles :

* Authentification administrateur
* Consultation des logs de production
* Recherche dans les logs
* Filtrage des logs
* Navigation entre les fichiers de logs quotidiens
* Suivi des traitements automatiques

Le dashboard a été conçu pour être évolutif et accueillir de nouvelles fonctionnalités métier.

---

## Cas d’usage

Flux typique :

1. Un utilisateur saisit son email sur une tablette lors d’un salon
2. Il donne son consentement explicite pour recevoir des communications marketing
3. Le frontend envoie les données à l’API Symfony
4. L’API :

   * Valide et nettoie les données
   * Vérifie les règles métier
   * Empêche certains abus (emails temporaires déjà utilisés)
   * Transmet les données aux outils CRM / marketing
5. Les traitements automatiques assurent la maintenance quotidienne
6. Les administrateurs peuvent consulter les logs via le dashboard

---

## Fonctionnalités

### API

* Endpoint de collecte d’emails
* Validation et normalisation des données
* Gestion du consentement RGPD (opt-in explicite)
* Protection contre certains abus et tentatives de spam
* Stockage temporaire d’informations métier
* Intégration avec des services tiers (Sellsy, Klaviyo)

### Dashboard

* Authentification administrateur
* Consultation des logs de production
* Recherche dans les logs
* Filtrage des logs
* Pagination
* Navigation entre les fichiers de logs journaliers

### Maintenance

* Commandes Symfony dédiées
* Maintenance quotidienne automatisée via cron
* Nettoyage RGPD automatisé
* Nettoyage des données temporaires

---

## Dashboard d'administration

Le dashboard est développé avec Symfony et Twig.

### Fonctionnalités actuelles

* Authentification administrateur
* Consultation des logs de production
* Navigation entre les fichiers de logs quotidiens
* Recherche et filtrage des événements
* Consultation des traitements de maintenance

### Technologies utilisées

* Symfony Security
* Twig
* SQLite
* Monolog

### Philosophie

Le dashboard est conçu comme une plateforme d'administration évolutive.

Aujourd'hui, la première fonctionnalité disponible est la consultation des logs de production.

À terme, il pourra accueillir :

* Monitoring applicatif
* Statistiques métier
* Outils d'administration
* Gestion des utilisateurs
* Tableaux de bord personnalisés
* Outils de support et diagnostic

---

## Conformité RGPD

Ce service est conçu pour respecter les obligations légales :

* Consentement explicite requis avant toute collecte
* Preuve du consentement enregistrée
* Finalité clairement définie
* Durée de conservation maîtrisée
* Nettoyage automatisé des données expirées

Possibilités d'évolution :

* Export des données
* Droit à l'effacement
* Gestion avancée des demandes RGPD

---

## Maintenance automatisée

Une commande Symfony centralisée permet d'exécuter les tâches de maintenance quotidiennes.

Exemple :

```bash
php bin/console app:daily-maintenance
```

Cette commande est exécutée automatiquement via un cron serveur.

Exemples de tâches :

* Nettoyage RGPD des données expirées
* Suppression des données temporaires
* Futures tâches de maintenance métier

---

## Stack technique

* **Backend** : Symfony
* **Frontend Admin** : Twig
* **API** : REST
* **Base de données** : SQLite
* **Logs** : Monolog
* **Authentification** : Symfony Security
* **Intégrations** : Sellsy, Klaviyo

---

## Structure du projet

```text
src/
├── Admin/
│   ├── Controller/
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
│   ├── Entity/
│   ├── Repository/
│   └── Controller/
│
├── Command/
│   └── DailyMaintenanceCommand.php
│
└── Sellsy/
```

---

## Évolutions possibles

* Dashboard d'administration avancé
* Monitoring applicatif
* Visualisation des tâches de maintenance
* Tableau de bord métier
* Statistiques et reporting
* Gestion des utilisateurs administrateurs
* Alertes et notifications
* Protection anti-spam avancée
* Système de queue (Messenger)
* Nouvelles intégrations métier

---

## Nom du projet

Nom interne : **Lead Hub Service**

Usage :

* Salons
* Événements
* Jeux concours
* Outils internes SHOGGA

---

## Auteur

**Pierre Lopez**
Alias **Elwindria**
