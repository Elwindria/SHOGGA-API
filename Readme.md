# Shogga - API

## Présentation

Ce projet est un service développé avec Symfony dans le cadre de mon stage chez SHOGGA (Licence).

Il permet de collecter des leads (emails) lors d’événements (salons, stands, tablettes) et de les transmettre vers les outils CRM et marketing de Shogga.

Il agit comme un **hub central** entre une interface de collecte (frontend), des services tiers (Sellsy, Klaviyo) et un **back-office interne** permettant de gérer les données.

---

## Objectif

Ce service a pour but de :

* Collecter des adresses email via une interface simple (tablette / kiosk)
* Assurer la conformité RGPD (gestion du consentement, traçabilité)
* Centraliser et structurer les données collectées
* Transmettre automatiquement les leads vers des outils externes (Sellsy, Klaviyo)
* Permettre la gestion des leads via une interface backend
* Fournir une base évolutive pour d’autres besoins métier

---

## Architecture

Le projet est composé de deux parties principales :

### API

* Réception des données (formulaire, webhooks)
* Validation et traitement des leads
* Intégration avec des services externes

### Back-office

* Visualisation des emails collectés
* Gestion des leads (consultation, suppression, etc.)
* Outils internes pour le suivi et l’exploitation des données

---

## Cas d’usage

Flux typique :

1. Un utilisateur saisit son email sur une tablette lors d’un salon
2. Il donne son consentement explicite pour recevoir des communications marketing
3. Le frontend envoie les données à l’API Symfony
4. L’API :

   * Valide et nettoie les données
   * Enregistre le lead
   * Stocke la preuve du consentement (date, contexte, source)
   * Transmet les données aux outils CRM / marketing
5. Les leads sont consultables et gérables via le back-office

---

## Fonctionnalités

* Endpoint de collecte d’emails
* Gestion du consentement RGPD (opt-in explicite)
* Validation et normalisation des données
* Intégration avec des services tiers (Sellsy, Klaviyo)
* Interface d’administration (backend)
* Architecture extensible (webhooks, files de traitement, etc.)

---

## Conformité RGPD

Ce service est conçu pour respecter les obligations légales :

* Consentement explicite requis avant toute collecte
* Preuve du consentement enregistrée (timestamp, source)
* Finalité clairement définie (prospection marketing)
* Possibilité d’implémenter :

  * suppression des données
  * export des données

---

## Stack technique

* **Backend** : Symfony
* **API** : REST
* **Base de données** : à définir
* **Intégrations** : Sellsy, Klaviyo

---

## Structure du projet (exemple)

```
/src
  /Controller
    /Api
    /Admin
  /Service
  /Entity
  /Repository
  /Integration
  /Message
  /MessageHandler
```

---

## Évolutions possibles

* Double opt-in (confirmation par email)
* Dashboard d’administration avancé
* Statistiques et reporting
* Protection anti-spam
* Système de queue (Messenger)
* Nouvelles intégrations métier

---

## Nom du projet

Nom interne : **Lead Hub Service**
Usage : salons, événements, outils internes

---

## Auteur

Elwindria (Pierre Lopez)
