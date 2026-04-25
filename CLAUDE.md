# CLAUDE.md — AbracadaWorld Core

> **Lis ce fichier entièrement avant de faire quoi que ce soit.**
> Il contient toutes les décisions d'architecture, les règles du projet, et le contexte nécessaire pour travailler correctement.

---

## 🧠 Règle fondamentale — À ne jamais oublier

**Avant de créer ou modifier quoi que ce soit :**

1. **Vérifier d'abord** ce que le projet Emergent (`AbracadaBati`) a déjà fait
2. **Extraire** ce qui est pertinent
3. **Adapter** à notre stack et architecture
4. **Enrichir** seulement si nécessaire pour AbracadaWorld

> Ne jamais inventer une structure, un champ, un rôle, ou un endpoint sans avoir vérifié s'il existe déjà dans le projet Emergent de référence.

---

## 📌 Contexte du projet

### Vision
**AbracadaWorld** = OS de business digitaux

```
AbracadaWorld (porte d'entrée centrale)
└── Univers indépendants et vendables :
    ├── AbracadaBati   (bâtiment)
    ├── AbracadaImmo   (immobilier)
    ├── AbracadaPool   (piscine)
    └── ...
```

### Ce repo : `abracadaworld-core`
Le **Core central** qui gère :
- Authentification unique pour tous les univers
- Gestion des users et identités
- API `/me` consommée par tous les univers

### Projet de référence : `AbracadaBati`
- Monolithe généré par **Emergent Agent**
- Stack : FastAPI + React + MongoDB
- Chemin local : `~/project/AbracadaBati/`
- **C'est la référence métier** — tout ce qu'on construit doit s'en inspirer

---

## ✅ Stack technique validé

### Core (`abracadaworld-core`)
| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 12 |
| Base de données | MySQL |
| Auth | JWT (tymon/jwt-auth) |
| Permissions | Système maison — tables `roles` + `user_roles` (pivot avec `universe_slug`) |
| Déploiement | Railway |

### Univers (`abracadabati`, etc.)
| Couche | Technologie |
|--------|-------------|
| Backend | Laravel 12 |
| Base de données | MySQL (base séparée par univers) |
| Auth | Valide via API Core `/me` |

### Frontend
| Couche | Technologie |
|--------|-------------|
| Framework | React (existant dans AbracadaBati) |
| Styling | TailwindCSS + Shadcn/UI |

### Infra & Services
| Service | Outil |
|---------|-------|
| Backend hosting | Railway |
| Frontend hosting | Vercel |
| Paiements | Stripe |
| Recherche | Algolia |
| Médias | Cloudinary / S3 |

---

## 🏗️ Architecture DDD Modulaire

### Structure des modules
```
app/
├── Modules/
│   ├── Auth/
│   │   ├── Controllers/    # Léger — reçoit, délègue, retourne
│   │   ├── Requests/       # Validation des données entrantes
│   │   └── Services/       # Toute la logique métier
│   ├── User/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Services/
│   └── Identity/
│       ├── Controllers/
│       ├── Requests/
│       └── Services/
├── Models/                 # Models Eloquent
└── Http/
    └── Controllers/        # Controller de base uniquement
```

### Règles d'architecture
- **Controller** → reçoit la requête, appelle le Service, retourne JsonResponse. Pas de logique métier.
- **Request** → toute la validation. Jamais de `$request->validate()` dans un controller.
- **Service** → toute la logique métier. C'est ici qu'on crée, modifie, supprime.
- **Model** → Eloquent pur. Relations, casts, scopes. Pas de logique métier.

### Syntaxe Laravel 12 (PHP Attributes)
```php
// ✅ Correct — Laravel 12
#[Fillable(['email', 'password', 'username'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject

// ❌ Incorrect — ancienne syntaxe
protected $fillable = ['email', 'password'];
protected $hidden = ['password'];
```

---

## 🗄️ Base de données — Core

### Tables existantes
```sql
users                    -- Table centrale unifiée
roles                    -- Rôles de la plateforme
user_roles               -- Pivot users <-> roles avec universe_slug
identity_verifications   -- Soumissions KYC (selfie + document)
```

### Champs clés de `users`
```
id, uuid, email, password, username
first_name, last_name, display_name, phone
avatar_url, cover_photo, bio
user_type (particulier|professionnel)
city, postal_code, country
company_name, siret, metier
is_verified, verified_at, identity_status
is_active, locale
remember_token, timestamps, soft_deletes
```

### Rôles seedés (basés sur Emergent)
| Slug | Description |
|------|-------------|
| `social_user` | Utilisateur réseau social (défaut) |
| `client` | Artisan abonné BatiAssist |
| `assistant` | Assistant humain BatiAssist |
| `admin` | Administration plateforme |
| `particulier` | Particulier cherchant des services |
| `professionnel` | Artisan/pro du bâtiment |

### Champs clés de `identity_verifications`
```
id, uuid, user_id
selfie_url, id_document_url, id_document_type (passport|id_card|driver_license)
status (pending|processing|verified|rejected|manual_review)
ai_confidence_score, ai_analysis
manual_reviewer_id, manual_review_notes, manual_review_at
rejection_reason
submitted_at, processed_at, verified_at, timestamps
```

