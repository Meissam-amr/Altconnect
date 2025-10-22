<?php
// includes/auth.php
declare(strict_types=1);

function ensure_session_started(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}

function register_user(string $nom, string $email, string $password, string $role = 'etudiant'): array {
    $nom   = trim($nom);
    $email = trim(mb_strtolower($email));
    if ($nom === '') return [false, "Le nom est obligatoire."];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return [false, "Email invalide."];
    if (mb_strlen($password) < 8) return [false, "Mot de passe trop court (min 8)."];
    if (!in_array($role, ['admin','recruteur','etudiant'], true)) $role = 'etudiant';

    $hash = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, role) VALUES (:nom,:email,:hash,:role)");
        $stmt->execute([':nom'=>$nom, ':email'=>$email, ':hash'=>$hash, ':role'=>$role]);
        return [true, null];
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') return [false, "Cet email est déjà utilisé."];
        return [false, "Erreur d'inscription : ".$e->getMessage()];
    }
}

function login_user(string $email, string $password): bool {
    $email = trim(mb_strtolower($email));
    $stmt = db()->prepare("SELECT id, mot_de_passe FROM users WHERE email = :email");
    $stmt->execute([':email'=>$email]);
    $u = $stmt->fetch();
    if (!$u || !password_verify($password, $u['mot_de_passe'])) return false;
    ensure_session_started();
    $_SESSION['user_id'] = (int)$u['id'];
    return true;
}

function logout_user(): void {
    ensure_session_started();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p["path"], $p["domain"], $p["secure"], $p["httponly"]);
    }
    session_destroy();
}

function current_user_id(): ?int {
    ensure_session_started();
    return $_SESSION['user_id'] ?? null;
}

function current_user_role(): ?string {
    $uid = current_user_id();
    if (!$uid) return null;
    $stmt = db()->prepare("SELECT role FROM users WHERE id = :id");
    $stmt->execute([':id'=>$uid]);
    return $stmt->fetchColumn() ?: null;
}

function require_login(): void {
    if (!current_user_id()) {
        header('Location: login.php?next='.urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'));
        exit;
    }
}
