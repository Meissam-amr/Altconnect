<?php
// includes/init.php
declare(strict_types=1);

/* ----------------------------------------------------------------------
   CONFIGURATION GLOBALE
---------------------------------------------------------------------- */

// Mode développement (afficher erreurs)
$IS_DEV = true; // mets false quand tu mettras en ligne
ini_set('display_errors', $IS_DEV ? '1' : '0');
error_reporting($IS_DEV ? E_ALL : 0);

// Fuseau horaire et encodage
date_default_timezone_set('Europe/Paris');
mb_internal_encoding('UTF-8');

/* ----------------------------------------------------------------------
   SESSIONS
---------------------------------------------------------------------- */
session_set_cookie_params([
  'lifetime' => 0,                   // expire à la fermeture du navigateur
  'path'     => '/',                 // valable sur tout le site
  'httponly' => true,                // inaccessible via JS
  'samesite' => 'Lax',               // protection CSRF de base
  'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

/* ----------------------------------------------------------------------
   CONSTANTES GLOBALES
---------------------------------------------------------------------- */

// Ton projet est accessible à http://localhost/altconnect/
define('BASE', '/altconnect');

/* ----------------------------------------------------------------------
   INCLUSIONS PRINCIPALES
---------------------------------------------------------------------- */

require_once __DIR__ . '/db_connect.php'; // -> fournit la fonction db()
require_once __DIR__ . '/auth.php';       // -> login_user(), register_*, etc.

/* ----------------------------------------------------------------------
   FONCTIONS UTILITAIRES GÉNÉRALES
---------------------------------------------------------------------- */

function e(string $str): string {
  // Échappe une chaîne pour affichage HTML (prévention XSS)
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
  header('Location: ' . $path);
  exit;
}