### Champ `universe_slug` sur `user_roles`
Permet à un même user d'avoir des rôles différents selon l'univers :
```
user_id | role_id | universe_slug
1       | 3       | core
1       | 2       | bati          ← artisan sur Bati
1       | 5       | immo          ← particulier sur Immo
```

---

## 🔌 API Auth — Endpoints existants

### Publics
```
POST /api/auth/register   → Créer un compte
POST /api/auth/login      → Se connecter
```

### Protégés (Bearer token JWT)
```
GET  /api/me                      → Profil de l'utilisateur connecté
POST /api/auth/logout             → Se déconnecter
PUT  /api/user/profile            → Mettre à jour le profil
POST /api/user/change-password    → Changer le mot de passe
GET  /api/verification/status     → Statut KYC de l'utilisateur
POST /api/verification/submit     → Soumettre selfie + document d'identité
```

### Structure de réponse (compatible frontend Emergent)
```json
{
  "token": "eyJ...",
  "user": {
    "id": "uuid",
    "email": "...",
    "name": "...",
    "role": "social_user",
    "user_type": "particulier",
    "is_active": true
  },
  "profile": {
    "id": "uuid",
    "user_id": "uuid",
    "email": "...",
    "username": "...",
    "display_name": "...",
    "user_type": "particulier",
    "profile_photo": null,
    "bio": null,
    "city": null,
    "company_name": null,
    "metier": null,
    "is_verified": false,
    "identity_status": "pending",
    "role": "social_user",
    "has_pro_subscription": false,
    "shop_enabled": false
  }
}
```

---

## 📁 Modules à construire

### ✅ Terminé
- `Auth` → Register, Login, Me, Logout
- `User` → Update profil, Change password
- `Identity` → Status KYC, Submit vérification
  - **IA** : OpenAI `gpt-4o` (vision) compare le selfie au document d'identité
  - **Seuils** : confiance ≥ 70 → `verified` · 50–70 → `manual_review` · < 50 → `rejected`
  - **Fallback** : pas de clé OpenAI → `manual_review`
  - ⚠️ **Stockage images** : actuellement base64 tronqué (placeholder). Brancher Cloudinary/S3 avant la prod.

### ✅ Terminé
- `abracadabativ2` créé — Laravel 12, DB MySQL `abracadabativ2`, port 8001
- `CoreAuthMiddleware` → appelle `GET /api/me` du Core, synchro user local
- Migrations : `users`, `prospects`, `clients`, `quotes`, `invoices`, `chantiers`, `company_settings`
- Module CRM/Prospects : CRUD complet (Controller, Service, Requests, Routes)
- Architecture DDD identique au Core : `app/Modules/CRM/{Controllers,Services,Requests}`

---

## ⚙️ Configuration locale

### Fichier `.env` important
```env
APP_NAME=AbracadaWorld-Core
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=abracadaworld_core
SESSION_DRIVER=file
CACHE_STORE=file
```

### Lancer le projet
```bash
cd ~/project/abracadaworld-core
php artisan serve --port=8000
```

### Tester l'API
- Insomnia / Postman
- Header obligatoire : `Accept: application/json`
- Auth : `Authorization: Bearer <token>`

---

## 🚫 Ce qu'on ne fait PAS

- ❌ Pas de `$request->validate()` dans les controllers
- ❌ Pas de logique métier dans les controllers
- ❌ Pas de MongoDB (on migre vers MySQL)
- ❌ Pas de Sanctum — migration `personal_access_tokens` présente mais non utilisée, à supprimer
- ❌ Pas d'interfaces Service pour l'instant (à ajouter quand l'équipe grandit)
- ❌ Pas de création sans vérifier Emergent d'abord

---

## 🔧 Corrections récentes (25 Avril 2026)

- **`DatabaseSeeder`** corrigé → appelle désormais `RoleSeeder` (les 6 rôles sont semés via `php artisan db:seed`). Ancien test user (champ `name` inexistant) supprimé.
- **Handler JWT global** ajouté dans `bootstrap/app.php` → `TokenExpiredException`, `TokenInvalidException`, `JWTException` et `AuthenticationException` (sur routes `api/*`) renvoient désormais :
  ```json
  { "message": "Token invalide ou expiré", "status": 401 }
  ```
  au lieu d'une page HTML Laravel.

---

## 📂 Projets locaux

| Projet | Chemin | Rôle |
|--------|--------|------|
| `abracadaworld-core` | `~/project/abracadaworld-core/` | Core Laravel — port 8000 |
| `abracadabativ2`     | `~/project/abracadabativ2/`     | Univers Bati Laravel — port 8001 |
| `AbracadaBati`       | `~/project/AbracadaBati/`       | Référence Emergent (FastAPI + React) |

---

*Dernière mise à jour : 25 Avril 2026 — DatabaseSeeder + handler JWT corrigés*
*Rédigé par : Fanomezantsoa + Claude*