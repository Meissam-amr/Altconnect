<?php
// includes/init.php

// 1️⃣ Configuration de base
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ========================================
// 2️⃣ Démarrer la session (si pas déjà active)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// ========================================
// 3️⃣ Charger les fichiers essentiels
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/auth.php';

// ========================================
// 4️⃣ (Optionnel) Paramètres globaux
date_default_timezone_set('Europe/Paris');

// Tu peux aussi définir des constantes globales ici, par ex. :
define('APP_NAME', 'AltConnect');
define('APP_VERSION', '1.0');
