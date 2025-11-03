<?php
// includes/auth.php
declare(strict_types=1);

/* ----------------------------- Sessions ----------------------------- */

function ensure_session_started(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/* --------------------------- Helpers généraux ------------------------ */

function valid_role(string $role): string {
    $allowed = ['etudiant','entreprise','admin'];
    return in_array($role, $allowed, true) ? $role : 'etudiant';
}

function email_disponible(string $email): bool {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    return !$stmt->fetchColumn();
}

function hash_password(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function normalize_email(string $email): string {
    return mb_strtolower(trim($email));
}

function validate_account_fields(string $nom, string $email, string $password, string $role): ?string {
    if (trim($nom) === '')                         return "Le nom est obligatoire.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return "Email invalide.";
    if (mb_strlen($password) < 8)                   return "Mot de passe trop court (min 8).";
    if (!in_array($role, ['etudiant','entreprise','admin'], true)) return "Rôle invalide.";
    return null;
}

/* ------------------------ Inscription : Entreprise ------------------- */
/**
 * Inscription complète "en une fois" pour une entreprise.
 * - Crée le compte dans `users`
 * - Crée la fiche entreprise dans `entreprises`
 * - Connecte l'utilisateur si $autoLogin = true
 *
 * Retourne [true, null] en cas de succès, sinon [false, "Message d'erreur"].
 */
function register_entreprise_full(
    string $compte_nom,           // nom du contact (table users.nom)
    string $email,
    string $password,
    string $telephone = '',
    string $nom_entreprise = '',
    string $secteur = '',
    string $localisation = '',
    ?string $site_web = null,
    ?string $description = null,
    ?string $contact_email = null,
    ?string $contact_tel = null,
    ?string $logo_url = null,
    bool $autoLogin = true
): array {
    $emailNorm = normalize_email($email);
    $role      = 'entreprise';

    // Validations compte
    if ($err = validate_account_fields($compte_nom, $emailNorm, $password, $role)) {
        return [false, $err];
    }
    if (!email_disponible($emailNorm)) {
        return [false, "Cet email est déjà utilisé."];
    }

    // Validations profil minimales (à adapter selon ta politique)
    if (trim($nom_entreprise) === '' || trim($secteur) === '' || trim($localisation) === '') {
        return [false, "Veuillez renseigner au minimum : nom de l’entreprise, secteur et localisation."];
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        // 1) users
        $stmt = $pdo->prepare("
            INSERT INTO users (nom, email, mot_de_passe, telephone, role)
            VALUES (:nom, :email, :hash, :tel, :role)
        ");
        $stmt->execute([
            ':nom'   => trim($compte_nom),
            ':email' => $emailNorm,
            ':hash'  => hash_password($password),
            ':tel'   => trim($telephone),
            ':role'  => $role,
        ]);
        $userId = (int)$pdo->lastInsertId();

        // 2) entreprises
        $stmt2 = $pdo->prepare("
            INSERT INTO entreprises
              (user_id, nom_entreprise, secteur, localisation, site_web, description, contact_email, contact_tel, logo_url)
            VALUES
              (:uid, :nom, :secteur, :loc, :site, :descr, :cemail, :ctel, :logo)
        ");
        $stmt2->execute([
            ':uid'   => $userId,
            ':nom'   => trim($nom_entreprise),
            ':secteur'=> trim($secteur),
            ':loc'   => trim($localisation),
            ':site'  => $site_web ? trim($site_web) : null,
            ':descr' => $description ? trim($description) : null,
            ':cemail'=> $contact_email ? trim($contact_email) : null,
            ':ctel'  => $contact_tel ? trim($contact_tel) : null,
            ':logo'  => $logo_url ? trim($logo_url) : null,
        ]);

        $pdo->commit();

        if ($autoLogin) {
            ensure_session_started();
            session_regenerate_id(true);
            $_SESSION['user'] = ['id'=>$userId, 'email'=>$emailNorm, 'role'=>'entreprise'];
        }

        return [true, null];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return [false, "Erreur lors de l'inscription entreprise : ".$e->getMessage()];
    }
}

/* ------------------------- Inscription : Étudiant -------------------- */
/**
 * Inscription complète "en une fois" pour un étudiant.
 * - Crée le compte dans `users`
 * - Crée la fiche étudiant dans `etudiants`
 * - Connecte l'utilisateur si $autoLogin = true
 */
function register_etudiant_full(
    string $nom, string $email, string $password, string $telephone = '',
    string $ville = '', string $domaine = '', string $cv_url = '',
    ?string $competances = null, ?string $ecole = null, ?string $diplome = null, ?string $photo_url = null,
    bool $autoLogin = true
): array {
    $emailNorm = normalize_email($email);
    $role      = 'etudiant';

    // Validations compte
    if ($err = validate_account_fields($nom, $emailNorm, $password, $role)) {
        return [false, $err];
    }
    if (!email_disponible($emailNorm)) {
        return [false, "Cet email est déjà utilisé."];
    }

    // Validations profil minimales
    if (trim($ville) === '' || trim($domaine) === '' || trim($cv_url) === '') {
        return [false, "Veuillez renseigner au minimum : ville, domaine et lien CV."];
    }

    $pdo = db();
    try {
        $pdo->beginTransaction();

        // 1) users
        $stmt = $pdo->prepare("
            INSERT INTO users (nom, email, mot_de_passe, telephone, role)
            VALUES (:nom, :email, :hash, :tel, :role)
        ");
        $stmt->execute([
            ':nom'   => trim($nom),
            ':email' => $emailNorm,
            ':hash'  => hash_password($password),
            ':tel'   => trim($telephone),
            ':role'  => $role,
        ]);
        $userId = (int)$pdo->lastInsertId();

        // 2) etudiants
        $stmt2 = $pdo->prepare("
            INSERT INTO etudiants
              (user_id, ville, domaine, competances, cv_url, ecole, diplome, photo_url)
            VALUES
              (:uid, :ville, :domaine, :compet, :cv, :ecole, :diplome, :photo)
        ");
        $stmt2->execute([
            ':uid'    => $userId,
            ':ville'  => trim($ville),
            ':domaine'=> trim($domaine),
            ':compet' => $competances ? trim($competances) : null,
            ':cv'     => trim($cv_url),
            ':ecole'  => $ecole ? trim($ecole) : null,
            ':diplome'=> $diplome ? trim($diplome) : null,
            ':photo'  => $photo_url ? trim($photo_url) : null,
        ]);

        $pdo->commit();

        if ($autoLogin) {
            ensure_session_started();
            session_regenerate_id(true);
            $_SESSION['user'] = ['id'=>$userId, 'email'=>$emailNorm, 'role'=>'etudiant'];
        }

        return [true, null];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return [false, "Erreur lors de l'inscription étudiant : ".$e->getMessage()];
    }
}

/* ----------------------------- Connexion ----------------------------- */

function login_user(string $email, string $password): bool {
    $emailNorm = normalize_email($email);

    $stmt = db()->prepare("
        SELECT id, email, mot_de_passe, role
        FROM users
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute([':email' => $emailNorm]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($password, $u['mot_de_passe'])) {
        return false;
    }

    ensure_session_started();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int)$u['id'],
        'email' => $u['email'],
        'role'  => $u['role'],
    ];
    return true;
}

/* ---------------------------- Déconnexion ---------------------------- */

function logout_user(): void {
    ensure_session_started();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ------------------- Accès utilisateur en session -------------------- */

function current_user(): ?array {
    ensure_session_started();
    return $_SESSION['user'] ?? null;
}
function current_user_id(): ?int {
    $u = current_user();
    return $u['id'] ?? null;
}
function current_user_role(): ?string {
    $u = current_user();
    return $u['role'] ?? null;
}

/* --------------------------- Protections pages ------------------------ */

function require_login(string $base = '/altconnect'): void {
    if (!current_user_id()) {
        $next = urlencode($_SERVER['REQUEST_URI'] ?? $base . '/index.php');
        header('Location: '.$base.'/login.php?next='.$next);
        exit;
    }
}

/** $roles peut être string ou array */
function require_role(array|string $roles, string $base = '/altconnect'): void {
    $roles = (array)$roles;
    $role  = current_user_role();
    if (!$role || !in_array($role, $roles, true)) {
        header('Location: '.$base.'/login.php');
        exit;
    }
}
