# Support Agent-Justice — application web d'assistance aux juridictions

Application de support (helpdesk) permettant aux **juridictions** de déclarer leurs
**plaintes et bugs**, et à l'**équipe support** de les prendre en charge, les affecter,
les traiter et les résoudre — avec suivi complet (commentaires, pièces jointes, historique).

**Stack** : PHP 8.3+ · Laravel 13 · Blade · CSS maison (aucun build npm requis, déployable
directement sur Apache) · SQLite (développement) ou MySQL (production).

## Fonctionnalités

| Côté juridiction | Côté support | Côté administration |
|---|---|---|
| Déclaration (bug, plainte, incident, assistance) | Tableau de bord (volumes, retards, délais, tops) | Référentiel des juridictions |
| Pièces jointes (5 × 10 Mo max) | Filtres multi-critères, tri, pagination, export CSV | Comptes (admin / support / juridiction) |
| Fil de discussion avec le support | Affectation, statuts, résolution, rejet motivé | Activation / désactivation des comptes |
| Confirmation de la résolution (clôture) | Notes internes (invisibles aux juridictions) | |

Chaque demande reçoit une **référence unique** (`SUP-2026-00001`) et chaque action est
**journalisée**. Sécurité : limitation des tentatives (5 essais), comptes
activables/désactivables, politiques d'accès par rôle, validation serveur en français,
téléversements restreints, pièces stockées hors accès web direct.

## Démarrage rapide (local)

```powershell
cd C:\Users\hp\Documents\PROJETS\ASCEND\supportJuridictions

# 1. Base de démonstration (SQLite, aucun serveur requis)
php artisan migrate:fresh --seed

# 2. Lancer le serveur de développement
php artisan serve --port=8000
```

Ouvrez ensuite `http://127.0.0.1:8000`.

> Extensions PHP requises (déjà activées sur ce poste) : `pdo_sqlite`, `sqlite3`,
> `mbstring`, `fileinfo`, `openssl`, `curl`, `zip`.

## Comptes de démonstration

Mot de passe unique de démonstration : **`Support1234`**

| E-mail | Rôle | Usage |
|---|---|---|
| `admin@support.local` | Administrateur | Juridictions + comptes |
| `awa.kone@support.local` | Support | Traite et résout |
| `yao.kouassi@support.local` | Support | Traite et résout |
| `contact-tca-abj@juridictions.local` | Juridiction | Tribunal de commerce d'Abidjan |
| `contact-tca-yop@juridictions.local` | Juridiction | Tribunal de commerce de Yopougon |
| `contact-<code>@juridictions.local` | Juridiction | Une adresse par structure |

> ⚠️ **Changez ces mots de passe avant toute exposition réseau.**

## Déploiement réalisé (serveur local — serveur existant Apache)

Configuration en place et validée le 29/09/2026 :

| Élément | Valeur |
|---|---|
| Serveur web | Apache 2.4.41 (`C:\apache24`), **PHP 8.4.14** (`mod_php`, dossiers `php84`) |
| URL de l'application | `http://<ip-du-serveur>:8080` (vhost `support.local`) |
| DocumentRoot | `C:\Users\hp\Documents\PROJETS\ASCEND\supportJuridictions\public` |
| Base de données | MySQL `192.168.1.202:3306` → base **`support_aj`** (dédiée, `tcadb` inchangée) |
| Sessions / cache / files | `database` |
| Port 80 | inchangé — sert l'application existante `htdocs\rccm` |

**Fichiers de configuration modifiés** (sauvegardes `*.bak-cline` à côté) :
- `C:\apache24\conf\httpd.conf` → `Listen 8080`, inclusion de `httpd-vhosts.conf`, module PHP 8.4
- `C:\apache24\conf\extra\httpd-vhosts.conf` → vhost de l'application, vhosts d'exemple désactivés
- `C:\apache24\php84\php.ini` → extensions `pdo_sqlite`/`sqlite3`, `extension_dir` absolu
- `.env` du projet → `DB_*` sur `support_aj`, `APP_URL=http://localhost:8080`

**Démarrer / redémarrer Apache** (aucun service Windows installé) :
```powershell
# Démarrer (processus détaché, survit à la fermeture du terminal)
Invoke-CimMethod -ClassName Win32_Process -MethodName Create -Arguments @{CommandLine='C:\apache24\bin\httpd.exe -d C:/apache24'}
# Redémarrer
Get-Process httpd | Stop-Process -Force
Invoke-CimMethod -ClassName Win32_Process -MethodName Create -Arguments @{CommandLine='C:\apache24\bin\httpd.exe -d C:/apache24'}
# Vérifier la configuration
C:\apache24\bin\httpd.exe -t
```

## Autre déploiement (nouveau serveur, à partir de zéro)

1. **Créer une base dédiée** (⚠️ ne pas réutiliser `tcadb` ni les bases applicatives) :

```sql
CREATE DATABASE support_aj CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Configurer `.env`** (voir `.env.example`) :

```ini
DB_CONNECTION=mysql
DB_HOST=192.168.1.202
DB_PORT=3306
DB_DATABASE=support_aj
DB_USERNAME=support_app
DB_PASSWORD=********
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

3. **Déployer** :

```powershell
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
```

4. **Apache** : pointer un VirtualHost vers `…\supportJuridictions\public`
   avec `AllowOverride All` pour le `.htaccess` fourni.

## Comptes et rôles

- **JURIDICTION** — rattaché à une juridiction : déclare et suit ses demandes, confirme les résolutions.
- **SUPPORT** — voit toutes les demandes : affecte, change les statuts, résout, rejette (motivé), notes internes.
- **ADMIN** — support + référentiel des juridictions et gestion des comptes.

## Cycle de vie d'une demande

```
NOUVELLE → ASSIGNEE → EN_COURS → [EN_ATTENTE_JURIDICTION] → RESOLUE → CLOTUREE
                    ↘ REJETEE (motif obligatoire)
```

Délais indicatifs : critique 8 h · haute 24 h · moyenne 72 h · basse 120 h.
Les demandes hors délai sont signalées « En retard » dans les listes et l'export CSV.

## Tests

```powershell
php artisan test        # 30 tests, base SQLite en mémoire
vendor\bin\pint --test  # conformité du style de code
```

Couverture : authentification (verrouillage après 5 échecs, comptes désactivés),
déclaration (pièces jointes, juridiction forcée), résolution (affectation, statuts,
rejet motivé, clôture par la juridiction), échanges (notes internes invisibles),
droits (isolement entre juridictions, administration réservée).

## Structure du code

```
app/
  Enums/            RoleUtilisateur, TypeTicket, PrioriteTicket, StatutTicket, ActionTicket
  Models/           Juridiction, Ticket (+ règles métier), TicketCommentaire,
                    TicketPieceJointe, TicketHistorique, User
  Http/
    Controllers/    Auth\LoginController, DashboardController, TicketController,
                    TicketCommentaireController, TicketPieceJointeController,
                    JuridictionController, UtilisateurController
    Requests/       validation FR (Store/Update Ticket, Juridiction, Utilisateur, Login)
    Middleware/     EnsureUserHasRole (alias « role »)
  Policies/         TicketPolicy, JuridictionPolicy, UserPolicy
database/           migrations, factories, seeders (Juridiction, Utilisateur, Ticket)
resources/views/    layouts, auth, dashboard, tickets (+ partiels),
                    juridictions, utilisateurs, components (badges), pagination
public/css/         support.css (aucune dépendance front)
tests/Feature/      Authentification, Déclaration, Résolution, Échanges, Accès
```
