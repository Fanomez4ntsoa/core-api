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
| Permissions | spatie/laravel-permission (installé) |
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
users          -- Table centrale unifiée
roles          -- Rôles de la plateforme
user_roles     -- Pivot users <-> roles avec universe_slug
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
- `Identity` → Status KYC, Submit vérification (manual_review si pas de clé OpenAI)

### 🔄 En cours / À faire
- Connecter `abracadabati` au Core via API `/me`

### 📋 Plus tard (quand abracadabati sera branché)
- Middleware de validation inter-univers
- Endpoint `/api/universe/validate` pour les univers externes

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
- ❌ Pas de Sanctum (installé mais non utilisé — on utilise JWT)
- ❌ Pas d'interfaces Service pour l'instant (à ajouter quand l'équipe grandit)
- ❌ Pas de création sans vérifier Emergent d'abord

---

## 📂 Projets locaux

| Projet | Chemin | Rôle |
|--------|--------|------|
| `abracadaworld-core` | `~/project/abracadaworld-core/` | Notre nouveau Core Laravel |
| `AbracadaBati` | `~/project/AbracadaBati/` | Référence Emergent (FastAPI + React) |

---

*Dernière mise à jour : 31 Mars 2026 — Module Identity validé — Core terminé*
*Rédigé par : Fanomezantsoa + Claude*