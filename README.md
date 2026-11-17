# 🔗 ShortLink NoSQL — Application de Raccourcissement de Liens

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Riak KV](https://img.shields.io/badge/Riak_KV-NoSQL-F29111?style=for-the-badge)
![Redis](https://img.shields.io/badge/Redis-In--Memory-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-Container-2496ED?style=for-the-badge&logo=docker&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)

Application web moderne de raccourcissement d'URL développée en **PHP natif** reposant sur une architecture hybride **NoSQL** combinant **Riak KV** (persistance distribuée des liens et statistiques) et **Redis** (gestion rapide des utilisateurs et sessions).

---

## 🏗️ Architecture & Technologies

Le projet suit une architecture modulaire 3-tiers :
* **Frontend :** HTML5, CSS3 personnalisé (design moderne, ombres douces, responsive), JavaScript (copie en 1 clic, téléchargement QR Code, recherche dynamique) et Bootstrap 5.
* **Backend :** PHP 8 (architecture structurée en répertoires `config/`, `functions/`, `public/`, `admin/`).
* **Base de données Clé-Valeur distribuée (Riak KV) :** Stockage des URLs originales, codes courts, compteurs de clics, dates d'expiration et protection par mot de passe via l'API HTTP REST (port `8098`).
* **Base de données en mémoire (Redis) :** Gestion des comptes utilisateurs (hachage `bcrypt`), indexation des liens par utilisateur et gestion des sessions.

---

## ✨ Fonctionnalités Principales

### Visiteurs (Anonymes)
* Génération instantanée d'un lien court unique.
* Expiration automatique configurée à **30 jours**.
* Génération et téléchargement direct du **QR Code** associé.
* Copie rapide du lien raccourci dans le presse-papiers.

### Utilisateurs Inscrits
* Authentification sécurisée (Inscription / Connexion via Redis).
* Création de liens avec **alias personnalisé**, **date d'expiration** et **protection par mot de passe**.
* Tableau de bord personnel (`dashboard.php`) avec suivi des clics, recherche en temps réel, modification et suppression de liens.

### Espace Administrateur & Avancé
* Gestion et consultation des liens et des utilisateurs de la plateforme.

---

## 📸 Aperçu de l'Application

### Page d'accueil (Mode Visiteur)
<p align="center">
  <img src="screenshots/accueil.jpg" alt="Page d'accueil" width="85%">
</p>

### Création de lien avancé (Utilisateur connecté)
<p align="center">
  <img src="screenshots/admin.jpg" alt="Création avancée" width="85%">
</p>

### Tableau de Bord Utilisateur
<p align="center">
  <img src="screenshots/dashboard.jpg" alt="Dashboard Utilisateur" width="85%">
</p>

---

## 📁 Structure du Projet

```text